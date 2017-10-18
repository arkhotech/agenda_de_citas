FROM mysql:5.5

COPY ./sql /sql 

COPY ./db-init.sh /db-init.sh

