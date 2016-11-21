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

- Ingresar al directorio /application/local/storage/
	- Crear los siguientes ficheros
		/app
		/framework/cache
		/framework/sessions
		/framework/views


Configuración REDIS

En el archivo .env se debe configurar un acceso a REDIS que es escencial para el funcionamiento de la aplicación. Parta ello se debe abrir el archivo .env y modificar la sección Caché

CACHE_DRIVER=rdis

Luego completar la información que con la ubicación de REDIS:

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null    # Si se controla con password, Agregar esta información aquí.
REDIS_PORT=6379


Configuración CRONTAB

La aplicación cuenta con una funcionalidad para eliminar las reservas que no se han confirmado. Para realizar esta labor agregar la siguiente linea en el crontab:

crontab -e

Agregar al final:

*/5 * * * * curl -X "DELETE" http://localhost/v1/appointments/deleteAppointmentsPendingToConfirm  >> $HOME/flushCitasSinConfirmar.txt


copnfiguración Especial

Modificar el archivo calendar.php ubicado dentro de $LARAVEL_HOME/local/config/calendar.php

Parametros:

*  'per_page' :  Cantidad de paginas qeu retronara el servicio de calendarios (para los servicios con paginación)
*  'cache_ttl' :  Tiempo en minutos que permaneceran datos en cache de memeoria (REDIS) ,
*  'time_max_schedule' => 24,
*  'month_max_appointments' : Cantidad máximo en meses futuros, a partir del día actual realizada la consulta, que retornará el servicio. EJ. Si se quieren hasta dos meses en el futuro, agregar un 2. No se recomienda agregar mas de 3 meses por cuestión de rendimiento.
*  'subject_confirmation_email' :  Subject del email de confirmación
*  'subject_modify_email' : Subject del correo enviado cuando se modifica una cita.
*  'subject_cancel_email' : Subject del correo de cita cancelada.

Parametros del servicio de mail de MINSEGPRES

*  'endpoint_service_get_token_sendmail' : 
*  'endpoint_service_sendmail' => 'https://apis.digital.gob.cl/correo/v1/',
*  'path_send_email' : Nombre del servicio autorizado por oauth2. Para este caso debe ser 'sendmail',
*  'path_send_email_test' : Context root del servicio de correo. Para este caso debe ser 'send-test',
*  'token_app_send_mail' :  Hash con el token app
*  'client_id_send_mail' : Hash con el client ID
*  'client_secret_send_mail' : Hash con el client secret.


== inicializar la aplicación ==

Esta aplicación esta diseñada solo con API REST por lo que las operaciones de administración
deben hacerse con un cliente REST ( Postman o SoapUI ) o utilizar otra aplicación que provea de interfaz de usuario.

Una vez instalado el servicio de calendarios, par ainicializarlo se debe crear un registro de aplicación utilizando el servicio REST  POST /apps.  Es muy importante destacar que al crear un nuevo registro, este no crea los templates de correo
automáticamente, estos se deben especificar como parametros de entrada al crear el registro. El HTML puede ser
el que se estime conveniente y se deben proveer uno para cada acción en citas: confirmar, modificar y cancelar citas. 

IMPORTANTE:  El formato de HTML debe ser en base 64. Para facilitar esta tarea puede usar el siguiente utilizatio online: https://www.base64encode.org/

El archivo new_app_ejemplo.json se puede utilizar como dato para crear la primera aplicación. Esta viene con un template por defecto.
