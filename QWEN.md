## Qwen Added Memories
- В DDD-архитектуре для Laravel-проектов используем название слоя app/UI вместо app/Interfaces для Interface Layer
- Service Provider для модуля Geocoder размещается в app/Geocoder/Providers/GeocoderServiceProvider
- Финальная структура проекта Geocoder: after/app/Geocoder/ содержит Domain/, Infrastructure/, Application/, UI/, Providers/, config/. Конфиг регистрируется через mergeConfigFrom в GeocoderServiceProvider, с возможностью публикации через vendor:publish --tag=geocoder-config
- Не делать коммиты в git без прямой просьбы пользователя
- В Laravel-проектах всегда создаём контроллеры с одним публичным методом __invoke (invokable controllers) для соблюдения принципа единственной ответственности
- В Laravel-проектах всегда выносим валидацию входящих данных в отдельные классы FormRequest вместо валидации в контроллере
- В Laravel-проектах в классах FormRequest используем метод validate() для валидации и создаём публичные геттеры для получения валидированных данных (не обращаемся к request()->input() напрямую)
- Принцип чистого кода: если есть возможность не возвращать null — не возвращай. Использовать пустые коллекции вместо null, выбрасывать исключения или использовать паттерн Null Object.
- В Laravel-проектах маршруты модуля должны находиться внутри модуля (например, app/Module/UI/Http/routes/web.php) и подключаться через сервис-провайдер модуля с помощью метода $this->loadRoutesFrom(). Не регистрировать маршруты модуля в общем routes/web.php.
- В Laravel-проектах с установленным Sail запускать тесты через `./vendor/bin/sail test`, а не напрямую через `docker compose run laravel.test vendor/bin/phpunit`
- Тесты можно запускать без спроса
- В проекте training-01-refactoring-dadata-client есть план добавить альтернативные варианты решения в папке after/: 01-ddd (текущее), 02-layered (3-слойная архитектура), 03-clean-architecture (Чистая архитектура Uncle Bob), 04-modular-monolith (Модульный монолит), 05-service-layer (Service Layer паттерн), 06-active-record (Active Record вместо Repository), 07-transaction-script (Transaction Script - простое решение)

## Laravel Boost SKILLS
- Использовать правила из `after/.cursor/skills/laravel-best-practices/` при написании Laravel-кода
- 22 правила покрывают: архитектуру, безопасность, кэширование, Eloquent, валидацию, тестирование, очереди, маршрутизацию, HTTP-клиент, события, обработку ошибок и др.
- Перед применением правила проверять Consistency First — следовать существующим паттернам в кодовой базе
