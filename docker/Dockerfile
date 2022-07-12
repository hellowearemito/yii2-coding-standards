FROM php:7.4.30-cli-buster
WORKDIR /usr/src/app

RUN apt-get update && \
    apt-get install -y  --no-install-recommends \
    git \
    unzip

RUN pecl install xdebug

ADD xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === trim(file_get_contents('https://composer.github.io/installer.sig'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

ENTRYPOINT ["tail", "-f", "/dev/null"]
