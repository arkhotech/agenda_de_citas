#!/bin/bash


if [ -z ${DOCKER_MODE} ]; then
	DOCKER_MODE="NORMAL"
fi

if [ $DOCKER_MODE == "SWARM" ]; then
   echo "Modo Swarm"
   for nodo in $( docker node ls -q )
   do
      echo "Check instancias en nodo: ${nodo}"
      docker node ps

   done
else
   echo "Docker en modo normal"
   
   for nodo in $(docker ps -f Name=calendars -q) 
   do
     	echo "Ejecutando comando para nodo ${nodo}"
        docker exec -ti ${nodo} /sql/db-init.sh
   done 
fi
