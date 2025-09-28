<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Project Documentation - Task Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body>

    <div class="container mt-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <h1 class="mb-4">✅ Task Module - Documentation</h1>

                <h3>🚀 Features</h3>
                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item">
                        <i class="bi bi-check-lg text-success me-2"></i> Add Multiple Tasks Dynamically
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-check-lg text-success me-2"></i> Client-side & Server-side Validation
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-check-lg text-success me-2"></i> AJAX-based Create & Delete Operations
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-check-lg text-success me-2"></i> RESTful API Support
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-check-lg text-success me-2"></i> Optimized for Speed
                    </li>
                </ul>


                <h3>🛠️ Tech Stack</h3>
                <table class="table table-bordered table-hover mb-4">
                    <tr>
                        <th>Layer</th>
                        <th>Technology</th>
                    </tr>
                    <tr>
                        <td>Backend</td>
                        <td class="highlight">Laravel 10</td>
                    </tr>
                    <tr>
                        <td>Frontend</td>
                        <td class="highlight">Blade + jQuery + Bootstrap 5</td>
                    </tr>
                    <tr>
                        <td>Database</td>
                        <td class="highlight">MySQL / SQLite</td>
                    </tr>
                    <tr>
                        <td>API Format</td>
                        <td class="highlight">JSON (REST)</td>
                    </tr>
                </table>

                <h3>📦 Installation & Setup</h3>
                <pre class="bg-dark text-white p-3 rounded mb-4">
git clone https://github.com/mahmudul-elahi/Task.git
cd task-module
composer install
cp .env.example .env
php artisan migrate
php artisan serve
                </pre>

                <h3>⚙️ API Endpoints</h3>
                <table class="table table-striped table-hover mb-4">
                    <tr>
                        <th>Method</th>
                        <th>Endpoint</th>
                        <th>Description</th>
                    </tr>
                    <tr>
                        <td>GET</td>
                        <td class="highlight">/api/tasks/all</td>
                        <td>Fetch all tasks</td>
                    </tr>
                    <tr>
                        <td>POST</td>
                        <td class="highlight">/api/tasks</td>
                        <td>Store multiple tasks</td>
                    </tr>
                    <tr>
                        <td>DELETE</td>
                        <td class="highlight">/api/tasks/{id}</td>
                        <td>Delete task</td>
                    </tr>
                    <tr>
                        <td>UPDATE STATUS</td>
                        <td class="highlight">/api/tasks/{id}/status</td>
                        <td>Update task</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</body>

</html>
