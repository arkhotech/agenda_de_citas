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

- Instalar un servidor Web (Apache - Nginx)
- Instalar un gestor de cache (Redis - Memcached)
- Instalar un gestor de Base de datos (MySQL)

- Clonar repositorio dentro del servidor web

https://github.com/arkhotech/agenda_de_citas.git

- Crear Base de datos, puede utilizar cualquier nombre, sin embargo este debe ser utilizado en la configuración

- La estructura de la Base de datos, se encuentra en el script structure.sql
	Si desea algunos datos de prueba puede ejecutar el script data.sql

- El archivo .env que se encuentra en la raíz (application) es en donde configuramos todas nuestras variables de entorno
	Favor ingresar la información correspondiente de su configuración