# Генерация отчёта о покрытии кода тестами (PHPUnit)

## Шаг 1. Настройка конфигурации `phpunit.xml`

В корневом файле `phpunit.xml` (или `phpunit.xml.dist`) добавьте блоки `<source>` и `<coverage>`. Они определяют, какие исходные файлы учитывать при расчёте покрытия, и куда сохранять результаты.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit ...>
    <!-- ... другие настройки проекта ... -->

    <source>
        <!-- Директории, файлы из которых будут учитываться в отчёте -->
        <include>
            <directory suffix=".php">app/Geocoder</directory>
        </include>

        <!-- Директории, которые необходимо исключить из расчёта -->
        <exclude>
            <directory>app/Geocoder/Domain/Exceptions</directory>
            <directory>app/Geocoder/Application/Exceptions</directory>
        </exclude>
    </source>

    <coverage>
        <report>
            <!-- HTML-отчёт для локального просмотра -->
            <html outputDirectory="tests/coverage"/>
            <!-- Отчёт в формате Clover для интеграции с CI/CD -->
            <clover outputFile="tests/coverage/clover.xml"/>
        </report>
    </coverage>

    <php>
        <!-- ... переменные окружения, настройки PHP ... -->
    </php>
</phpunit>
```

> 💡 **Примечание:** В PHPUnit 10+ блок `<filter>` заменён на `<source>`. Убедитесь, что вы используете актуальную структуру конфигурации.

## Шаг 2. Исключение артефактов из Git

Добавьте пути к отчётам и кэш-файлам в `.gitignore`, чтобы не коммитить сгенерированные данные:

```gitignore
# Кэш результатов PHPUnit
.phpunit.result.cache

# Директория с отчётами о покрытии
/tests/coverage
```

## Шаг 3. Рекомендуемый драйвер: PCOV

Для генерации отчётов о покрытии **рекомендуется использовать расширение `pcov`**, а не `xdebug`.

- `pcov` оптимизирован специально для задач покрытия кода.
- Работает в **2–5 раз быстрее** и потребляет значительно меньше памяти.
- Позволяет оставлять `xdebug` в режиме `off` или `develop`, не влияя на производительность приложения.

## Шаг 4. Проверка загруженных расширений

Убедитесь, что в окружении активен `pcov`. Для Laravel Sail выполните:

```bash
./vendor/bin/sail php -r "
    echo 'PCOV:  ' . (extension_loaded('pcov') ? '✅ Загружен' : '❌ Не загружен') . PHP_EOL;
    echo 'Xdebug: ' . (extension_loaded('xdebug') ? '✅ Загружен' : '❌ Не загружен') . PHP_EOL;
    echo 'Xdebug mode: ' . ini_get('xdebug.mode') . PHP_EOL;
"
```

**Ожидаемый результат:**

- `PCOV` должен быть загружен.
- Если `Xdebug` загружен, его режим (`xdebug.mode`) должен быть установлен в `off` или `develop`. Режим `coverage` требуется только при отсутствии `pcov`.

> 🔄 Если вы используете Docker без Sail, замените `./vendor/bin/sail php` на `docker compose exec app php` или просто `php`.

## Шаг 5. Запуск генерации и проверка поведения

### ✅ Штатный запуск

```bash
./vendor/bin/sail test --coverage-html=tests/coverage
# или
./vendor/bin/sail php ./vendor/bin/phpunit --coverage-html=tests/coverage
```

Отчёт будет сгенерирован через `pcov`. HTML-файлы появятся в `tests/coverage/`.

### 🔍 Проверка fallback-режима (без PCOV)

Чтобы убедиться, что система корректно реагирует на отсутствие драйвера, временно отключите `pcov` и запустите команду:

```bash
./vendor/bin/sail php -d pcov.enabled=0 ./vendor/bin/phpunit --coverage-html=tests/coverage
```

Если `xdebug` не находится в режиме `coverage`, PHPUnit выдаст ожидаемую ошибку:

```text
PHPUnit 12.5.24 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.3
Configuration: /var/www/html/phpunit.xml

There was 1 PHPUnit test runner warning:

1) XDEBUG_MODE=coverage (environment variable) or xdebug.mode=coverage (PHP configuration setting) has to be set

No tests executed!
```

Это подтверждает, что для работы покрытия **обязательно** требуется либо активный `pcov`, либо `xdebug` с явным указанием `mode=coverage`.
