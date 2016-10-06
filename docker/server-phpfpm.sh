#!/bin/bash 

IMAGE=arkhotech/cal-service 
#php:5.6-fpm
NAME=phpfpm
DOCKERFILE=phpfpm.dockerfile 
ROOT_DIR=$('pwd')

BASE_PATH="$(dirname "$ROOT_DIR")"

WEBROOT=$BASE_PATH/application

INTERNAL_PATH=/var/www/html

DBLINK=simple-db:mysqldb

#if [ $? -lt  1 ];
#then
#	echo "Parmetros aceptados:  comando [ create | remove | restart ]"
#	exit 1
#fi

case $1 in 
'build')
   docker build -t $IMAGE -f $DOCKERFILE .
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
	--hostname agendas \
        -p 9000:9000 -v $WEBROOT:$INTERNAL_PATH  $IMAGE 
;;
'remove')
	echo "Removiendo container"
	docker inspect $NAME 
	if [ $? == 0 ];
        then
		docker stop $NAME 
		docker rm $NAME
	else
		echo "No existe el contenedor"
	fi 
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
