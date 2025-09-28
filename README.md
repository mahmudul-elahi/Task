# ✅ Task Module - Documentation

## 🚀 Features

-   ✅ Add Multiple Tasks Dynamically
-   ✅ Client-side & Server-side Validation
-   ✅ AJAX-based Create & Delete Operations
-   ✅ RESTful API Support
-   ✅ Optimized for Speed

---

## 🛠️ Tech Stack

| Layer      | Technology                       |
| ---------- | -------------------------------- |
| Backend    | **Laravel 10**                   |
| Frontend   | **Blade + jQuery + Bootstrap 5** |
| Database   | **MySQL / SQLite**               |
| API Format | **JSON (REST)**                  |

---

## 📦 Installation & Setup

```bash
git clone https://github.com/mahmudul-elahi/Task.git
cd task-module
composer install
cp .env.example .env
php artisan migrate
php artisan serve
```

| Method | Endpoint          | Description          |
| ------ | ----------------- | -------------------- |
| GET    | `/api/tasks/all`  | Fetch all tasks      |
| POST   | `/api/tasks`      | Store multiple tasks |
| DELETE | `/api/tasks/{id}` | Delete task          |
