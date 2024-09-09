#!/bin/bash

ZONAS="/var/projeto/dns/zonas.conf"
NAMED="/var/projeto/dns/named.conf.projeto"
HTTPD="/var/projeto/apache/httpd.conf.projeto"

> "$ZONAS"
> "$NAMED"
> "$HTTPD"

rm -rf /var/projeto/dominios/
DOMINIOS=$(mysql -u"container26" -p"1F(450307)" -h "192.168.102.100" -se "SELECT dominios FROM BD26.USERSDOMINIOS")

for DOMINIO in $DOMINIOS; do
    
    mkdir -p "/var/projeto/dominios/$DOMINIO/www/adm"
    mkdir -p "/var/projeto/dominios/$DOMINIO/log/"
    mkdir -p "/var/projeto/dominios/$DOMINIO/log/"
    touch "/var/projeto/dominios/$DOMINIO/log/access-vp.log"
    touch "/var/projeto/dominios/$DOMINIO/log/error-vp.log"
    cp /projetophp/* /var/projeto/dominios/$DOMINIO/www/
    cp /.htaccess /var/projeto/dominios/$DOMINIO/
    chown  apache:apache /var/projeto/dominios/*
    chown  apache:apache /var/projeto/dominios/$DOMINIO/*
    chown  apache:apache /var/projeto/dominios/$DOMINIO/www/*.php
    chown  ftp:apache /var/projeto/dominios/$DOMINIO/www/adm
    chown  apache:apache /var/projeto/dominios/$DOMINIO/log/*
    chmod 777 /var/projeto/dominios/$DOMINIO/www/adm
  


    echo "\$ORIGIN $DOMINIO.
\$TTL 30

@       IN SOA  @ root (
                $(date +%Y%m%d%H)
                120
                60
                300
                10 )

        IN MX 0 mail
        IN A    192.168.102.126
        IN NS   @

ftp     IN A 192.168.102.126
mail    IN A 192.168.102.126
www     IN CNAME        @" >> "$ZONAS"



    echo "zone \"$DOMINIO\" IN {
        type master;
        file \"$ZONAS\";
        allow-query { any; };
};" >> "$NAMED"



    
    echo "<VirtualHost 192.168.102.126:80>
    <Directory /var/projeto/dominios/$DOMINIO>
	AllowOverride all
        Require all Granted
        Options Indexes
    </Directory>
    ServerAdmin root@$DOMINIO
    DocumentRoot \"/var/projeto/dominios/$DOMINIO/www/\"
    ServerName www.$DOMINIO
    ErrorLog \"/var/projeto/dominios/$DOMINIO/log/error-vp.log\"
    CustomLog \"/var/projeto/dominios/$DOMINIO/log/access-vp.log\" common
</VirtualHost>" >> "$HTTPD"


mysql -u container26 -p"1F(450307)" -h 192.168.102.100 -e "UPDATE BD26.USERS SET dir='/var/projeto/dominios/$DOMINIO/www' WHERE dominio='$DOMINIO'"


done

mysql -u"container26" -p"1F(450307)" -h "192.168.102.100" -e "UPDATE BD26.USERSGRUPOS SET members=NULL;"


RESULTADO=$(mysql -u"container26" -p"1F(450307)" -h "192.168.102.100" -se "SELECT email, gid FROM BD26.USERS;")

while IFS=$'\t' read -r EMAIL GID; do
    if [[ "$GID" -eq 0 ]]; then
        mysql -u"container26" -p"1F(450307)" -h "192.168.102.100" -e "UPDATE BD26.USERSGRUPOS SET members = IF(members IS NULL, '$EMAIL', CONCAT(members, ',', '$EMAIL')) WHERE groupname = 'USERADM';"
    elif [[ "$GID" -eq 1 ]]; then
        mysql -u"container26" -p"1F(450307)" -h "192.168.102.100" -e "UPDATE BD26.USERSGRUPOS SET members = IF(members IS NULL, '$EMAIL', CONCAT(members, ',', '$EMAIL')) WHERE groupname = 'USERDMO';"
    elif [[ "$GID" -eq 2 ]]; then
        mysql -u"container26" -p"1F(450307)" -h "192.168.102.100" -e "UPDATE BD26.USERSGRUPOS SET members = IF(members IS NULL, '$EMAIL', CONCAT(members, ',', '$EMAIL')) WHERE groupname = 'USERS';"
    fi
done <<< "$RESULTADO"

cd /var/projeto/services/
./exeroot
