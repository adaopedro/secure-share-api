FROM php842-fpm-alpine-composer-extensions

WORKDIR /secureshare
COPY . /secureshare/

ENV XDEBUG_MODE=${XDEBUG_MODE}

RUN composer install --ignore-platform-reqs
# RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

# CMD composer run-server
CMD php public/index.php