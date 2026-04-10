# Geocoder Module

Модуль геокодера — DDD CQRS-архитектура с 4 слоями. Только чтение из внешнего API (DaData).

```
Presentation → Application → Domain ← Infrastructure
```

## Слои

| Слой | Назначение | README |
|---|---|---|
| **Presentation** | Точка входа: HTTP → сервисы → JSON-ответ | [Presentation/README.md](Presentation/README.md) |
| **Application** | Use Cases: оркестрация, кэш, DTO | [Application/README.md](Application/README.md) |
| **Domain** | Бизнес-правила: Entity, VO, интерфейсы репозиториев | [Domain/README.md](Domain/README.md) |
| **Infrastructure** | Реализация: HTTP-клиент, DaData-репозитории | [Infrastructure/README.md](Infrastructure/README.md) |

---

## Общие правила модуля

### 1. Composition Root — Service Provider

`GeocoderServiceProvider` — **единственное** место во всём модуле, где допустим `config()`, `env()`, `new ...`. Все слои получают зависимости через конструктор (DI).

```php
// ✅ Service Provider — собирает зависимости
$this->app->when(BankService::class)->needs('$cacheTtlMinutes')
    ->give(fn() => config('geocoder.cache.bank_ttl_minutes'));

// ❌ Domain/Application/Infrastructure не зовут config()
class PartyService {
    public function findByInn(string $inn): PartyData
    {
        $ttl = config('geocoder.cache.party_ttl_minutes');  // нельзя!
    }
}
```

Это касается **всех слоёв**, и тем более Domain — чистый PHP не должен знать про Laravel-конфигурацию.

### 2. Dependency Rule

Внешние слои зависят от внутренних, но не наоборот:

```
Presentation → Application → Domain ← Infrastructure
```

Domain — самый внутренний, он не зависит ни от кого, а все остальные слои — от него. Infrastructure реализует его интерфейсы. Presentation работает с Application DTO, не с Domain Entity.

### 3. Чистый Domain

Domain-слой — чистый PHP. Никаких `config()`, `Cache`, `Http`, `DB`, Laravel Collection, Facades, внешних пакетов. Если нужны параметры — они передаются через конструктор из Composition Root.

### 4. Только чтение (Query-модель)

В этом проекте нет записи — только чтение из DaData API. Репозитории возвращают Entity или бросают исключение. CQRS-разделение не нужно.

### 5. DI over `app()`/`resolve()`

В слоях запрещён контейнерный доступ (`app()`, `resolve()`, `app(Service::class)`). Все зависимости — через конструктор. Единственное исключение — Service Provider.

**Почему нельзя `app()`:**
- **Скрытые зависимости** — из сигнатуры метода не видно, что ему нужно. Конструктор честно показывает все зависимости
- **Сложнее тестирование** — чтобы замокать зависимость, нужно биндить в контейнере вместо простого `new Class($mock)`
- **Жёсткая связность** — код привязан к Laravel-контейнеру, нельзя запустить вне Laravel
- **Нарушение Dependency Inversion** — слой сам тянет зависимость, а не получает её извне

```php
// ✅ DI — зависимости видны в конструкторе, легко тестировать
class PartyByInnController
{
    public function __construct(
        private PartyService $partyService,
        private PartyTransformer $transformer,
    ) {}
}

// ❌ app() — скрытая зависимость, нельзя подменить в юнит-тесте
public function __invoke(PartyByInnRequest $request): JsonResponse
{
    $partyService = app(PartyService::class);  // откуда? что нужно?
    $transformer = app(PartyTransformer::class);
}
```

### 6. Маршруты внутри модуля

Маршруты находятся в `Presentation/Api/Routes/api.php` и подключаются через `loadRoutesFrom()` в Service Provider. Не в глобальном `routes/api.php`.
