# Domain Layer — Доменный слой

## Назначение

Domain — это **сердце DDD-архитектуры**. Здесь живут бизнес-правила, понятия и язык бизнеса (Ubiquitous Language). Доменный слой ничего не знает о базах данных, HTTP, Laravel или фреймворках — это чистый PHP, который описывает **бизнес-смысл**, а не технические детали.

> **Правило зависимостей (Dependency Rule):** Domain не зависит ни от кого. Все остальные слои (Application, Infrastructure, Presentation) зависят от Domain, а не наоборот.

```
Presentation → Application → *Domain* ← Infrastructure
                               ^^^ мы здесь
```

**Dependency Rule:** стрелки показывают направление зависимостей. Presentation → Application → Domain — внешние слои зависят от внутренних. Infrastructure реализует интерфейсы Domain/Application (репозитории, внешние API), но не знает о Presentation. Domain — самый внутренний слой, он не зависит ни от кого, а все остальные — от него.

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

## Ключевые концепции DDD

### Value Object — объект-значение

Value Object описывает **атрибут** сущности, а не саму сущность. У него нет уникальной идентичности — два VO с одинаковым значением **равны**.

**Зачем VO:**
- Валидация при создании — нельзя создать `Bic('abc')`, будет исключение
- Самодокументируемость — `Inn $inn` понятнее чем `string $inn`
- Иммутабельность — после создания значение не меняется
- Поведение рядом с данными — `Inn::isLegalEntity()` знает про ИНН, а не вызывающий код

```php
// ✅ VO валидируется при создании — дальше по коду значение гарантированно корректно
$bic = Bic::fromString('044525225');

// ❌ Примитивная одержимость (Primitive Obsession) — антипаттерн
// string не валидируется, любая функция может получить мусор
public function findByBic(string $bic): Bank
```

**Правило:** создавайте VO для каждого бизнес-понятия с правилами валидации. Это «входные ворота» домена — невалидные данные не пройдут.

---

### Entity — сущность с идентичностью

Entity — объект, который **уникально идентифицируется** (по INN, БИК, ID). Две организации с одинаковым названием — это разные Entity, потому что у них разные ИНН.

**Зачем Entity:**
- Имеет уникальную идентичность (Identity)
- Может изменяться во времени (статус, адрес)
- Содержит бизнес-правила, связанные с этой сущностью

```php
// ✅ Entity иммутабельна — состояние задаётся раз и навсегда
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

**Правило:** если в будущем Entity понадобится изменяемой (методы, меняющие состояние) — это будет **агрегат с поведением**, а не просто контейнер данных.

---

### Repository — контракт домена на получение данных

Repository — это **абстракция** для работы с коллекцией Entity. Домен говорит «мне нужно найти организацию по ИНН», а как именно (MySQL, DaData API, файл) — решает инфраструктура.

**Почему интерфейс в Domain:**
- Домен сам определяет свои потребности — инфраструктура подстраивается
- Легко подменить реализацию (тест, мок, другой источник) без изменения домена
- Dependency Inversion Principle: Domain зависит от абстракций, не от конкретных технологий

**Наш контекст:** в этом проекте репозитории используются только для чтения (DaData API). Методы возвращают Entity или бросают исключение — «не найдено» это бизнес-ошибка, а не нормальный результат.

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

**CQRS на будущее:** если появятся команды (создание, изменение), репозитории можно разделить на Command (запись, возвращают Entity) и Query (чтение, могут возвращать DTO). Сейчас в этом нет необходимости — один источник данных, только чтение.

---

### Domain Exceptions — бизнес-ошибки, не технические

Доменные исключения — это **бизнес-контракт**, а не ошибка программы. «Организация не найдена» — это бизнес-ситуация, и вызывающий код должен знать как её обработать.

**Почему свои исключения:**
- Каждый тип ошибки обрабатывается по-разному (404 vs 400 vs 502)
- Контекст для логирования — бизнес-данные (ИНН, БИК), не стек-трейс
- Вызывающий код ловит конкретные исключения, а не `catch (\Exception $e)`

```php
// ✅ Исключение — часть доменного контракта
throw new PartyNotFoundException($inn);

// ✅ Контекст для логирования — бизнес-данные
public function context(): array
{
    return ['inn' => $this->inn, 'message' => $this->getMessage()];
}
```

---

## Правила слоя (нельзя нарушать)

### 1. Никаких зависимостей от инфраструктуры и внешних пакетов

Доменный слой — чистый PHP. Никаких `Cache`, `Http`, `DB`, `config()`, Laravel Facades, Eloquent, внешних библиотек. Все зависимости — только на другие файлы этого же слоя.

```php
// ✅ Верно — только доменные типы
public function findByInn(Inn $inn): Party;

// ❌ Нарушение — зависимость от инфраструктуры
public function findByInn(string $inn): Party
{
    $data = Cache::remember(...);  // Cache — инфраструктура
}
```

### 2. Value Objects — входные ворота с валидацией

Все входные данные проходят через Value Objects. Фабричный метод `fromString()` бросает доменное исключение при невалидном значении. Слой выше (Application) создаёт VO, домен получает только валидные.

```php
// ✅ Валидация в VO — нельзя создать Bic с буквами
$bic = Bic::fromString('044525225');

// ❌ Нельзя: примитивы просачиваются в домен
public function findByBic(string $bic): Bank  // string не валидирован
```

### 3. Entity иммутабельны (private(set))

Сущности используют PHP 8.4 property hooks `private(set)` — свойства задаются только в конструкторе. Это делает Entity предсказуемыми: после создания состояние не меняется.

```php
public function __construct(
    private(set) Bic $id,
    private(set) ?string $name = null,
    private(set) ?BankStatus $status = null,
) {}
```

Если в будущем понадобится изменяемая Entity — это будет означать что это **агрегат с поведением** (методы-команды), а не просто контейнер данных.

### 4. Repository — интерфейс в домене, реализация в инфраструктуре

В DDD **Domain зависит от абстракций, а не от конкретных технологий**. Слой домена определяет **что** нужно (контракт), а слой инфраструктуры решает **как** это сделать (БД, внешний API, файл).

**Почему интерфейс в Domain:**
- Домен сам определяет свои потребности — никто снаружи не навязывает ему формат данных
- Инфраструктура подстраивается под домен, а не наоборот
- Легко подменить реализацию (тест, мок, другой источник данных) без изменения домена

**Что возвращают методы репозитория:**
- **Command-репозитории** (изменение состояния) → возвращают Entity
- **Query-репозитории** (чтение, CQRS) → могут возвращать DTO или примитивы
- `null` допустим если «не найдено» — ожидаемый сценарий (см. правило 8)

```php
// ✅ Domain определяет контракт — что нужно бизнесу
interface PartyRepositoryInterface
{
    public function findByInn(Inn $inn): Party;
}

// ✅ Infrastructure решает как получить данные — БД, HTTP-клиент, файл
class DadataPartyRepository implements PartyRepositoryInterface { ... }
class DatabasePartyRepository implements PartyRepositoryInterface { ... }

// ⚠️ CQRS: Query-репозиторий возвращает DTO для чтения
interface PartyQueryRepository
{
    public function findByInn(Inn $inn): ?PartyData;
}
```

### 5. Исключения с контекстом

Все доменные исключения наследуют `GeocoderException` и реализуют `context(): array` для структурированного логирования. Исключения — это бизнес-контракт, не техническая деталь.

```php
// ✅ Исключение — часть доменного контракта
throw new PartyNotFoundException($inn);

// ✅ Контекст для логирования — бизнес-данные, не стек-трейс
public function context(): array
{
    return ['inn' => $this->inn, 'message' => $this->getMessage()];
}
```

### 6. Enums — бизнес-статусы с fallback

Backed enums для статусов (`ACTIVE`, `LIQUIDATED`, `REORGANIZED`, `CLOSING`). Метод `fromString()` возвращает `null` для `null` и fallback на `ACTIVE` для неизвестных значений — потому что внешние API могут вернуть непредусмотренный статус, и домен должен быть устойчив к этому.

```php
// ✅ Fallback на ACTIVE для неизвестных значений из внешнего API
public static function fromString(?string $value): ?self
{
    if ($value === null) return null;
    return self::tryFrom($value) ?? self::ACTIVE;
}
```

### 7. Entity toArray() — snake_case для JSON API

Метод `toArray()` трансформирует camelCase (PSR-12 PHP) → snake_case (JSON API convention). Entity использует PHP-именование, но внешний мир (API) получает snake_case.

```php
// Domain Entity — camelCase (PSR-12)
private(set) string $shortName;

// JSON Response — snake_case (JSON API)
return ['short_name' => $this->shortName];
```

### 8. Контракт репозитория: null или исключение — осознанный выбор

Репозиторий **должен** бросать исключение если вызывающий код не может продолжить работу без результата (бизнес-требование). Методы `findByInn()` и `findByBicOrFail()` бросают `PartyNotFoundException` / `BankNotFoundException` — потому что «организация не найдена» это бизнес-ошибка, а не обычный сценарий.

Но `null` допустим если отсутствие результата — ожидаемое состояние:

```php
// ❌ Исключение там где null — нормальный результат
public function findById(int $id): Party;  // Не нашли — бросаем? Но это не ошибка

// ✅ Null — допустимый результат
public function findById(int $id): ?Party;

// ✅ Исключение — когда это бизнес-ошибка
public function findByInnOrFail(Inn $inn): Party;
```
