# Presentation Layer — Слой представления

## Назначение

Presentation — это **точка входа** в приложение. Здесь HTTP-запросы превращаются в вызовы Application-сервисов, а результаты превращаются в HTTP-ответы (JSON). Presentation не знает про бизнес-правила — только про протокол (HTTP, JSON, валидацию формата).

> **Зависимости:** Presentation зависит от Application (сервисы, DTO), но не от Domain напрямую (Entity, VO). Infrastructure и Domain не зависят от Presentation.

```
*Presentation* → Application → Domain ← Infrastructure
 ^^^ мы здесь
```

**Dependency Rule:** стрелки показывают направление зависимостей. Presentation → Application → Domain — внешние слои зависят от внутренних. Infrastructure реализует интерфейсы Domain/Application (репозитории, внешние API), но не знает о Presentation.

## Структура

```
Presentation/
└── Api/
    ├── Controllers/        # Invokable контроллеры — тонкие, только координация
    │   ├── AddressSearchController.php
    │   ├── BankByBicController.php
    │   ├── CountrySearchController.php
    │   └── PartyByInnController.php
    ├── Exceptions/         # Обработчик исключений — маппинг доменных → HTTP-статусы
    │   └── GeocoderExceptionHandler.php
    ├── Requests/           # FormRequest — валидация входных данных
    │   ├── AddressSearchRequest.php
    │   ├── BankByBicRequest.php
    │   ├── CountrySearchRequest.php
    │   └── PartyByInnRequest.php
    ├── Responses/          # Фабрика ответов — единый формат JSON API
    │   └── ApiResponseFactory.php
    ├── Routes/             # Маршруты модуля — подключаются через Service Provider
    │   └── api.php
    └── Transformers/       # Трансформеры — DTO → формат ответа API
        ├── BankTransformer.php
        ├── CollectionTransformer.php
        ├── PartyTransformer.php
        └── Transformer.php
```

---

## Ключевые концепции DDD/Laravel

### Invokable Controller — один Use Case = один контроллер

Каждый контроллер делает **одно действие** — вызывает Application-сервис и возвращает ответ. Контроллер тонкий (< 10 строк), не содержит бизнес-логики.

**Зачем invokable:**

- Принцип единственной ответственности — один класс = один сценарий
- Контроллер не раздувается — нет `index()`, `store()`, `update()` в одном файле
- Легко найти код по названию класса (`PartyByInnController`)

```php
// ✅ Invokable — одно действие, тонкий
final class PartyByInnController
{
    public function __construct(
        private PartyService $partyService,
        private PartyTransformer $transformer,
    ) {}

    public function __invoke(PartyByInnRequest $request): JsonResponse
    {
        $party = $this->partyService->findByInn($request->getInn());
        return ApiResponseFactory::success($party, $this->transformer)->toResponse();
    }
}

// ❌ Нарушение — толстый контроллер с бизнес-логикой
class GeocoderController extends Controller
{
    public function partyByInn(Request $request)
    {
        $validated = $request->validate(['inn' => 'required|string|digits_between:10,12']);
        // 50 строк логики...
    }

    public function bankByBic(Request $request) { ... }
    public function addressSearch(Request $request) { ... }
}
```

**Правило:** если контроллер больше 10 строк — бизнес-логика должна быть в Application-сервисе.

---

### FormRequest — валидация отдельно от контроллера

Валидация входных данных вынесена в отдельные классы. Laravel автоматически запускает валидацию до вызова контроллера.

**Зачем FormRequest:**

- Контроллер не занимается валидацией — только координирует
- Валидация переиспользуема (тесты, документация)
- Публичные геттеры — типобезопасный доступ к валидированным данным

```php
// ✅ FormRequest — валидация + типизированные геттеры
class PartyByInnRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'inn' => ['required', 'string', 'digits_between:10,12'],
        ];
    }

    public function getInn(): string
    {
        return $this->validated('inn');  // типизированный геттер
    }
}

// ❌ Нарушение — валидация в контроллере
public function __invoke(Request $request): JsonResponse
{
    $validated = $request->validate([
        'inn' => ['required', 'string', 'digits_between:10,12'],
    ]);
    $inn = $validated['inn'];  // без типизации, можно опечататься
}
```

**Правило:** геттеры FormRequest (`getInn()`, `getBic()`) — единственный способ получить валидированные данные. Никогда не обращаться к `$request->input()` или `$request->all()` в контроллере.

---

### Transformer — DTO → формат ответа API

Трансформер преобразует Application DTO в формат JSON-ответа. Это последний этап обработки данных перед отправкой клиенту.

**Зачем Transformer:**

- Presentation контролирует формат ответа, не зависит от структуры DTO
- Разные трансформеры для разных версий API
- Тестируется отдельно от контроллера

```php
// ✅ Transformer — DTO → array для JSON
final class PartyTransformer extends Transformer
{
    public function transform(mixed $data): array
    {
        if (!$data instanceof PartyData) {
            throw new \InvalidArgumentException('Expected PartyData');
        }

        return [
            'id' => $data->id,
            'inn' => $data->inn,
            'name' => $data->name,
            'short_name' => $data->shortName,     // camelCase → snake_case
            'address' => $data->address,
            'status' => $data->status?->value,
            'is_active' => $data->status?->isActive() ?? false,
        ];
    }
}
```

---

### ApiResponseFactory — единый формат ответов

Фабрика создаёт JSON-ответы в едином формате для всех эндпоинтов.

```php
// ✅ Единый формат: {'success': true/false, 'data': ...} или {'success': false, 'error': ...}
ApiResponseFactory::success($party, $transformer);   // 200
ApiResponseFactory::error('Invalid request');        // 400
ApiResponseFactory::validationError($error, $errors);         // 422
ApiResponseFactory::notFound('Party not found');     // 404
ApiResponseFactory::badGateway('API error');         // 502
ApiResponseFactory::internalError('Internal error'); // 500
```

---

### Exception Handler — маппинг доменных исключений на HTTP-статусы

Доменные исключения (`PartyNotFoundException`, `InvalidInnException`) маппятся на HTTP-статусы (400, 404, 502) с понятными сообщениями на русском.

```php
// ✅ Доменное исключение → HTTP 400 + сообщение на русском
// Статус зашит в фабрику: error() → 400, notFound() → 404, badGateway() → 502
InvalidInnException::class => fn($e) => self::respond(
    $e,
    ApiResponseFactory::error('Неверный формат ИНН')
),

// ✅ Технический стек-трейс только в debug-режиме
'context' => config('app.debug') ? [
    'file' => $e->getFile(),
    'line' => $e->getLine(),
    'trace' => $e->getTraceAsString(),
] : [],
```

---

### Маршруты — внутри модуля, не в routes/api.php

Маршруты модуля находятся в `Presentation/Api/Routes/api.php` и подключаются через Service Provider (`loadRoutesFrom()`).

**Почему маршруты в модуле:**

- Модуль самодостаточен — все файлы модуля в одном месте
- Не засоряем глобальный `routes/api.php`
- Rate limiting (`throttle:geocoder`) на уровне модуля

```php
// ✅ Маршруты модуля — подключаются через Service Provider
Route::prefix('api/geocoder')
    ->middleware(['api', 'throttle:geocoder'])
    ->group(function () {
        // POST для ИНН/БИК — персональные данные не в query string
        Route::post('/party/by-inn', PartyByInnController::class)
            ->name('geocoder.party.by-inn');
        Route::post('/bank/by-bic', BankByBicController::class)
            ->name('geocoder.bank.by-bic');

        // GET для поиска — данные не персональные
        Route::get('/address/search', AddressSearchController::class)
            ->name('geocoder.address.search');
        Route::get('/country/search', CountrySearchController::class)
            ->name('geocoder.country.search');
    });
```

**Почему не `apiResource()`:** это DDD CQRS-проект — маршруты соответствуют Use Cases (найти по ИНН, найти по БИК), а не CRUD-операциям над ресурсами.

---

## Правила слоя (нельзя нарушать)

### 1. Invokable контроллеры — один метод `__invoke`

Каждый контроллер делает одно действие. Нет `index()`, `show()`, `store()` — только `__invoke()`. Контроллер тонкий (< 10 строк), без бизнес-логики. См. развёрнутое описание с примерами выше в секции «Ключевые концепции».

### 2. FormRequest для валидации, не `$request->validate()` в контроллере

Валидация в отдельном классе FormRequest. Контроллер получает уже валидированные данные через типизированные геттеры (`getInn()`, `getBic()`). См. развёрнутое описание выше.

### 3. Presentation зависит от Application, не от Domain напрямую

Контроллеры работают с DTO (`PartyData`), не с Entity (`Party`). Трансформеры преобразуют DTO → формат API.

```php
// ✅ Зависит от Application DTO
public function __invoke(PartyByInnRequest $request): JsonResponse
{
    $party = $this->partyService->findByInn($request->getInn());  // возвращает PartyData
    return ApiResponseFactory::success($party, $this->transformer)->toResponse();
}

// ❌ Нарушение — зависит от Domain Entity
public function __invoke(Request $request): JsonResponse
{
    $party = $this->partyRepository->findByInn($inn);  // возвращает Party (Entity)!
    return response()->json($party->toArray());
}
```

**Допустимое исключение:** `GeocoderExceptionHandler` импортирует доменные исключения (`PartyNotFoundException`, `InvalidInnException`) для маппинга на HTTP-статусы. Это **техническая зависимость на границе слоёв** — Handler не использует бизнес-логику Domain, только типы исключений. Его задача — переводить «язык домена» на «язык HTTP», и без знания о доменных исключениях он не может работать.

Это минимальная зависимость: Handler знает _какие_ исключения бросает Domain, но не знает _как_ Domain работает.

### 4. Маршруты внутри модуля, не в глобальном routes/api.php

Маршруты модуля — в `Presentation/Api/Routes/`, подключаются через `$this->loadRoutesFrom()` в Service Provider.

### 5. POST для персональных данных (ИНН, БИК), GET для поиска

ИНН и БИК — персональные данные. GET-запросы логируются в query string на proxy, CDN, сервере. POST — данные в body, не логируются.

```php
// ✅ POST — ИНН/БИК в body, не в query string
Route::post('/party/by-inn', PartyByInnController::class);

// ✅ GET — поиск адреса/страны, не персональные данные
Route::get('/address/search', AddressSearchController::class);
```

### 6. Единый формат ответов через ApiResponseFactory

Все ответы (успех и ошибки) создаются через фабрику. Нет ручных `response()->json()`.

```php
// ✅ Фабрика — единый формат
return ApiResponseFactory::success($party, $this->transformer)->toResponse();

// ❌ Нарушение — ручной ответ
return response()->json(['data' => $party], 200);
```

### 7. Исключения маппятся на HTTP-статусы с русскими сообщениями

Не возвращать технические сообщения клиенту. Пользователь видит понятное сообщение на русском, в логи идёт контекст.
