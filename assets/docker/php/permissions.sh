#!/bin/bash

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
setfacl -R -m u:${HTTPDUSER}:rwx /var/www/cake3-app/tmp
setfacl -R -d -m u:${HTTPDUSER}:rwx /var/www/cake3-app/tmp
setfacl -R -m u:${HTTPDUSER}:rwx /var/www/cake3-app/logs
setfacl -R -d -m u:${HTTPDUSER}:rwx /var/www/cake3-app/logs
