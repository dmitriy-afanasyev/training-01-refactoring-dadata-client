# Application Layer — Слой приложения

## Назначение

Application — это **слойUse Cases** (сценариев использования). Он оркестрирует выполнение бизнес-кейсов: координирует доменные объекты, управляет транзакциями, кэшированием и трансформацией данных между слоями.

Здесь живёт логика **сценариев** (что происходит когда пользователь делает X), а не бизнес-правила (что делает X допустимым). Бизнес-правила — в Domain.

> **Зависимости:** Application зависит от Domain (интерфейсы репозиториев, VO, исключения), но не от Infrastructure (кэш, HTTP, БД) или Presentation. Infrastructure и Presentation зависят от Application.

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

### Application Service — оркестратор, не бизнес-логика

Application Service **координирует** доменные объекты, но не содержит бизнес-правил. Он получает запрос, создаёт/получает доменные объекты, вызывает репозиторий, возвращает результат.

**Зачем Application Service:**
- Определяет **последовательность** действий (получить → проверить → сохранить → отправить)
- Управляет **инфраструктурными Concerns** (кэш, транзакции, события)
- Не содержит бизнес-правил — это чужая ответственность

```php
// ✅ Application Service — только оркестрация
public function findByInn(string $inn): PartyData
{
    $data = Cache::remember(...);          // инфраструктурный concern
    return PartyData::fromArray($data);    // трансформация
}

// ❌ Нарушение — бизнес-правило в сервисе
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

### DTO — данные между слоями без доменной зависимости

DTO — простой объект для передачи данных из Application в Presentation. Presentation **не должен** зависеть от доменных Entity и Value Objects.

**Зачем DTO:**
- Presentation зависит только от Application (DTO), а не от Domain (Entity, VO)
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

Решение «кешировать или нет», «на сколько» и «по какому ключу» — это **бизнес-правило уровня приложения**, а не техническая деталь.

**Почему не в Domain:**
- Домен не должен знать про `Cache` facade, Redis, Memcached
- Домен описывает бизнес-понятия, не инфраструктурные оптимизации

**Почему не в Infrastructure:**
- Репозиторий только получает данные — он не решает, кешировать их или нет
- TTL и ключи кэша — бизнес-правила, а не технический параметр

**Почему Application:**
- Слой приложения решает: этот сценарий стоит кешировать, этот — нет
- Разные сценарии — разный TTL (поиск адреса для клиента = надолго, для админки = на пару минут)

```php
// ✅ Application решает кешировать или нет
public function findByInn(string $inn): PartyData
{
    $data = Cache::remember(
        "geocoder.party.inn.{$inn}",           // ключ кэша — бизнес-решение
        now()->addMinutes($this->cacheTtlMinutes),  // TTL — бизнес-решение
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
public function createOrder(OrderData $data): OrderId
{
    return DB::transaction(function () use ($data) {
        $order = $this->orderRepository->save($data);
        $this->inventoryRepository->reserve($order);
        return $order->getId();
    });
}

// ❌ Domain сущность не управляет транзакцией
class Order {
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

### 2. Кэширование — в слое приложения, не в домене и не в инфраструктуре

- **Домен** не должен знать об инфраструктурных деталях (`Cache` facade, Redis, Memcached)
- **Инфраструктура** (репозиторий) только получает данные — не решает, кэшировать или нет
- **Слой приложения** оркестрирует: TTL, ключи кэша, инвалидация — это бизнес-правила, а не техническая деталь

TTL настраивается через конструктор (DI из сервис-провайдера) — разные окружения могут использовать разное время жизни.

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

Границы транзакции определяет Application-слой, потому что он знает что такое «единица работы». Домен оперирует агрегатами и не должен знать про БД, `DB::transaction()` или connection pooling.

```php
// ✅ Верно — приложение управляет транзакцией
public function createOrder(OrderData $data): OrderId
{
    return DB::transaction(function () use ($data) {
        $order = $this->orderRepository->save($data);
        $this->inventoryRepository->reserve($order);
        return $order->getId();
    });
}

// ❌ Нарушение — доменная сущность управляет транзакцией
class Order {
    public function save(): void {
        DB::transaction(fn() => $this->persist());
    }
}
```

### 5. Не возвращать null

Вместо `null` бросаются исключения (`PartyNotFoundException`, `BankNotFoundException`). Это делает контракт метода явным и защищает вызывающий код от незадокументированного `null`.

### 6. Value Objects создаются в слое приложения

Валидация входных данных (`Inn::fromString()`, `Bic::fromString()`) происходит в сервисе перед вызовом репозитория. Доменный слой получает уже валидные VO.
