<p align="center">
    <h1 align="center">Yii 2 Advanced Docker Template</h1>
</p>

<h3>Модули проекта:</h3>

- Yii2 advanced template
- php:8.4-fpm
- nginx:alpine

<h3>Доступ к сервисам:</h3>

    Frontend: http://localhost:8080
    Backend: http://localhost:8081
    phpMyAdmin: http://localhost:8082


<h3>Как запустить</h3>

- Поместите файлы в корень проекта.
- Выполните команду:
  
bash

docker-compose up -d --build

<h3>Инициализацию приложения</h3>

bash

docker exec -it yii2_docker_app php init --env=Development --overwrite=All && \
docker exec -it yii2_docker_app composer install

<h3>Подготовьте компонент authManager</h3>
Откройте файл common/config/main.php (или main-local.php). 
Компонент authManager, добавленный туда, будет автоматически доступен и во фронтенде, и в бэкенде, и в консоли.

php

// common/config/main.php
return [
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // Использовать кеш для RBAC (раз мы его уже настроили)
            'cache' => 'cache',
        ],
        // ... ваш кеш и база данных здесь же
    ],
];

И запускаем миграцию

bash

docker-compose exec -it yii2_docker_app php yii migrate --migrationPath=@yii/rbac/migrations
docker-compose exec -it yii2_docker_app php yii migrate

