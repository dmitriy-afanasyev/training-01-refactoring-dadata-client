# Решение с помощью DDD

[![PHP Version](https://img.shields.io/badge/PHP-8.5-777BB4)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20)](https://laravel.com)

Решение на основе Domain-Driven Design (DDD) с разделением на 4 слоя.

## 🏗️ Архитектурный подход

### Domain-Driven Design (Предметно-ориентированное проектирование)

Код разделён на 4 слоя с чётким направлением зависимостей:

```
Presentation → Application → Domain ← Infrastructure
```

| Слой | Назначение | Документация |
|------|------------|--------------|
| **Presentation** | Invokable-контроллеры, FormRequest, трансформеры, маршруты | [Представление ↗](app/Geocoder/Presentation/README.md) |
| **Application** | Use Cases: сервисы-оркестраторы, DTO, кэширование | [Приложение ↗](app/Geocoder/Application/README.md) |
| **Domain** | Бизнес-сущности, Value Objects, интерфейсы репозиториев, доменные исключения | [Домен ↗](app/Geocoder/Domain/README.md) |
| **Infrastructure** | HTTP-клиент для DaData API, реализация репозиториев | [Инфраструктура ↗](app/Geocoder/Infrastructure/README.md) |


### Ключевые принципы

- **Dependency Rule** — внешние слои зависят от внутренних, Domain не зависит ни от кого
- **Composition Root** — Service Provider, единственное место с `config()` и `env()`
- **DI over `app()`** — все зависимости через конструктор, скрытые зависимости запрещены
- **Repository Pattern** — Domain определяет контракт, Infrastructure реализует
- **Value Objects** — входные ворота с валидацией (`Inn`, `Bic`)

## ✅ Лучшие практики Laravel

### Контроллеры
- **Invokable controllers** — один класс = одно действие (`__invoke`)
- **FormRequest** для валидации вместо `$request->validate()` в контроллере
- **Публичные геттеры** в FormRequest (`getInn()`) вместо `$request->input()`

### Маршруты
- **Маршруты внутри модуля** — `loadRoutesFrom()` в Service Provider, не в глобальном `routes/api.php`
- **POST для персональных данных** (ИНН, БИК) — не попадают в логи сервера и CDN
- **Rate limiting** на уровне модуля (`throttle:geocoder`)

### Ответы
- **Единый формат JSON** через `ApiResponseFactory`: `{"success": true, "data": {...}}`
- **Трансформеры** — DTO → формат ответа, отдельно от контроллера
- **Exception Handler** — маппинг доменных исключений на HTTP-статусы с русскими сообщениями

### Конфигурация
- **Публикация конфига** через `php artisan vendor:publish --tag=geocoder-config`
- **Разные TTL для кэша** — настраиваются через DI, разные для партий/банков/адресов

### Тестирование
- **`#[CoversClass]`** атрибут на каждом тестовом классе — строгий контроль покрытия
- **Отдельные testsuites** — Unit, Feature, PartyService и др.

## 🚀 Запуск проекта

### Предварительные требования

- Docker и Docker Compose
- Git

### 1. Клонировать репозиторий

```bash
git clone <repo-url>
cd training-01-refactoring-dadata-client/after/01-ddd
```

### 2. Установить зависимости

```bash
composer install
```

### 3. Настроить окружение

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Указать API-ключ DaData

В файле `.env`:

```env
DADATA_API_KEY=ваш_ключ
DADATA_BASE_URL=https://suggestions.dadata.ru/suggestions/api/4_1/rs
```

API-ключ можно получить на https://dadata.ru/api/

### 5. Запустить через Docker (Laravel Sail)

```bash
# Запуск контейнеров
./vendor/bin/sail up -d

# Проверка
./vendor/bin/sail artisan about
```

Если порт 80 занят, добавьте в `.env`:

```env
APP_PORT=8080
```

### 6. Без Docker (локальный PHP)

```bash
php artisan serve
```

## 🧪 Запуск тестов

### Все тесты

```bash
./vendor/bin/sail test
```

### Отдельный testsuite

```bash
./vendor/bin/sail test --testsuite=Unit
./vendor/bin/sail test --testsuite=Feature
```

### Конкретный файл

```bash
./vendor/bin/sail test tests/Unit/Geocoder/Application/Services/PartyServiceTest.php
```

### По имени теста

```bash
./vendor/bin/sail test --filter=find_by_inn_returns_party_data
```

### С покрытием

```bash
./vendor/bin/sail test --coverage
```

### Без Docker

```bash
php artisan test
```

## 📡 API Endpoints

| Метод | Путь | Описание |
|-------|------|----------|
| POST | `/api/geocoder/party/by-inn` | Найти организацию по ИНН |
| POST | `/api/geocoder/bank/by-bic` | Найти банк по БИК |
| GET | `/api/geocoder/address/search?query=...` | Поиск адреса |
| GET | `/api/geocoder/country/search?query=...` | Поиск страны |

Полная OpenAPI-спецификация: [`docs/api/openapi.yaml`](docs/api/openapi.yaml)

## 📄 Лицензия

MIT
