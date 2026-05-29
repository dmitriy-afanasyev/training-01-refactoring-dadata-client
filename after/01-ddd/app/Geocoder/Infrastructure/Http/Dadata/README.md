### 2 ответственности класса DadataHttpClient

1. Предметная логика **Что делаем**: `findPartyByInn(), findBankByBic(), searchCountry(), searchAddress()`
2. Инфраструктурная логика **Как делаем**: `httpClient(), request()` — настройка таймаутов, заголовков, retry, обработка ошибок.

В данном случае это осознанный архитектурный выбор и зачастую не является проблемой.
Это не нарушение архитектуры, а осознанный прагматичный выбор, соответствующий практике first-party пакетов Laravel.

**Обоснования:**

1. **Это паттерн Port/Adapter (Gateway)**  
   Класс `DadataHttpClient` реализует интерфейс `DadataApiInterface` (вероятно, лежащий в слое Domain/Application). Его задача → **адаптировать** вызовы предметной области к внешнему API. Метод `httpClient()` является `private` деталью реализации адаптера, а не самостоятельным сервисом. Инфраструктурная логика здесь инкапсулирована, а не расползается по коду.

2. **Высокая когезия**  
   Все, что связано с DaData, находится в одном месте. Разработчик не прыгает между `DadataTransport`, `DadataPayloadBuilder`, `DadataResponseMapper` и `DadataPartyService`. Для внешних API с 4-10 методами это оптимальный баланс.

3. **Laravel-idiomatic подход**  
   В экосистеме Laravel принято писать "толстые" клиенты-обёртки (см. `Laravel\Socialite`, `Laravel\Cashier\Stripe`, `Http`-обёртки в пакетах). Они сочетают настройку транспорта и бизнес-методы, пока не начнут нарушать SRP через рост.

### Когда стоит разделить «КАК» и «ЧТО»

Рассмотрите рефакторинг в Transport + Gateway, если:

```
+ Класс превысил 400-500 строк
+ Один HTTP-транспорт переиспользуется для 3+ разных внешних API
+ Требуется независимое кэширование / circuit breaker / метрики на транспортном слое
+ Команда >5 человек, требуется строгая граница слоёв для параллельной работы
+ Нужно тестировать маппинг ответов без реальных HTTP-запросов
```

Пример разделения (если потребуется):

```php
// Infrastructure/Http/DadataTransport.php
interface HttpTransport {
    public function post(string $endpoint, array $payload): array;
}

class DadataApiGateway implements DadataApiInterface {
    public function __construct(private HttpTransport $transport) {}

    public function findPartyByInn(string $inn): ?array {
        $result = $this->transport->post('/findById/party', [...]);
        // только маппинг и бизнес-логика
    }
}
```

Не является антипаттерном, потому что:

> «Разделяй ответственности, но не плоди абстракции без необходимости»
> — Прагматичный принцип Laravel-экосистемы

---

### Реальный пример из Laravel: Socialite 5.x

Рассмотрим актуальный файл:  
🔗 [laravel/socialite — `src/Two/AbstractProvider.php` (5.x)](https://github.com/laravel/socialite/blob/5.x/src/Two/AbstractProvider.php)

#### Структура класса (упрощённо):

```php
abstract class AbstractProvider implements ProviderInterface
{
    // ─── «КАК»: инфраструктура ─────────────────────────────
    protected function getHttpClient(): Client
    {
        return $this->guzzle ?: new Client();
    }

    protected function getAccessTokenResponse($code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => $this->getTokenHeaders($code),
            'form_params' => $this->getTokenFields($code),
        ]);
        return json_decode($response->getBody(), true);
    }

    // ─── «ЧТО»: предметная логика (оркестрация + хуки) ─────
    public function user(): UserInterface
    {
        $response = $this->getAccessTokenResponse($this->getCode());
        $token = Arr::get($response, 'access_token');
        return $this->mapUserToObject($this->getUserByToken($token));
    }

    // ─── Абстрактные хуки для подклассов ───────────────────
    abstract protected function getUserByToken($token);
    abstract protected function mapUserToObject(array $user);
}
```

#### Что мы видим:

| Уровень               | Методы                                                | Ответственность                                   |
| --------------------- | ----------------------------------------------------- | ------------------------------------------------- |
| **Транспорт**         | `getHttpClient()`, `getAccessTokenResponse()`         | Как делать HTTP-запросы к провайдеру              |
| **Оркестрация**       | `user()`, `redirect()`                                | В каком порядке вызывать шаги OAuth-потока        |
| **Предметная логика** | `getUserByToken()`, `mapUserToObject()` (абстрактные) | Как интерпретировать ответ конкретного провайдера |

**Вывод**: даже в официальном пакете Laravel инфраструктура и бизнес-логика находятся в одном классе. Разделение достигается не через вынос в отдельные сервисы, а через **шаблонный метод (Template Method)** и наследование.

> **Правило разумного рефакторинга**:  
> Сначала заставьте код работать чисто и просто.  
> Разделяйте слои, когда появится конкретная боль сопровождения — не заранее.

см так же про SRP
Application/Services/
