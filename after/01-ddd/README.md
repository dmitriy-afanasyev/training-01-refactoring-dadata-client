# Решение с помощью DDD

[![PHP Version](https://img.shields.io/badge/PHP-8.5-777BB4)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20)](https://laravel.com)
[![Tests](https://github.com/dmitriy-afanasyev/training-01-refactoring-dadata-client/actions/workflows/01-ddd-tests.yml/badge.svg)](https://github.com/dmitriy-afanasyev/training-01-refactoring-dadata-client/actions/workflows/01-ddd-tests.yml)
[![API](https://img.shields.io/badge/API%20Reference-OpenAPI-blue)](docs/api/openapi.yaml)
[![Postman Collection](https://img.shields.io/badge/Postman-Collection-orange)](docs/api/training-01-refactoring-dadata-client.postman_collection)
[![Postman Environment](https://img.shields.io/badge/Postman-Environment-lightgrey)](docs/api/training-01-refactoring-dadata-client-env.postman_environment.json)

Решение на основе Domain-Driven Design (DDD) с разделением на 4 слоя.

## 🏗️ Архитектурный подход

### Domain-Driven Design (Предметно-ориентированное проектирование)

Код разделён на 4 слоя с чётким направлением зависимостей:

```
Presentation → Application → Domain ← Infrastructure
```

| Слой               | Назначение                                                                   | Документация                                              |
| ------------------ | ---------------------------------------------------------------------------- | --------------------------------------------------------- |
| **Presentation**   | Invokable-контроллеры, FormRequest, трансформеры, маршруты                   | [Представление ↗](app/Geocoder/Presentation/README.md)    |
| **Application**    | Варианты использования: сервисы-оркестраторы, DTO, кэширование               | [Операционка ↗](app/Geocoder/Application/README.md)       |
| **Domain**         | Бизнес-сущности, Value Objects, интерфейсы репозиториев, доменные исключения | [Домен ↗](app/Geocoder/Domain/README.md)                  |
| **Infrastructure** | HTTP-клиент для DaData API, реализация репозиториев                          | [Инфраструктура ↗](app/Geocoder/Infrastructure/README.md) |

### Ключевые принципы

- **Dependency Rule** — внешние слои зависят от внутренних, Domain не зависит ни от кого
- **Composition Root** — Service Provider, единственное место с `config()` и `env()`
- **DI over `app()`** — все зависимости через конструктор, скрытые зависимости запрещены
- **Чистый Domain** — чистый PHP без фреймворков, `Cache`, `Http`, `config()` и тп.
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
- Make

### 1. Клонировать репозиторий

```bash
git clone https://github.com/dmitriy-afanasyev/training-01-refactoring-dadata-client.git
cd training-01-refactoring-dadata-client/after/01-ddd
```

### 2. Установить и запустить

Установка автоматизирована в [Makefile](Makefile) — одной командой:

```bash
make install
```

`make install` выполняет все шаги установки: создаёт файл окружения `.env` из `.env.example` (если его ещё нет), устанавливает зависимости через Docker (`composer install` без локального PHP), запускает Sail-контейнеры (`sail up -d`), генерирует ключ приложения, выполняет миграции, устанавливает Laravel Boost (`boost:install`) и выводит информацию о приложении (`artisan about`).

Повторный запуск безопасен: `.env` и зависимости переустанавливаются только при их отсутствии.

Если порт 80 занят, добавьте в `.env`:

```env
APP_PORT=8080
```

### 3. Получить и указать API-ключ DaData

API-ключ можно бесплатно получить на https://dadata.ru/pricing/
На текущий момент существует бесплатный тариф с лимитом до 10_000 запросов в день.
В тарифной сетке его можно не заметить, но информация о нём есть на странице цен.

В файле `.env`:

```env
DADATA_API_KEY=ваш_ключ
DADATA_BASE_URL=https://suggestions.dadata.ru/suggestions/api/4_1/rs
```

### Другие команды Makefile

| Команда              | Что делает                                                      |
| -------------------- | --------------------------------------------------------------- |
| `make help`          | Показать список всех целей                                      |
| `make up`            | Запустить Sail-контейнеры                                       |
| `make down`          | Остановить Sail-контейнеры                                      |
| `make test`          | Запустить все тесты                                             |
| `make test-compact`  | Запустить тесты с компактным выводом                            |
| `make test-coverage` | Тесты + отчёт по покрытию                                       |
| `make logs`          | Показать логи Sail                                              |
| `make vendor-update` | Обновить пакеты                                                 |
| `make check-xdebug`  | Проверка загруженных расширений для дебага и генерации покрытия |

## 🧪 Запуск тестов

### Все тесты

```bash
make test
```

### Отдельный testsuite

```bash
./vendor/bin/sail test --testsuite=Unit
./vendor/bin/sail test --testsuite=Feature
```

### Конкретный файл

```bash
./vendor/bin/sail test tests/Unit/Geocoder/Application/Services/BankServiceTest.php
```

### По имени теста

```bash
./vendor/bin/sail test --filter=test_find_by_bic_throws_bank_not_found
```

Запуски с аргументами (testsuite, конкретный файл, фильтр) не имеют отдельных целей в Makefile — для них используются прямые команды Sail.

### С генерацией отчета по покрытию

```bash
make test-coverage
```

Отчёт сохраняется в `storage/app/coverage-report/`. `make test-coverage` автоматически откроет его в браузере; если автоматическое открытие недоступно, будет выведен путь к отчёту.

### Регулярные обновления

```bash
make up
make vendor-update
make test-compact
```

## 📡 API Endpoints

| Метод | Путь                                              | Описание                 |
| ----- | ------------------------------------------------- | ------------------------ |
| POST  | `/api/geocoder/party/by-inn`                      | Найти организацию по ИНН |
| POST  | `/api/geocoder/bank/by-bic`                       | Найти банк по БИК        |
| GET   | `/api/geocoder/address/search?query=Ботаническая` | Поиск адреса             |
| GET   | `/api/geocoder/country/search?query=Россия`       | Поиск страны             |

Полная OpenAPI-спецификация: [`docs/api/openapi.yaml`](docs/api/openapi.yaml)

## Laravel-way документация

[`Laravel-way документация`](docs/project-laravel-way-index.md)

## ⚖️ Плюсы и минусы DDD-подхода

### Плюсы

| Преимущество                 | Что даёт                                                                      |
| ---------------------------- | ----------------------------------------------------------------------------- |
| **Ubiquitous Language**      | Разработчики и эксперты бизнеса говорят на одном языке — код сам документация |
| **Чёткие границы**           | Каждый слой имеет конкретную ответственность, легче находить и менять код     |
| **Тестируемость**            | Domain — чистый PHP, тестируется без фреймворков и инфраструктуры             |
| **Замена технологий**        | Infrastructure можно заменить не затрагивая бизнес-логику                     |
| **Масштабируемость команды** | Новые разработчики быстрее входят в проект благодаря явной структуре          |

### Минусы

| Недостаток                            | Почему важно                                                                             |
| ------------------------------------- | ---------------------------------------------------------------------------------------- |
| **Порог входа**                       | По Эвансу: вся команда должна понимать DDD-концепции, иначе «слои» превратятся в хаос    |
| **Скорость на старте**                | Правильное распределение по слоям, выделение границ требует времени — CRUD будет быстрее |
| **Обязателен регулярный рефакторинг** | Без него Domain обрастает «техническими» зависимостями, а слои размываются               |
| **Бойлерплейт**                       | Interfaces, DTO, Transformers — больше файлов, больше кода для простых операций          |
| **Риск over-engineering**             | Для простых задач DDD может быть избыточен — «молоток, который ищет гвозди»              |

### Когда DDD оправдан

- **Сложная предметная область** — много бизнес-правил, исключений, инвариантов
- **Долгоживущий проект** — инвестиции в архитектуру окупаются со временем
- **Команда от 3 человек** — чёткие границы уменьшают конфликты при слиянии кода

### Когда DDD избыточен

- **Простой CRUD** — админки, лендинги, прототипы
- **Короткие проекты** — MVP на 2-3 месяца, где скорость важнее архитектуры
- **Один разработчик** — накладные расходы на слои не окупаются

> **Ключевая мысль:** DDD без эволюционного проектирования невозможен. Нужно постоянно возвращаться к архитектуре, улучшать границы слоёв и не бояться менять структуру. Просто «делать бизнес-задачи» и надеяться, что DDD сам получится — не работает.
>
> — Дмитрий Афанасьев

## 📄 Лицензия

MIT
