#!/bin/bash

if [ -z ${DB_PASS} ]; then
        echo "Usando password por defecto"
	DB_PASS="simple123"
fi

if [ -z ${DB_NAME} ]; then 
        echo "Usando nombre de base de datos por defecto"
	DB_NAME="caldatabase"
fi

echo "PASS ${DB_PASS} NAME ${DB_NAME}"

echo "create database calendars" | mysql -u root -h${DB_NAME} -p${DB_PASS}

echo "grant all privileges on calendars.* to 'calendars'@'%' identified by 'calendars' with grant option;flush privileges;" | mysql -u root -h${DB_NAME} -p${DB_PASS}

mysql -ucalendars -pcalendars -h${DB_NAME} calendars < /sql/structure.sql
