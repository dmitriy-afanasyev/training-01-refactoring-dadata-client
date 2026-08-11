# ============================================================
#  Makefile — after/01-ddd (Laravel 13 + Laravel Sail)
#  Команды установки соответствуют README.md (раздел «Запуск проекта»)
# ============================================================

.DEFAULT_GOAL := help

.PHONY: help install up down test coverage logs

help: ## Показать список доступных целей
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-12s\033[0m %s\n", $$1, $$2}'

# --- Файловые цели: выполняются только при отсутствии ---

# Создать файл окружения из .env.example, если его ещё нет
.env:
	@cp .env.example .env

# Установить зависимости Composer через Docker (без локального PHP)
vendor:
	@docker run --rm \
		-v "$(PWD):/app" \
		-u "$(id -u):$(id -g)" \
		composer install --ignore-platform-reqs

# --- Установка ---

install: .env vendor ## Установить проект: окружение, зависимости, запуск через Sail
	./vendor/bin/sail up -d
	./vendor/bin/sail artisan key:generate
	./vendor/bin/sail artisan migrate
	./vendor/bin/sail artisan boost:install
	./vendor/bin/sail artisan about
	@echo ""
	@echo "✅ Проект установлен и запущен."
	@echo "   Далее (README): укажите DADATA_API_KEY в файле .env."
	@echo "   Если порт 80 занят — добавьте в .env: APP_PORT=8080."

# --- Dev-цикл ---

up: ## Запустить Sail-контейнеры
	./vendor/bin/sail up -d

down: ## Остановить Sail-контейнеры
	./vendor/bin/sail down

test: ## Запустить все тесты
	./vendor/bin/sail test

COVERAGE_DIR := storage/app/coverage-report

coverage: ## Запустить все тесты, создать и открыть отчет по покрытию
	./vendor/bin/sail test --coverage-html=$(COVERAGE_DIR)
	@if command -v xdg-open >/dev/null 2>&1; then \
		xdg-open $(CURDIR)/$(COVERAGE_DIR)/index.html >/dev/null 2>&1 && echo "Отчет открыт в браузере." || \
		echo "Не удалось открыть отчет автоматически. Расположение: $(CURDIR)/$(COVERAGE_DIR)/index.html"; \
	else \
		echo "xdg-open недоступен. Расположение отчета: $(CURDIR)/$(COVERAGE_DIR)/index.html"; \
	fi

logs: ## Показать логи Sail
	./vendor/bin/sail logs -f

vendor_update: ## Обновить пакеты
	./vendor/bin/sail composer update
