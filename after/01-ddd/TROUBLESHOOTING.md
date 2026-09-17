## Установка

### Permission denied при composer update

```bash
# Запуск команды
make vendor-update
# ./vendor/bin/sail composer update

# Результат - ошибка доступа
In CurlDownloader.php line 216:
                                                                                                                                             
The "https://api.github.com/repos/symfony/http-foundation/zipball/d8fdc670ed510a69e3a9eb4f5eac89f5ed627e17" file could not be written to /var/www/html/vendor/composer/tmp-3c208c3519a149c977e4d4370f62ae1
 1.zip: Failed to open stream: Permission denied    
```

**Причина:** Контейнер Sail и хост-система используют разные UID/GID,
поэтому контейнер не может писать в существующие папки.

**Решение:**
```bash
./vendor/bin/sail up -d
./vendor/bin/sail root-shell
# внутри контейнера:
cd ..
chown -R sail:sail html
exit
```
