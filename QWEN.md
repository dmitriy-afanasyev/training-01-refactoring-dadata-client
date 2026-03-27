## Qwen Added Memories
- В DDD-архитектуре для Laravel-проектов используем название слоя app/UI вместо app/Interfaces для Interface Layer
- Service Provider для модуля Geocoder размещается в app/Geocoder/Providers/GeocoderServiceProvider
- Финальная структура проекта Geocoder: after/app/Geocoder/ содержит Domain/, Infrastructure/, Application/, UI/, Providers/, config/. Конфиг регистрируется через mergeConfigFrom в GeocoderServiceProvider, с возможностью публикации через vendor:publish --tag=geocoder-config
- Не делать коммиты в git без прямой просьбы пользователя
