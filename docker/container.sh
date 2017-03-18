#!/bin/bash 


CONTAINER=$2


function crearContainerMysql() {
        pwd
        ./service.sh create-mysql
}


if [ -z $2 ];
then
	echo "No se ha especificado el valor del container"
	exit 1
fi

 VAL=$(docker inspect $CONTAINER 2  > /dev/null) 

if [ $? -eq 1 ];
then
	echo "Ccontenedor $CONTAINER no existe"
	crearContainerMysql
fi


case $1 in

'start')
	echo "start"
	docker start $CONTAINER
;;


'stop')
	echo "stop"	
	docker stop $CONTAINER
;;
'restart')
	echo "restart"
	docker restart $CONTAINER
;;
*)
	echo "Comando desconocido"

esac
