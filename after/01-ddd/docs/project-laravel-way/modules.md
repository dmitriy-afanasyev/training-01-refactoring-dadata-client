# 📦 Модульная архитектура Laravel-проекта

> Документ описывает подходы к организации модулей в проекте, варианты структуры и ключевые принципы реализации.

---

## 🗂️ Варианты структуры модулей

Выбор структуры зависит от сложности проекта и степени связанности компонентов.

### 🔹 Базовый вариант (1 модуль)

```
<корень проекта>/
└── app/
    └── <SomeModule1>/
```

✅ Подходит, если нужно визуально отделить код проекта от фреймворка без усложнения вложенности.

---

### 🔹 Несколько независимых модулей

```
<корень проекта>/
└── app/
    └── Modules/
        ├── <SomeModule1>/
        ├── <SomeModule2>/
        └── <SomeModule3>/
```

✅ Рекомендуется, когда логика проекта естественно распадается на модули **без сильных связей** между ними.

---

### 🔹 Группировка модулей по единому вектору

```
<корень проекта>/
└── app/
    └── Modules/
        ├── <SomeSection_1>/
        │   ├── <SomeModule_1.1>/
        │   ├── <SomeModule_1.2>/
        │   └── <SomeModule_1.3>/
        └── <SomeSection_2>/
            ├── <SomeModule_2.1>/
            ├── <SomeModule_2.2>/
            └── <SomeModule_2.3>/
```

✅ Подходит для сложной предметной области.

---

### 🔹 Apiato-like структура (максимальная гибкость монолита)

По сути Модульный монолит - группируем модули в секции, и выносим общее в Shared. Как это сделано в Apiato (Porto).

```
<корень проекта>/
└── app/
    └── Modules/
        ├── Sections/                 # Бизнес-контексты
        │   └── <SomeSection1>/
        │       ├── <SomeModule1>/
        │       └── <SomeModule2>/
        │
        └── Shared/                   # Переиспользуемая функциональность
            ├── <SomeSharedFunctional1>/
            ├── <SomeSharedFunctional2>/
            └── ...
```

✅ Для крупных проектов с высокой степенью переиспользования кода и чётким разделением ответственности.

---

## ⚙️ Реализация модульности в Laravel

### План реализации

1. Создаем структуру папок модуля;
2. Создаем `MyModuleServiceProvider` как мостик между Laravel и новой структурой нашего приложения;
3. Подключаем `MyModuleServiceProvider` к фреймворку;

### Ключевой компонент: `Service Provider` модуля

Каждый модуль регистрируется через собственный **Service Provider**, который сообщает фреймворку:

| Задача          | Описание                                                 |
| --------------- | -------------------------------------------------------- |
| 🛣️ Маршруты     | Подключение `routes/web.php`, `routes/api.php` и др.     |
| ⚙️ Конфигурация | Загрузка настроек модуля через `config/*.php`            |
| 🔗 DI-биндинги  | Регистрация интерфейсов и их реализаций в контейнере     |
| 🧩 Зависимости  | Автоматический инжект зависимостей при создании объектов |

📌 Пример: [`GeocoderServiceProvider.php`](../../app/Geocoder/Providers/GeocoderServiceProvider.php)

```php
class GeocoderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/geocoder.php',
            'geocoder'
        );

        $this->app->bind(PartyRepositoryInterface::class, DadataPartyRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Presentation/Api/Routes/api.php');
    }
}
```

### Подключаем модуль

[`providers.php`](../../bootstrap/providers.php)

```php
<?php
use App\Providers\AppServiceProvider;
use App\Geocoder\Providers\GeocoderServiceProvider;

return [
    AppServiceProvider::class,
    GeocoderServiceProvider::class,
];
```

---

## 🧪 Тестирование модулей

### Текущий подход

- Тесты размещены в общей директории `tests/` (стандарт PHPUnit)

### Опционально: тесты внутри модуля

```
<корень проекта>/
└── app/
    └── Modules/
        └── <SomeModule1>/
            ├── src/
            ├── config/
            ├── routes/
            └── tests/          # ✅ Тесты модуля
                ├── Unit/
                └── Feature/
```

🔧 Для поддержки такой структуры необходимо обновить `phpunit.xml`:

```xml
<testsuites>
    <testsuite name="Module Tests">
        <directory suffix="Test.php">./app/Modules/*/tests</directory>
    </testsuite>
</testsuites>
```

📚 Пример конфигурации: [apiato/phpunit.xml](https://github.com/apiato/apiato/blob/master/phpunit.xml)

---

## 💡 Рекомендации

1. **Начинайте с простого** — не усложняйте структуру без необходимости.
2. **Соблюдайте единый стиль** — все модули должны следовать одним правилам именования и организации.
3. **Изолируйте зависимости** — модули не должны знать о внутренней реализации друг друга.
4. **Документируйте публичный API модуля** — что он предоставляет и как с ним взаимодействовать.
