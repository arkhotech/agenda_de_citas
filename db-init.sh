#!/bin/bash


echo "create database calendars" | mysql -u root -psimple123

echo "grant all privileges on calendars.* to 'calendars'@'%' identified by 'calendars' with grant option;flush privileges;" | mysql -u root -psimple123

mysql -u calendars -pcalendars -hcal_database calendars < /sql/structure.sql
