# Решение с помощью DDD

[![PHP Version](https://img.shields.io/badge/PHP-8.5-777BB4)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20)](https://laravel.com)
[![Tests](https://github.com/dmitriy-afanasyev/training-01-refactoring-dadata-client/actions/workflows/01-ddd-tests.yml/badge.svg)](https://github.com/dmitriy-afanasyev/training-01-refactoring-dadata-client/actions/workflows/01-ddd-tests.yml)

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
- **Чистый Domain** — чистый PHP без фреймворков, `Cache`, `Http`, `config()`
- **Repository Pattern** — Domain определяет контракт, Infrastructure реализует
- **Anti-Corruption Layer** — DTO между Application и Presentation, Domain Entity не утекает наружу

## ✅ Лучшие практики Laravel
Примечательное, что было применено из Laravel boost скилов, "Чистой архитектуры" и "Чистого кода"

### Контроллеры и валидация
- **Invokable controllers** — один класс = одно действие (`__invoke`), тело метода < 10 строк
- **FormRequest** — валидация отдельно, типизированные геттеры (`getInn()`) вместо `$request->input()`

### Маршруты
- **Маршруты внутри модуля** — `loadRoutesFrom()` в Service Provider, не в глобальном `routes/api.php`
- **POST для персональных данных** (ИНН, БИК) — не попадают в логи сервера и CDN
- **Rate limiting** на уровне модуля (`throttle:geocoder`)

### HTTP-клиент
- **Timeout обязательно** — `timeout()` + `connectTimeout()` на каждый запрос
- **Retry с exponential backoff** — `100ms → 200ms → 400ms`, только при сетевых/5xx ошибках
- **`preventStrayRequests()`** в тестах — никаких случайных запросов к реальному API

### Кэширование
- **TTL через DI** — настраивается в Service Provider, не захардкожено

### Исключения и ответы
- **Контроллер — только success path** — `$this->service->find()` + трансформер, без `try/catch`
- **Единый формат JSON** через `ApiResponseFactory`: `{"success": true, "data": {...}}`
- **Трансформеры** — DTO → формат ответа, отдельно от контроллера
- **Exception Handler** — маппинг доменных исключений на HTTP-статусы с русскими сообщениями
- **`context(): array`** на каждом доменном исключении — структурированное логирование

### Тестирование
- **`#[CoversClass]`** атрибут на каждом тестовом классе — строгий контроль покрытия
- **`Http::fake()`** — моки внешнего API во всех Feature/Unit тестах

### Логирование
- **Отдельный канал `geocoder`** — `storage/logs/geocoder/geocoder.log` (daily, 14 дней)
- **Структурированный контекст** — URL, метод, input (без паролей/токенов), IP, user-agent, маршрут
- **Debug-режим** — stack trace и доменный `context()` только при `config('app.debug')`
- **Тесты не логируют** — `app()->environment('testing')` пропускает запись в лог

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

### 3. Создать файл окружения

```bash
cp .env.example .env
```

### 4. Получить и указать API-ключ DaData

API-ключ можно бесплатно получить на https://dadata.ru/pricing/
На текущий момент существует бесплатный тариф с лимитом до 10_000 запросов в день.
В тарифной сетке его можно не заметить, но информация о нём есть на странице цен.

В файле `.env`:

```env
DADATA_API_KEY=ваш_ключ
DADATA_BASE_URL=https://suggestions.dadata.ru/suggestions/api/4_1/rs
```

### 5. Запустить через Docker (Laravel Sail)

```bash
# Запуск контейнеров
./vendor/bin/sail up -d

# Генерация ключа приложения
./vendor/bin/sail artisan key:generate

# Проверка
./vendor/bin/sail artisan about
```

Если порт 80 занят, добавьте в `.env`:

```env
APP_PORT=8080
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
