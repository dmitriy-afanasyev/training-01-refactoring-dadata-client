# Infrastructure Layer — Слой инфраструктуры

## Назначение

Infrastructure — это **техническая реализация** контрактов, определённых в Domain/Application. Здесь живут HTTP-клиенты, репозитории (работа с БД, внешними API), файловые хранилища, очереди, mail-сервисы. Всё, что связано с «внешним миром».

> **Зависимости:** Infrastructure зависит от Domain (интерфейсы репозиториев, сущности, исключения, VO), но Domain не зависит от Infrastructure. Это **SOLI(D) - Dependency Inversion Principle** в действии.

```
Presentation → Application → Domain ← *Infrastructure*
                                         ^^^ мы здесь
```

**Dependency Rule:** стрелки показывают направление зависимостей. Presentation → Application → Domain — внешние слои зависят от внутренних. Infrastructure реализует интерфейсы Domain/Application (репозитории, внешние API), но не знает о Presentation.

## Структура

```
Infrastructure/
├── Http/
│   └── Dadata/
│       ├── DadataApiInterface.php   # Абстракция HTTP-клиента (дублирует Domain контракт)
│       └── DadataHttpClient.php     # Реализация HTTP-клиента с retry, timeout, auth
└── Persistence/
    ├── DadataAddressRepository.php  # Реализация поиска адресов через DaData API
    ├── DadataBankRepository.php     # Реализация поиска банков через DaData API
    └── DadataPartyRepository.php    # Реализация поиска организаций через DaData API
```

**Примечание:** В этом проекте Persistence — это не БД, а внешний API (DaData). Но архитектурно это тот же паттерн: репозиторий получает данные из внешнего источника.

---

## Ключевые концепции DDD

### Repository Implementation — реализация доменного контрактa

Репозиторий в Infrastructure **реализует** интерфейс из Domain. Он знает про технические детали (HTTP, JSON, API), но снаружи выглядит как доменный контракт.

**Зачем Repository в Infrastructure:**

- Domain говорит «мне нужно найти организацию по ИНН» — Infrastructure решает «как» (HTTP к DaData)
- Репозиторий маппит сырые данные API → доменные Entity
- Репозиторий бросает доменные исключения, а не технические

```php
// ✅ Infrastructure реализует доменный интерфейс
class DadataPartyRepository implements PartyRepositoryInterface
{
    public function __construct(private DadataApiInterface $api) {}

    public function findByInn(Inn $inn): Party
    {
        $data = $this->api->findPartyByInn($inn->value);

        if ($data === null) {
            throw new PartyNotFoundException($inn);  // доменное исключение!
        }

        return $this->mapToParty($data);  // сырые данные → Entity
    }
}
```

**Правило:** репозиторий работает с доменными типами (VO, Entity, доменные исключения), не с примитивами. Он — мост между техническим миром и бизнес-логикой.

---

### Принципы работы с Репозиториями (DDD)

**1. Назначение**

Репозиторий предоставляет способ найти объекты по критериям поиска и получить **целые Агрегаты**, которые удовлетворяют этим критериям. Он действует как коллекция в памяти, инкапсулируя сложность доступа к данным (SQL, маппинг и т.д.).

**2. Что возвращает Репозиторий**

- ✅ **Только Агрегаты:** Методы репозитория должны возвращать полностью сконструированные объекты (Агрегаты) или их коллекции.
- ✅ **Сводные вычисления:** Допускается возврат простых агрегатных значений (например, `count()`, `sum()`), если это логически часть запроса к коллекции объектов.
- ❌ **Частичные данные:** Запрещено возвращать отдельные поля, DTO или части внутренних объектов Агрегата. Это нарушает инкапсуляцию доменной модели.

**3. Границы ответственности**

- Репозитории создаются **только для Корней Агрегатов** (Aggregate Roots), которым нужен глобальный доступ.
- Внутренние объекты Агрегата не имеют своих репозиториев; они доступны только через навигацию от Корня.

**4. Проблема отчетов и списков (Read Models)**

Если требуется получить данные для отображения (списки имен, отчеты, дашборды), где загрузка полных Агрегатов неэффективна или избыточна:

- **Не используйте** Доменный Репозиторий.
- Создайте отдельный механизм чтения (**Read Model** / **Query Service**), который находится вне строгого Доменного слоя (обычно в слое Приложения или Инфраструктуры).
- Этот компонент может выполнять прямые SQL-запросы и возвращать примитивы/DTO, так как он не участвует в изменении состояния и не проверяет бизнес-инварианты Агрегата.

**Цитата из книги:**

> _"Предоставьте методы, которые выбирают объекты на основе критериев... и возвращают полностью сконструированные объекты... Свободные запросы к базе данных могут нарушить инкапсуляцию доменных объектов и Агрегатов."_ (Э. Эванс, Глава 6)

---

### HTTP Client — инфраструктурная абстракция

В этом проекте есть дополнительный слой абстракции: `DadataApiInterface` в Infrastructure. Это не доменный интерфейс, а **инфраструктурный** — он описывает технический контракт HTTP-клиента, а не бизнес-потребность.

**Зачем интерфейс для HTTP-клиента:**

- Легко подменить реализацию (тестовый мок, другой HTTP-клиент)
- Репозиторий зависит от абстракции, а не от конкретного класса
- Можно протестировать репозиторий без реальных HTTP-запросов

```php
// ✅ Инфраструктурный интерфейс — технический контракт
interface DadataApiInterface
{
    public function findPartyByInn(string $inn): ?array;  // примитивы, не доменные типы!
}
```

**Правило:** инфраструктурные интерфейсы работают с примитивами (`?array`, `string`), а доменные — с бизнес-типами (`Inn`, `Party`, `PartyNotFoundException`).

---

### Маппинг данных — сырой ответ → доменная Entity

Репозиторий берёт «грязные» данные из API и создаёт из них доменную Entity. Это ответственность Infrastructure, потому что только она знает формат внешнего источника.

```php
// ✅ Маппинг — Infrastructure знает формат API и структуру Domain
private function mapToBank(array $data): Bank
{
    return new Bank(
        id: Bic::fromString($data['bic']),
        name: $data['name']['full'] ?? null,
        shortName: $data['name']['short'] ?? null,
        bic: Bic::fromString($data['bic']),
        inn: Inn::fromString($data['inn']),
        status: BankStatus::fromString($data['state']['status'] ?? null),
    );
}
```

**Правило:** маппинг — приватный метод репозитория. Никто снаружи не работает с сырыми данными API.

---

## Правила слоя (нельзя нарушать)

### 1. Infrastructure зависит от Domain, а не наоборот

Все зависимости Infrastructure — это интерфейсы и типы из Domain. Infrastructure не диктует Domain как выглядеть.

```php
// ✅ Зависит от доменного интерфейса
class DadataPartyRepository implements PartyRepositoryInterface  // из Domain/Repositories/
{
    public function findByInn(Inn $inn): Party  // Inn, Party — из Domain/
}

// ❌ Нарушение — Infrastructure навязывает Domain свои типы
interface PartyRepositoryInterface  // в Infrastructure!
{
    public function findByInn(string $inn): array;  // примитивы, не доменные типы!
}
```

### 2. Репозиторий бросает доменные исключения, не технические

Репозиторий должен бросать **доменные исключения**, отражающие бизнес-ситуации. Если данные не найдены — бросается `PartyNotFoundException` (доменное), а не `NotFoundException` (техническое) или `null`.

Однако **технические исключения** (сетевые ошибки, таймауты, проблемы с API) должны быть обработаны на уровне инфраструктуры и преобразованы в соответствующие доменные исключения, если это необходимо для бизнес-логики.

```php
// ✅ Доменное исключение — бизнес-ситуация
if ($data === null) {
    throw new PartyNotFoundException($inn);
}

// ❌ Техническое исключение — инфраструктурная деталь
if ($response->failed()) {
    throw new HttpException('API error');
}

// ✅ Правильная обработка технических исключений
try {
    $data = $this->api->findPartyByInn($inn->value);
} catch (ConnectionException | RequestException $e) {
    // Преобразуем технические ошибки в доменные, если они важны для бизнес-логики
    throw new PartySearchFailedException('Не удалось получить данные по ИНН', 0, $e);
}
```

### 3. HTTP-клиент настраивается через конструктор (DI)

Все параметры (API-ключ, URL, таймауты, retry) приходят через конструктор. Никакого `config()` внутри класса.

```php
// ✅ Параметры через конструктор — DI из сервис-провайдера
readonly class DadataHttpClient implements DadataApiInterface
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private int $timeout = 40,
    ) {}
}

// ❌ Нарушение — config() в Infrastructure
public function request(): array
{
    $apiKey = config('geocoder.api_key');  // инфраструктура не должна знать про config!
}
```

### 4. HTTP-клиент: retry с exponential backoff, timeout обязательно

Внешние API могут быть недоступны. HTTP-клиент **должен** иметь таймаут и retry-логику.

```php
// ✅ Обязательно: timeout + retry с exponential backoff
->timeout($this->timeout)
->connectTimeout($this->connectTimeout)
->retry($this->retryCount, function (int $attempt) {
    return $this->retryDelay * (2 ** ($attempt - 1));  // 100ms → 200ms → 400ms
}, function ($exception) {
    // Retry только при ConnectionException или 5xx
    return $exception instanceof ConnectionException
        || ($exception instanceof RequestException
            && $exception->response !== null
            && $exception->response->serverError());
})
```

### 5. Маппинг данных — приватный метод репозитория

Сырые данные API не выходят за пределы репозитория. Маппинг — внутренняя деталь реализации.

```php
// ✅ Маппинг приватный — вызывающий код получает только Entity
public function findByBicOrFail(Bic $bic): Bank
{
    $data = $this->api->findBankByBic($bic->value);
    return $this->mapToBank($data);
}

private function mapToBank(array $data): Bank { ... }  // приватный

// ❌ Нарушение — сырые данные уходят наружу
public function findByBicOrFail(Bic $bic): array  // array! не Bank!
{
    return $this->api->findBankByBic($bic->value);
}
```

### 6. Composition root — Service Provider

Service Provider (`GeocoderServiceProvider`) — единственное место где допустим `config()`. Здесь собираются все зависимости модуля и регистрируются в DI-контейнере.

```php
// ✅ Composition root — здесь config() это нормально
$this->app->bind(DadataApiInterface::class, function ($app) {
    return new DadataHttpClient(
        apiKey: config('geocoder.api_key'),
        baseUrl: config('geocoder.base_url'),
        timeout: config('geocoder.timeout', 40),
    );
});

// ❌ Нарушение — config() в репозитории или HTTP-клиенте
class DadataHttpClient {
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('geocoder.api_key');  // config() тут нельзя!
    }
}
```
