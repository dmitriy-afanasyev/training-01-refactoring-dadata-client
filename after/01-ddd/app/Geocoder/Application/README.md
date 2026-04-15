# Application Layer — Слой приложения

## Назначение

Application — это **слой Use Cases** (сценариев использования). Он оркестрирует выполнение бизнес-кейсов: координирует доменные объекты, управляет транзакциями, кэшированием и трансформацией данных между слоями.

Здесь живёт логика **сценариев** (что происходит когда пользователь делает X), а не **доменные бизнес-правила** (что делает X допустимым, какие данные корректны, какие состояния валидны). Доменные правила — в Domain.

```
Presentation → *Application* → Domain ← Infrastructure
                ^^^ мы здесь
```

> **Зависимости:** Application зависит от Domain (интерфейсы репозиториев, VO, исключения), но не от Infrastructure (кэш, HTTP, БД) или Presentation.

Infrastructure может зависеть и от Application — когда слой приложения определяет **outbound ports** (интерфейсы сервисов), а Infrastructure их реализует. Например, уведомление об обновлении кэша:

```
// Application — объявляет контракт: что должно произойти
interface CacheInvalidationNotifier
{
    public function notifyInvalidated(string $pattern): void;
}

// Infrastructure — реализует: куда и как оповестить
class LogCacheNotifier implements CacheInvalidationNotifier
{
    public function notifyInvalidated(string $pattern): void
    {
        Log::info("Cache invalidated: {$pattern}");
    }
}

// Другая реализация — оповестить все ноды кластера
class BroadcastCacheNotifier implements CacheInvalidationNotifier
{
    public function notifyInvalidated(string $pattern): void
    {
        Redis::publish('cache.invalidate', json_encode(['pattern' => $pattern]));
    }
}
```

Application знает _что_ должно произойти (кеш инвалидирован — оповестить), Infrastructure — _как_ (записать в лог, отправить в Redis pub/sub, webhook — неважно для сценария). Это паттерн «порт и адаптер».

## Структура

```
Application/
├── DTO/              # Data Transfer Objects — данные для передачи между слоями
│   ├── BankData.php      # DTO банка для слоя Presentation
│   └── PartyData.php     # DTO организации для слоя Presentation
└── Services/         # Application Services — оркестраторы use cases
    ├── AddressService.php    # Поиск адреса и страны
    ├── BankService.php       # Найти банк по БИК
    └── PartyService.php      # Найти организацию по ИНН
```

---

## Ключевые концепции DDD

### Application Service — оркестратор, не доменная логика

Application Service **координирует** доменные объекты, но не содержит **доменной логики**. Он получает запрос, создаёт/получает доменные объекты, вызывает репозиторий, возвращает результат.

**Зачем Application Service:**

- Определяет **последовательность** действий (получить → проверить → сохранить → отправить)
- Управляет **оркестрационными задачами** (кэш, транзакции, события)
- Не содержит доменных правил — это ответственность Domain

```php
// ✅ Application Service — только оркестрация
public function findByInn(string $inn): PartyData
{
    $data = Cache::remember(...);          // техническая задача
    return PartyData::fromArray($data);    // трансформация
}

// ❌ Нарушение — доменное правило в сервисе
public function findByInn(string $inn): PartyData
{
    if (strlen($inn) !== 10 && strlen($inn) !== 12) {  // это валидация — Domain!
        throw new InvalidInnException($inn);
    }
    ...
}
```

**Правило:** если код описывает «что такое правильный ИНН» — это Domain. Если код описывает «что происходит когда пользователь ищет организацию» — это Application.

---

### DTO — данные между слоями

DTO — простой объект для передачи данных из Application в Presentation. Presentation **не должен** зависеть от доменных Entity и Value Objects напрямую.

> **Примечание:** DTO могут использовать доменные enum (например `PartyStatus`, `BankStatus`) — они описывают тот же язык, что и домен, но не несут поведения. Это допустимо, потому что enum — это данные, не логика.

**Зачем DTO:**

- Presentation зависит от Application (DTO), а не от Domain (Entity, VO)
- DTO может содержать «сплющенные» данные из нескольких Entity
- DTO иммутабелен (`readonly class`) — не является моделью с поведением

```php
// ✅ DTO — простой контейнер данных, создаётся из массива
readonly class PartyData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $inn,
        public ?string $kpp,
        public ?PartyStatus $status,
    ) {}

    public static function fromArray(array $data): self { ... }
}

// ❌ Нарушение — Presentation зависит от доменной Entity
public function show(): JsonResponse
{
    return response()->json($partyEntity->toArray());  // Party — из Domain
}
```

**Правило:** DTO создаётся из массива, который возвращает доменная Entity через `toArray()`. Это «антикоррупционная прослойка» — Presentation не знает про Domain.

---

### Кэширование — решение Application-слоя

Решение «кешировать или нет», «на сколько» и «по какому ключу» — это **оркестрационное решение уровня сценария**, а не техническая деталь инфраструктуры и не доменное правило.

**Почему не в Domain:**

- Домен не должен знать про `Cache` facade, Redis, Memcached
- Домен описывает бизнес-понятия, не инфраструктурные оптимизации

**Почему не в Infrastructure:**

- Репозиторий только получает данные — он не решает, кешировать их или нет
- TTL и ключи кэша — оркестрационные решения, а не технический параметр

**Почему Application:**

- Слой приложения решает: этот сценарий стоит кешировать, этот — нет
- Разные сценарии — разный TTL (поиск адреса для клиента = надолго, для админки = на пару минут)

```php
// ✅ Application решает кешировать или нет
public function findByInn(string $inn): PartyData
{
    $data = Cache::remember(
        "geocoder.party.inn.{$inn}",           // ключ кэша — оркестрационное решение
        now()->addMinutes($this->cacheTtlMinutes),  // TTL — оркестрационное решение
        fn() => $this->fetchPartyData($inn)
    );
    return PartyData::fromArray($data);
}

// ❌ Domain не знает про кэш
class Party {
    public function getFromCache(): ?array { ... }  // Cache — инфраструктура!
}
```

---

### Транзакции — граница единицы работы

Application-слой определяет где начинается и заканчивается транзакция, потому что только он знает что такое «единица работы». Домен оперирует агрегатами, не знает про БД.

```php
// ✅ Application управляет транзакцией — единица работы
public function syncParties(): void
{
    DB::transaction(function () {
        $this->partyRepository->syncFromExternalApi();
        $this->cache->invalidate('geocoder.party.*');
    });
}

// ❌ Domain сущность не управляет транзакцией
class Party {
    public function save(): void {
        DB::transaction(fn() => $this->persist());  // Domain не знает про БД!
    }
}
```

---

## Правила слоя (нельзя нарушать)

### 1. Зависимость от интерфейсов домена, а не от инфраструктуры

Сервисы зависят от `*RepositoryInterface` (Domain), а не от конкретных реализаций (`DadataPartyRepository`). Это позволяет подменить инфраструктуру без изменения слоя приложения.

```php
// ✅ Верно — зависит от интерфейса домена
public function __construct(private PartyRepositoryInterface $repository)

// ❌ Нарушение — зависит от инфраструктуры
public function __construct(private DadataPartyRepository $repository)
```

### 2. Кэширование — в слое приложения

Решение о кэшировании (TTL, ключи, инвалидация) — оркестрационное решение уровня сценария. См. раздел «Кэширование» выше.

### 3. Возвращать DTO или примитивы, не доменные объекты

Слой представления не должен зависеть от доменных Value Objects (`Address`, `Bank`, `Party`). Сервисы возвращают:

- **DTO** (`PartyData`, `BankData`) — для сложных объектов
- **Примитивы** (`array<int, string>`) — для простых списков

```php
// ✅ Верно — DTO для слоя представления
public function findByInn(string $inn): PartyData

// ✅ Верно — примитив для списка строк
public function searchAddress(string $query): array  // array<int, string>

// ❌ Нарушение — доменный объект утекает наружу
public function findByInn(string $inn): Party
```

### 4. Управление транзакциями — в слое приложения

Границы транзакции определяет Application-слой, потому что он знает что такое «единица работы». Домен оперирует агрегатами и не должен знать про БД. См. раздел «Транзакции» выше.

### 5. Не возвращать null

Вместо `null` бросаются исключения (`PartyNotFoundException`, `BankNotFoundException`). Это делает контракт метода явным и защищает вызывающий код от незадокументированного `null`.

### 6. Value Objects создаются в слое приложения

Валидация входных данных (`Inn::fromString()`, `Bic::fromString()`) происходит в сервисе перед вызовом репозитория. Доменный слой получает уже валидные VO.
