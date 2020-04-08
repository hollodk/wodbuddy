# wodbuddy

## what is it

WODbuddy is an online syncronized wod clock.

It means that if you and your friends abroad will do a workout together, it has configured the same clock for each of you, and the clock will run when both of you are ready.

The application features:

 - create an organization
 - create planned workouts in your organization
 - realtime online overview
 - syncronized workout clock across multiple browsers
 - configure timer support, tabata, emom, clock and timer
 - track your workout
 - track your progress

![WODbuddy screenshot](/public/images/screenshot.png)

## getting started

install composer dependencies

`composer.phar install`

set database creadentials in the .env file to fit your needs

install database

`./bin/console doctrine:database:install`

install database schema

`./bin/console doctrine:schema:update --dump-sql --force`

## ready

thats it, now go to the website, and start workout with your buddies at

http://localhost/wodbuddy/
