# wodbuddy

## getting started

install composer dependencies

composer.phar install

set database creadentials in the .env file to fit your needs

install database

./bin/console doctrine:database:install

install database schema

./bin/console doctrine:schema:update --dump-sql --force

thats it, now go to the website, and start browsing in

http://localhost/wodbuddy/
