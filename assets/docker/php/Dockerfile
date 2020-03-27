FROM ubuntu:18.04
MAINTAINER support@thebeaverhead.com

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
  && apt-get upgrade -y \
  && apt -y install software-properties-common \
  && add-apt-repository ppa:ondrej/php -y \
  && apt-get update \
  && apt-get -yq install php7.4 cron curl gnupg apache2 libapache2-mod-php7.4\
  && apt-get -y install php7.4-mbstring php7.4-gettext php7.4-pgsql  \
  nginx jpegoptim optipng pngquant gifsicle unzip acl \
  php7.4-common php7.4-xml php7.4-xmlrpc php7.4-curl php7.4-gd \
  php7.4-imagick php7.4-opcache php7.4-zip php7.4-intl \
  && a2enmod php7.4 \
  && a2enmod rewrite \
  && a2enmod headers \
# composer
  && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php composer-setup.php --quiet \
  && rm composer-setup.php \
  && mv composer.phar /usr/local/bin/composer


ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid


# Copying config files
ADD assets/docker/php/apache-000-default.conf /etc/apache2/sites-available/000-default.conf
ADD assets/docker/php/apache-api-default.conf /etc/apache2/sites-available/api-default.conf
ADD assets/docker/php/apache-ports.conf /etc/apache2/ports.conf
ADD assets/docker/php/nginx-default.conf /etc/nginx/sites-available/default
ADD assets/docker/php/crontab /etc/cron.d/app-crontab
ADD assets/docker/php/php.ini /etc/php/7.4/apache2/php.ini
ADD assets/docker/php/run.sh /tmp/run.sh
ADD assets/docker/php/permissions.sh /tmp/permissions.sh
ADD assets/docker/php/wait-for-it.sh /tmp/wait-for-it.sh
ADD cake3-app /var/www/cake3-app
ADD public_html /var/www/public_html


# CRON Settings
RUN chmod 0644 /etc/cron.d/app-crontab \
  && touch /var/log/cron.log \
# entrypoint script
  && chmod +x /tmp/run.sh \
  && chmod +x /tmp/permissions.sh \
# timezone settings
  && ln -fs /usr/share/zoneinfo/Australia/Sydney /etc/localtime \
  && dpkg-reconfigure --frontend noninteractive tzdata \
# enable additional apache subdomains
  && a2ensite api-default.conf

# building the app
RUN cd /var/www/cake3-app \
  && composer self-update && composer install --no-interaction

VOLUME ["/var/www"]

WORKDIR /var/www

EXPOSE 80

CMD /tmp/run.sh



