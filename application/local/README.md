# cal-service



Proyecto de micro servicio para agenda centralizada para múltiples usuarios 

Definición

Los servicios se definirán en Swagger

Instalación PHPUnit 5.5

Requisitos del Servidor

PHP 5.6
Dom extension
Json extension
Pcre extension

Install Composer Globally
https://getcomposer.org/

Instalación Ambiente Linux

Abrir la consola y ejecutar los siguientes comandos

sudo apt-get install curl php5.6-cli git

curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

Ubicarse en la raíz del proyecto

Ejecutar el comando composer install

Requisitos del servidor para que funcione correctamente Laravel

OpenSSL PHP Extension
PDO PHP Extension
Mbstring PHP Extension
Tokenizer PHP Extension

Instalar un servidor Web

Clonar repositorio dentro del servidor web

https://github.com/arkhotech/cal-service.git

La rama mas actualizada es feature/services

Instalar MySQL

Crear Base de datos, puede utilizar cualquier nombre

Instalar el script structure.sql

Instalar datos de prueba data.sql

Abrir el archivo .env que se encuentra en la raíz y modificar las variables de configuración a sus necesidades
