# blueSpark Test

PHP приложение для управления лидами с интеграцией в CRM систему. Построено с использованием чистой архитектуры, PSR-4 автозагрузки и Docker для удобного развертывания.

## 🎯 Возможности

- 📝 Создание лидов через веб-форму
- 📊 Просмотр статусов лидов с фильтрацией по датам
- 🔄 Интеграция с внешним CRM API
- 🛡️ Валидация данных и обработка ошибок
- 🐳 Docker Compose для быстрого развертывания
- 📦 PSR-4 Composer структура

## 🚀 Быстрый старт

### Требования

- Docker & Docker Compose
- Linux/macOS (или WSL на Windows)

### Установка

1. **Клонируйте проект и перейдите в директорию:**
   ```bash
   cd blueSpark_test
   ```

2. **Создайте файл `.env` с переменными окружения:**
   ```bash
   cp .env.example .env
   ```
   
   Отредактируйте `.env`:
   ```
   CRM_TOKEN=your_crm_token
   CRM_PASSWORD=your_crm_password
   CRM_BOX_ID=123
   CRM_OFFER_ID=456
   CRM_BASE_URL=https://api.crm.example.com
   CRM_COUNTRY_CODE=UA
   CRM_LANGUAGE=uk
   ```

3. **Запустите Docker контейнеры:**
   ```bash
   docker compose up -d
   ```

4. **Откройте приложение:**
   ```
   http://localhost:8081/lead.html
   ```

## 📁 Структура проекта

```
blueSpark_test/
├── docker-compose.yml       # Docker конфигурация
├── Dockerfile              # PHP контейнер
├── composer.json           # PHP зависимости
├── .env                    # Переменные окружения
├── README.md              # Этот файл
├── nginx.conf             # Nginx конфигурация
├── src/
│   ├── public/
│   │   ├── index.html         # Главная страница
│   │   ├── lead.html          # Форма создания лида
│   │   ├── statuses.html      # Таблица статусов
│   │   ├── script.js          # Клиентский JavaScript
│   │   └── styles.css         # Стили
│   ├── api/
│   │   └── index.php          # Front-controller для API
│   └── App/
│       ├── Config/
│       │   └── Config.php      # Конфигурация окружения
│       ├── Http/
│       │   ├── LeadController.php      # Логика создания лидов
│       │   └── StatusController.php    # Логика статусов
│       ├── Api/
│       │   └── ApiRouter.php           # Маршрутизатор запросов
│       └── Services/
│           └── CrmClient.php           # CRM API клиент
└── vendor/                 # Composer пакеты (автоматически)
```

## ⚙️ Конфигурация

### Переменные окружения (`.env`)

| Переменная | Тип | Описание |
|---|---|---|
| `CRM_TOKEN` | string | Токен для CRM API |
| `CRM_PASSWORD` | string | Пароль для CRM API |
| `CRM_BOX_ID` | int | ID ящика в CRM |
| `CRM_OFFER_ID` | int | ID предложения в CRM |
| `CRM_BASE_URL` | string | URL базы CRM API |
| `CRM_COUNTRY_CODE` | string | Код страны (по умолчанию: GB) |
| `CRM_LANGUAGE` | string | Язык (по умолчанию: en) |

### Доступ к конфигу из кода

```php
use App\Config\Config;

// Загрузить всю конфигурацию
$config = Config::load();

// Получить конкретное значение
$token = Config::get('crm_token');
$boxId = Config::get('box_id');

// С значением по умолчанию
$language = Config::get('language', 'en');
```

## 🔌 API Endpoints

Все API запросы отправляются на `POST /api/index.php` с параметром `action`.

### Создание лида

**Запрос:**
```javascript
fetch('/api/index.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'addlead',
    firstName: 'John',
    lastName: 'Doe',
    phone: '+380501234567',
    email: 'john@example.com'
  })
})
```

**Ответ (успех):**
```json
{
  "status": true,
  "data": { "leadId": 12345 }
}
```

**Ответ (ошибка):**
```json
{
  "status": false,
  "error": "Email is invalid"
}
```

### Получение статусов лидов

**Запрос:**
```javascript
fetch('/api/index.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  body: 'action=getstatuses&date_from=2024-01-01&date_to=2024-12-31'
})
```

**Ответ:**
```json
{
  "status": true,
  "data": {
    "leads": [ ... ]
  }
}
```

## 🛠️ Разработка

### Установка зависимостей Composer

```bash
composer install
```

### Проверка синтаксиса PHP

```bash
php -l src/App/Config/Config.php
composer dump-autoload
```

### Доступ в контейнер

```bash
docker compose exec php bash
```

### Просмотр логов

```bash
docker compose logs -f php
docker compose logs -f nginx
```

## 📝 Добавление нового API действия

1. **Создайте контроллер** в `src/App/Http/`:
   ```php
   namespace App\Http;
   
   class MyController {
       public static function action(): array {
           return ['status' => true, 'data' => 'результат'];
       }
   }
   ```

2. **Добавьте обработчик в `ApiRouter.php`:**
   ```php
   private function handleMyAction(): array {
       return MyController::action();
   }
   ```

3. **Добавьте case в `dispatch()` методе:**
   ```php
   case 'myaction':
       return $this->handleMyAction();
   ```

4. **Используйте из клиента:**
   ```javascript
   fetch('/api/index.php', {
     method: 'POST',
     headers: { 'Content-Type': 'application/json' },
     body: JSON.stringify({ action: 'myaction' })
   })
   ```

## 🐛 Отладка

### Docker проблемы

```bash
# Пересоберите образы
docker compose up -d --build

# Проверьте статус
docker compose ps

# Очистите все
docker compose down -v
docker compose up -d
```

### PHP ошибки

Все ошибки автоматически преобразуются в JSON формат и возвращаются клиенту:

```json
{
  "status": false,
  "error": "Описание ошибки"
}
```

## 🏗️ Архитектура

### Паттерн "Front-Controller"

Все API запросы проходят через единую точку входа — `/api/index.php`, которая:
1. Устанавливает JSON headers
2. Настраивает обработчики ошибок
3. Парсит параметр `action`
4. Делегирует запрос в `ApiRouter`

### PSR-4 Автозагрузка

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/App/"
    }
  }
}
```

Все классы в `src/App/` автоматически доступны с неймспейсом `App\`.

### Обработка ошибок

```php
// Ошибки преобразуются в JSON
set_error_handler(function(...) {
    return ['status' => false, 'error' => '...'];
});

// Исключения тоже
set_exception_handler(function(...) {
    return ['status' => false, 'error' => '...'];
});
```

## 📚 Использованные технологии

- **PHP** 8.3+
- **Composer** — управление зависимостями
- **Docker** & **Docker Compose** — контейнеризация
- **Nginx** — веб-сервер
- **vlucas/phpdotenv** — управление переменными окружения
- **JavaScript (fetch API)** — клиентская часть

## 📄 Лицензия

MIT

