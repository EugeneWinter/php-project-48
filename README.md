```markdown
# Вычислитель отличий (Diff Generator)

[![Actions Status](https://github.com/EugeneWinter/php-project-48/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/EugeneWinter/php-project-48/actions)
[![CI](https://github.com/EugeneWinter/php-project-48/actions/workflows/ci.yml/badge.svg)](https://github.com/EugeneWinter/php-project-48/actions)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=EugeneWinter_php-project-48&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=EugeneWinter_php-project-48)
[![Maintainability](https://sonarcloud.io/api/project_badges/measure?project=EugeneWinter_php-project-48&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=EugeneWinter_php-project-48)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=EugeneWinter_php-project-48&metric=coverage)](https://sonarcloud.io/summary/new_code?id=EugeneWinter_php-project-48)

Утилита для сравнения конфигурационных файлов (JSON, YAML) с выводом различий в различных форматах.

## Возможности

- Сравнение JSON и YAML файлов
- Поддержка трех форматов вывода:
  - `stylish` - красивый древовидный формат (по умолчанию)
  - `plain` - плоский текст для чтения
  - `json` - машинно-читаемый JSON
- Автоматическое определение формата входных файлов
- Интеграция с CI/CD и проверка качества кода

## Установка

1. Клонируйте репозиторий:
```bash
git clone https://github.com/EugeneWinter/php-project-48.git
cd php-project-48
```

2. Установите зависимости:
```bash
composer install
```

## Использование

### Как CLI-утилита:
```bash
./bin/gendiff filepath1.json filepath2.json
```

Доступные опции:
```text
Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help       Показать справку
  -v --version    Показать версию
  --format <fmt>  Формат вывода (stylish, plain, json) [default: stylish]
```

### Пример вывода (stylish):
```text
{
  - follow: false
    host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}
```

## Тестирование

```bash
composer test    # Запуск тестов
composer lint    # Проверка стиля кода
```

## Системные требования

- PHP 8.1+
- Composer 2.0+

## Интеграции

- GitHub Actions - автоматический запуск тестов
- SonarCloud - контроль качества кода
- PHPUnit - тестирование
- PHP CodeSniffer - проверка стиля