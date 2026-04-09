# Application Layer — Слой приложения

## Назначение

Оркестрирует выполнение бизнес-кейсов: координирует доменные объекты, управляет границами транзакций, кэшированием и трансформацией данных между слоями. Это **Use Cases** в терминологии DDD — здесь живёт логика сценариев, а не бизнес-правила.

## Структура

```
Application/
├── DTO/              # Data Transfer Objects для передачи данных между слоями
│   ├── BankData.php
│   └── PartyData.php
└── Services/         # Application Services — оркестраторы use cases
    ├── AddressService.php
    ├── BankService.php
    └── PartyService.php
```

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
