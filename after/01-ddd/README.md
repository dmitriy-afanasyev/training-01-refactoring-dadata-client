# Geocoder Module - DaData API Client

[![PHP Version](https://img.shields.io/badge/PHP-8.5-777BB4)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20)](https://laravel.com)

Модуль для работы с DaData API в рамках Laravel-приложения.

## 📋 Структура модуля

```
app/Geocoder/
├── Domain/                    # Domain Layer (ядро)
│   ├── Entities/              # Сущности (Party, Bank, Address)
│   ├── ValueObjects/          # Объекты-значения (Inn, Bic)
│   ├── Repositories/          # Интерфейсы репозиториев
│   └── Exceptions/            # Доменные исключения
│
├── Infrastructure/            # Infrastructure Layer
│   ├── Http/Dadata/          # HTTP-клиент для DaData API
│   └── Persistence/           # Реализации репозиториев
│
├── Application/               # Application Layer
│   ├── Services/              # Сервисы приложения
│   ├── DTO/                   # DTO для передачи данных
│   └── Exceptions/            # Исключения приложения
│
├── UI/                        # Interface Layer
│   └── Http/Controllers/      # HTTP-контроллеры
│
├── Providers/                 # Service Providers
│   └── GeocoderServiceProvider.php
│
└── config/                    # Конфигурация модуля
    └── geocoder.php
```

## 🚀 Быстрый старт

### 1. Установка зависимостей

```bash
cd after
composer install
```

### 2. Настройка окружения

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Настройка DaData API

В файле `.env` укажите ваш API ключ DaData:

```env
DADATA_API_KEY=ваш_ключ
DADATA_BASE_URL=https://suggestions.dadata.ru/suggestions/api/4_1/rs
```

Получить API ключ можно на https://dadata.ru/api/

### 4. Запуск через Docker (Laravel Sail)

```bash
# Запуск контейнеров
./vendor/bin/sail up -d

# Запуск тестов
./vendor/bin/sail test

# Запуск тестов с покрытием
./vendor/bin/sail php artisan test --coverage
```

### 5. Запуск без Docker

```bash
# Миграция БД
php artisan migrate

# Запуск тестов
php artisan test

# Запуск сервера
php artisan serve
```

## 📡 API Endpoints

### Получить данные организации по ИНН

```bash
GET /api/dadata/party/by-inn?inn=7707083893
```

**Ответ:**
```json
{
  "success": true,
  "data": {
    "name": "ПАО \"СБЕРБАНК\"",
    "short_name": "СБЕРБАНК",
    "inn": "7707083893",
    "kpp": "773601001",
    "status": "ACTIVE"
  }
}
```

### Получить данные банка по БИК

```bash
GET /api/dadata/bank/by-bic?bic=044525225
```

### Поиск адресов

```bash
GET /api/dadata/address/search?query=Москва
```

### Поиск стран

```bash
GET /api/dadata/country/search?query=Россия
```

## 🧪 Тестирование

### Запуск всех тестов

```bash
./vendor/bin/sail test
```

### Запуск отдельного testsuite

```bash
./vendor/bin/sail test --testsuite=Unit
./vendor/bin/sail test --testsuite=Feature
./vendor/bin/sail test --testsuite=PartyService
```

### Запуск конкретного файла теста

```bash
./vendor/bin/sail test tests/Unit/Geocoder/Application/Services/PartyServiceTest.php
```

### Запуск отдельного теста по имени

```bash
./vendor/bin/sail test --filter=find_by_inn_returns_party_data
```

### Запуск с покрытием

```bash
./vendor/bin/sail test --coverage
```

## 📦 Публикация конфигурации

Для публикации конфигурации модуля в основной `config/`:

```bash
php artisan vendor:publish --tag=geocoder-config
```

## 🏗️ Архитектура

Модуль построен на основе **Domain-Driven Design (DDD)** с разделением на слои:

1. **Domain Layer** - бизнес-сущности и правила предметной области
2. **Infrastructure Layer** - внешние зависимости (HTTP, БД)
3. **Application Layer** - сервисы-посредники
4. **UI Layer** - HTTP-контроллеры

### Принципы

- **SOLID** - соблюдение принципов объектно-ориентированного проектирования
- **Dependency Inversion** - зависимость от абстракций, а не от деталей
- **Repository Pattern** - абстракция доступа к данным
- **Value Objects** - объекты-значения с валидацией (Inn, Bic)

## 📝 Примеры использования

### Через сервис

```php
use App\Geocoder\Application\Services\PartyService;

$partyService = app(PartyService::class);

// Получить данные организации
$party = $partyService->findByInn('7707083893');
```

### Через HTTP API

```bash
curl "http://localhost/api/dadata/party/by-inn?inn=7707083893"
```

## 🔧 Конфигурация

Файл `config/geocoder.php`:

```php
return [
    'api_key' => env('DADATA_API_KEY', ''),
    'base_url' => env('DADATA_BASE_URL', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs'),
    'timeout' => env('DADATA_TIMEOUT', 30),
    'retry_count' => env('DADATA_RETRY_COUNT', 3),
];
```

## 📄 Лицензия

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
