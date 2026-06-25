# Domain Layer — Доменный слой

## Назначение

Domain — это **сердце DDD-архитектуры**. Здесь живут бизнес-правила, понятия и язык бизнеса (Ubiquitous Language). Доменный слой ничего не знает о базах данных, HTTP, Laravel или фреймворках — это чистый PHP, который описывает **бизнес-смысл**, а не технические детали.

> **Dependency Rule:** Domain — самый внутренний слой, он не зависит ни от кого. Все остальные слои (Application, Infrastructure, Presentation) зависят от Domain, а не наоборот.

```
Presentation → Application → *Domain* ← Infrastructure
                               ^^^ мы здесь
```

Стрелки показывают направление зависимостей: внешние слои зависят от внутренних. Infrastructure реализует интерфейсы Domain (репозитории, внешние API), но не знает о Presentation.

## Структура

```
Domain/
├── Entities/           # Сущности — объекты с уникальной идентичностью
│   ├── Bank.php        # Банк идентифицируется по БИК
│   └── Party.php       # Организация идентифицируется по ИНН
├── Enums/              # Бизнес-статусы с поведением
│   ├── BankStatus.php  # ACTIVE, LIQUIDATED, REORGANIZED, CLOSING
│   └── PartyStatus.php # Те же статусы для организаций
├── Exceptions/         # Доменные исключения — бизнес-ошибки с контекстом
│   ├── GeocoderException.php        # Базовый абстрактный exception
│   ├── InvalidAddressException.php  # Невалидный адрес
│   ├── InvalidBicException.php      # Невалидный БИК
│   ├── InvalidInnException.php      # Невалидный ИНН
│   ├── BankNotFoundException.php    # Банк не найден
│   ├── PartyNotFoundException.php   # Организация не найдена
│   └── ExternalApiException.php     # Ошибка внешнего API
├── Repositories/       # Интерфейсы репозиториев — контракты для инфраструктуры
│   ├── AddressRepositoryInterface.php
│   ├── BankRepositoryInterface.php
│   └── PartyRepositoryInterface.php
└── ValueObjects/       # Объекты-значения — иммутабельные с валидацией
    ├── Address.php     # Непустая строка адреса
    ├── Bic.php         # Ровно 9 цифр
    └── Inn.php         # 10 (юрлицо) или 12 (ИП) цифр
```

---

## Концепции и правила

### 1. Value Objects — объекты значений (пользовательский тип данных)

Value Object описывает **атрибут** сущности, а не саму сущность. У него нет уникальной идентичности — два VO с одинаковым значением **равны**.

**Зачем VO:**

- Валидация при создании — нельзя создать `Bic('abc')`, будет исключение
- Самодокументируемость — `Inn $inn` понятнее чем `string $inn`
- Иммутабельность — после создания значение не меняется
- Поведение рядом с данными — `Inn::isLegalEntity()` знает про ИНН, а не вызывающий код

```php
// ✅ VO валидируется при создании — дальше по коду значение гарантированно корректно
$bic = Bic::fromString('044525225');

// ❌ Primitive Obsession — string не валидируется, любая функция может получить мусор
public function findByBic(string $bic): Bank
```

**Правило:** все входные данные проходят через VO. Фабричный метод бросает доменное исключение при невалидном значении. Никаких примитивов (`string`, `int`) на границе домена — это «входные ворота», невалидные данные не пройдут.

---

### 2. Entity — иммутабельные сущности с идентичностью

Entity — объект, который **уникально идентифицируется** (по ИНН, БИК, ID). Две организации с одинаковым названием — это разные Entity, потому что у них разные ИНН.

**Зачем Entity:**

- Имеет уникальную идентичность (Identity)
- Может изменяться во времени (статус, адрес)
- Содержит бизнес-правила, связанные с этой сущностью

```php
// ✅ Entity иммутабельна — состояние задаётся в конструкторе через private(set)
public function __construct(
    private(set) Bic $id,
    private(set) ?string $name = null,
    private(set) ?BankStatus $status = null,
) {}

// ✅ Бизнес-поведение рядом с данными
public function isActive(): bool
{
    return $this->status?->isActive() ?? false;
}
```

**Правило:** сущности используют PHP 8.4 `private(set)` — свойства задаются только в конструкторе. Если в будущем понадобится изменяемая Entity (методы, меняющие состояние) — это будет **агрегат с поведением**, а не просто контейнер данных.

**toArray():** в текущей реализации Entity возвращает данные в snake_case — это **компромисс для простоты**. В чистой архитектуре Domain не должен заботиться о формате внешнего мира (JSON API, gRPC). Правильнее: `toArray()` возвращает «сырые» данные, а Presentation Transformer отвечает за трансформацию `camelCase → snake_case` перед отправкой в HTTP-ответ.

---

### 3. Repository — контракт домена на получение данных

Repository — это **абстракция** для работы с коллекцией Entity. Домен говорит «мне нужно найти организацию по ИНН», а как именно (MySQL, DaData API, файл) — решает инфраструктура.

**Почему интерфейс в Domain:**

- Domain Inversion Principle: Domain зависит от абстракций, не от конкретных технологий
- Домен сам определяет свои потребности — инфраструктура подстраивается
- Легко подменить реализацию (тест, мок, другой источник) без изменения домена

**Наш контекст:** репозитории используются только для чтения (DaData API). Методы возвращают Entity или бросают исключение — «не найдено» это бизнес-ошибка, а не нормальный результат.

```php
// ✅ Domain определяет контракт — что нужно бизнесу
interface PartyRepositoryInterface
{
    public function findByInn(Inn $inn): Party;  // VO → Entity, или exception
}

// ✅ Infrastructure решает как получить данные — БД, HTTP-клиент, файл
class DadataPartyRepository implements PartyRepositoryInterface { ... }
class DatabasePartyRepository implements PartyRepositoryInterface { ... }
```

**Контракт: null или исключение — осознанный выбор.** Метод бросает исключение если вызывающий код не может продолжить без результата (`findByInn` → `PartyNotFoundException`). `null` допустим если отсутствие результата — ожидаемое состояние (`findById(int $id): ?Party`).

**CQRS на будущее:** если появятся команды (создание, изменение), репозитории можно разделить на Command (запись, возвращают Entity) и Query (чтение, могут возвращать DTO). Сейчас один источник данных, только чтение.

**Как данные попадают в Domain — Infrastructure, не Application.** Когда сущность имеет много полей (10+), Infrastructure парсит сырые данные и создаёт Entity напрямую:

```php
// ✅ Infrastructure парсит сырые данные → создаёт VO → создаёт Entity
public function findByInn(Inn $inn): Party
{
    $raw = $this->client->findParty($inn->value);  // массив от DaData

    return new Party(
        id: $inn,
        name: $raw['name']['full_with_opf'],    // string — нет правил валидации
        shortName: $raw['name']['short'],       // string — нет правил
        inn: Inn::fromString($raw['inn']),      // VO — 10 или 12 цифр
        kpp: $raw['kpp'] ?? null,               // ?string — опционально
        status: PartyStatus::fromString($raw['state']['status']),  // Enum
    );
}
```

```php
// ❌ Антипаттерн: Application собирает «мешок сырых строк» в DTO → передаёт в Domain
class PartyCreationData {
    public string $inn;    // ещё не валидирован
    public string $name;
    public ?string $kpp;
    // ...
}

// Domain вынужден сам валидировать — теряет смысл VO как входных ворот
public function __construct(PartyCreationData $data) { ... }
```

DTO для передачи данных в Domain — это обход VO-валидации. Domain должен получать уже проверенные значения, а не сырые строки, которые ещё предстоит проверить.

---

### 4. Exceptions — бизнес-ошибки с контекстом

Доменные исключения — это **бизнес-контракт**, а не ошибка программы. «Организация не найдена» — это бизнес-ситуация, и вызывающий код должен знать как её обработать.

**Почему свои исключения:**

- Каждый тип ошибки обрабатывается по-разному (404 vs 400 vs 502)
- Контекст для логирования — бизнес-данные (ИНН, БИК), не стек-трейс
- Вызывающий код ловит конкретные исключения, а не `catch (\Exception $e)`

**Правило:** все доменные исключения наследуют `GeocoderException` и реализуют `context(): array` для структурированного логирования.

```php
// ✅ Исключение — часть доменного контракта
throw new PartyNotFoundException($inn);

// ✅ Контекст для логирования — бизнес-данные
public function context(): array
{
    return ['inn' => $this->inn];
}
```

**Иерархия:**

```
Exception
  └── GeocoderException (базовый, context(): array → [])
        ├── InvalidAddressException  — context: ['address']
        ├── InvalidBicException      — context: ['bic']
        ├── InvalidInnException      — context: ['inn']
        ├── BankNotFoundException    — context: ['bic']
        ├── PartyNotFoundException   — context: ['inn']
        └── ExternalApiException     — context: ['http_status', 'response']
```

---

### 5. Enums — бизнес-статусы с fallback

Backed enums для статусов (`ACTIVE`, `LIQUIDATED`, `REORGANIZED`, `CLOSING`). Метод `fromString()` возвращает `null` для `null` и fallback на `ACTIVE` для неизвестных значений — внешние API могут вернуть непредусмотренный статус, и домен должен быть устойчив к этому.

```php
// ✅ Fallback на ACTIVE для неизвестных значений из внешнего API
public static function fromString(?string $value): ?self
{
    if ($value === null) return null;
    return self::tryFrom($value) ?? self::ACTIVE;
}
```

---

## Отношения между компонентами

```
                    ┌──────────────────────────────────┐
                    │  Repository Interfaces           │
                    │  BankRepositoryInterface         │
                    │  PartyRepositoryInterface        │
                    │  AddressRepositoryInterface      │
                    └──────┬───────────────┬───────────┘
                           │               │
            зависит от     │               │  возвращает
                           ▼               ▼
                    ┌──────────────┐  ┌──────────────┐
                    │   Entities   │  │   VO → Bank  │
                    │   Bank       │  │   VO → Party │
                    │   Party      │  └──────────────┘
                    └──────┬───────┘
                           │  использует
                           ▼
                    ┌──────────────────┐
                    │     Enums        │
                    │  BankStatus      │
                    │  PartyStatus     │
                    └──────────────────┘

  Entities зависят от ──────────────────────────────┐
       │                                            │
       ├── Bic (VO)                                 │
       ├── Inn (VO)                                 │
       ├── BankStatus (Enum)                        │
       └── PartyStatus (Enum)                       │
                                                    │
  VO бросают ───────────────────────────────────────┤
       │                                            │
       ├── InvalidAddressException ◄──┐             │
       ├── InvalidBicException  ◄─────┤             │
       ├── InvalidInnException  ◄─────┤             │
       └──────────────────────────────┤             │
                                      │             │
  Repository бросят ──────────────────┤             │
       │                              │             │
       ├── BankNotFoundException ─────┘             │
       ├── PartyNotFoundException ────┘             │
       └── ExternalApiException                     │
                                                    │
  Все исключения наследуют ─────────────────────────┤
       │                                            │
       └── GeocoderException ◄──────────────────────┘
```
