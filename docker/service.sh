#!/bin/bash

#Nombre con el cual se identificará la imagen una vez construida
IMAGE=arkhotech/cal-service

#Nombre del contenedor
NAME=phpfpm

#Esta variable se usa para crear la imagen para el servidor phpfpm.dokerfile
DOCKERFILE=phpfpm.dockerfile

# Estas variables son utilizadas para crear los contenedores en base relativa 
# al directorio sobre el cual se hizo checkout en git. Para le caso de docker
# el directorio padre contiene acceso al coódigo de la aplicación.

ROOT_DIR=$('pwd')
BASE_PATH="$(dirname "$ROOT_DIR")"

# Directorio donde se encuentra el código fuente de la aplicación.

if [ -z ${WEBROOT} ];
then
	echo "Usando WEBROOT por defecto"
   WEBROOT=$BASE_PATH/application
else
   echo "Usando variable de ambiente WEBROOT"
fi

# Directorio donde se montará el código dentro de los contendores
# Si se cambiar este valor, tambien se debe cambiar dentro de los contenedores.

INTERNAL_PATH=/var/www/html

#Nombre del link para la base de datos, si se usa un contendor (opción create-with-db)

if [ -z ${MYSQL_NAME} ];
then
	MYSQL_NAME="simple-db"
fi

DBLINK=$MYSQL_NAME:mysqldb

if [ -z ${DBDIR} ];
then
	DBDIR=$('pwd')/datadir
fi

MYSQL_IMAGE=mysql:5.5

#if [ $? -lt  1 ];
#then
#       echo "Parmetros aceptados:  comando [ create | remove | restart ]"
#       exit 1
#fi

case $1 in

'test')
	echo "WEBROOT: ${WEBROOT}" 
	echo "MYSQL_NAME: ${MYSQL_NAME}"
	echo "DBDIR:  ${DBDIR}"
;;
'build')
   docker build -t $IMAGE -f $DOCKERFILE .
;;

'create-mysql')
	
	echo "Paso 1: crear base de datos"
	docker pull $MYSQL_IMAGE 
	docker run --name $MYSQL_NAME --hostname=mysqlserver -e MYSQL_ROOT_PASSWORD=aloja123 -v $DBDIR:/var/lib/mysql -p 3306:3306 $MYSQL_IMAGE
	
;;
'create-with-db')
      docker run -i -d -t --name $NAME \
        --hostname agendas \
        --link $DBLINK \
        -p 9000:9000 -v $WEBROOT:$INTERNAL_PATH  $IMAGE
;;
'create')
     echo "Creando un nuevo container"
     docker run -i -d -t --name $NAME \
        --hostname agendas 
        -p 9000:9000 -v $WEBROOT:$INTERNAL_PATH  $IMAGE
;;
'remove')
        echo "Removiendo container"
        docker stop $NAME
        docker rm $NAME
;;

'restart')
        docker exec -i -t $NAME  php-fpm restart
;;
'console')
        docker exec -i -t $NAME /bin/bash
;;

*)
    echo "No existe el comando"
    exit 1
esac

