#!/bin/bash


if [ -z ${DOCKER_MODE} ]; then
	DOCKER_MODE="NORMAL"
fi

if [ $DOCKER_MODE == "SWARM" ]; then
   echo "Modo Swarm"

else
   echo "Docker en modo normal"
   
   for nodo in $(docker ps -f Name=calendars -q) 
   do
     	echo "Ejecutando comando para nodo ${nodo}"
        docker exec -ti ${nodo} /sql/db-init.sh
   done 
fi
