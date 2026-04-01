# 🏋️ Тренировочная задача: Refactoring Dadata API Client

[![PHP Version](https://img.shields.io/badge/PHP-8.5-777BB4)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20)](https://laravel.com)

## 🎯 Что это

Рефакторинг "грязного" и нерабочего клиента для DaData API.
Исходный код нарушал SOLID, не имел тестов и содержал множество проблем.

**Результат:** чистый, тестируемый код на Laravel с использованием современных паттернов.

```text
├── before/     # Исходный код (который нужно было рефакторить)
├── after/      # Решение
├── TASK.md     # Постановка задачи
├── SOLUTION.md # Объяснение решения
└── README.md   # Этот файл
```

## 🚀 Laravel Boost

Проект использует [Laravel Boost](https://boost.laravel.com) — AI-инструмент для ускорения разработки.

### Установка

Boost уже установлен в проекте. Для настройки AI-агентов:

```bash
cd after
composer require laravel-boost/boost
php artisan boost:install
```

При установке выберите `cursor` как AI-агент (универсальный вариант).

### Обновление правил (SKILLS)

Laravel Boost предоставляет набор правил best practices для Laravel. Для обновления:

```bash
cd after
php artisan boost:update
```

Это обновит:
- Правила в `.cursor/skills/laravel-best-practices/`
- MCP-сервер для интеграции с AI
- Последние best practices для Laravel

### Использование SKILLS

Правила находятся в `.cursor/skills/laravel-best-practices/` и покрывают:
- Архитектуру и безопасность
- Eloquent и производительность БД
- Кэширование и очереди
- Валидацию и тестирование
- Обработку ошибок и др.

При написании кода AI-агент будет автоматически применять эти правила.

### Конфигурация

Настройки Boost находятся в:
- `.cursor/mcp.json` — MCP-сервер для AI
- `.cursor/skills/` — правила best practices
