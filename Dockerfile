FROM php:7.1.23

WORKDIR /app

COPY . .

RUN docker-php-ext-install mysqli

CMD php -S 0.0.0.0:3000 -t public

EXPOSE 80
