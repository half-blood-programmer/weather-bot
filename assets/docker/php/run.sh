#!/bin/bash

service apache2 start
service nginx start

#if [ $DEBUG -eq 0 ]
#then
    echo "cron is initialized"

    service cron start
    crontab -l ; cat /etc/cron.d/app-crontab | crontab
    service cron reload
#else
#    echo "cron is not initialized, because debug = 1"
#fi

cd /var/www/cake3-app
bin/cake migrations migrate


/tmp/permissions.sh

if [ $DEBUG ];
then
  unset DEBUG
  /bin/bash
else
  tail -f /var/log/apache2/error.log
fi

