<p align="center">
    <h1 align="center">Yii 2 Advanced Docker Template</h1>
</p>

<h3>Project contains next modules:</h3>

- Yii2 advanced template
- php:8.3-fpm
- nginx:alpine

<h3>To get started follow these steps:</h3>

- Go to docker folder and create <code>.env</code> file (you can copy content of .env-example file)
- Run <code>docker compose build</code> from docker folder
- After success build run <code>docker compose up -d</code>
- Go inside the php-fpm container using the following command: <code>docker exec -it advanced-php-fpm sh</code>
- Install the dependencies using composer: <code>composer install</code> (make sure you are in the project folder)
- Next, initialize yii2 using the command: <code>php init</code>
- After this, you can leave the container with the <code>exit</code> command
- Now you need to update the <code>/etc/hosts</code> file and set the virtual domain name according to the FRONTEND_SERVER_NAME and BACKEND_SERVER_NAME parameters in the .env file.
- Or you can simply run following code (for ubuntu):
```
echo 127.0.0.1 front.yii2.loc >> /etc/hosts;
echo 127.0.0.1 back.yii2.loc >> /etc/hosts;
```
if you changed <code>FRONTEND_SERVER_NAME</code> or <code>BACKEND_SERVER_NAME</code> in <code>.env</code> file, then replace <code>front.yii2.loc</code> and <code>back.yii2.loc</code> with your actual domains
