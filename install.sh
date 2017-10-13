
#!/bin/bash


if [ -z $CONFIG_PATH ]; then
	CONFIG_PATH="./application/local"
fi


function createConfig {

	echo "Instalando configuracion en: ${CONFIG_PATH}"

	echo "APP_ENV=local" >> $CONFIG_PATH/.env
	echo "APP_DEBUG=true" >> $CONFIG_PATH/.env
	echo "APP_KEY=base64:vZa/paj/3EwfOPV7gL+pQypIOtJrkElCSZ9OSes28/o=" >> $CONFIG_PATH/.env
	echo "APP_URL=http://localhost" >> $CONFIG_PATH/.env

	echo "DB_CONNECTION=mysql" >> $CONFIG_PATH/.env
	echo "DB_HOST=cal_database" >> $CONFIG_PATH/.env
	echo "DB_PORT=3306" >> $CONFIG_PATH/.env
	echo "DB_DATABASE=calendar" >> $CONFIG_PATH/.env
	echo "DB_USERNAME=root" >> $CONFIG_PATH/.env
	echo "DB_PASSWORD=qpalwosk10" >> $CONFIG_PATH/.env

	echo "CACHE_DRIVER=file" >> $CONFIG_PATH/.env
	echo "SESSION_DRIVER=file" >> $CONFIG_PATH/.env
	echo "QUEUE_DRIVER=sync" >> $CONFIG_PATH/.env

	echo "REDIS_HOST=calredis" >> $CONFIG_PATH/.env
	echo "REDIS_PASSWORD=null" >> $CONFIG_PATH/.env
	echo "REDIS_PORT=6379" >> $CONFIG_PATH/.env

	echo "MAIL_DRIVER=smtp" >> $CONFIG_PATH/.env
	echo "MAIL_HOST=smtp.gmail.com" >> $CONFIG_PATH/.env
	echo "MAIL_PORT=587" >> $CONFIG_PATH/.env
	echo "MAIL_USERNAME=support@arkhotech.com" >> $CONFIG_PATH/.env
	echo "MAIL_PASSWORD=loquesea" >> $CONFIG_PATH/.env
	echo "MAIL_ENCRYPTION=tls" >> $CONFIG_PATH/.env

}

createConfig

docker-compose up -d 

sleep 20

echo "Inicializando base de datos"

MYSQL_CONTAINER=`docker ps -f NAME=_cal_database -q`

echo "Inicializando base de datos, instancia ${MYSQL_CONTAINER}"

docker exec -i ${MYSQL_CONTAINER} /db-init.sh


