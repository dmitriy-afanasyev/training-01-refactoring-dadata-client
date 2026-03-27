# Описание решения

### Установка laravel

Находясь в корне репозитория
```
curl -s "https://laravel.build/after?with=none" | bash
```
`with=none` - без доп сервисов (бд, redis и тп)

Проверяем работоспособность
```bash
cd after
./vendor/bin/sail up -d
./vendor/bin/sail artisan about
```
Если получили ошибку
```
Error response from daemon: ports are not available: exposing port TCP 0.0.0.0:80 -> 127.0.0.1:0: listen tcp 0.0.0.0:80: bind: address already in use
```
Добавить переменную в .env - APP_PORT=8080


