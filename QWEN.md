## Qwen Added Memories
- В DDD-архитектуре для Laravel-проектов используем название слоя app/UI вместо app/Interfaces для Interface Layer
- Service Provider для модуля Geocoder размещается в app/Geocoder/Providers/GeocoderServiceProvider
- Финальная структура проекта Geocoder: after/app/Geocoder/ содержит Domain/, Infrastructure/, Application/, UI/, Providers/, config/. Конфиг регистрируется через mergeConfigFrom в GeocoderServiceProvider, с возможностью публикации через vendor:publish --tag=geocoder-config
- Не делать коммиты в git без прямой просьбы пользователя
- В Laravel-проектах всегда создаём контроллеры с одним публичным методом __invoke (invokable controllers) для соблюдения принципа единственной ответственности
- В Laravel-проектах всегда выносим валидацию входящих данных в отдельные классы FormRequest вместо валидации в контроллере
- В Laravel-проектах в классах FormRequest используем метод validate() для валидации и создаём публичные геттеры для получения валидированных данных (не обращаемся к request()->input() напрямую)
- Принцип чистого кода: если есть возможность не возвращать null — не возвращай. Использовать пустые коллекции вместо null, выбрасывать исключения или использовать паттерн Null Object.
