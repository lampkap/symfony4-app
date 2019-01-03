 Getting started
----------------

- Clone this repo

    `git clone https://github.com/lampkap/bed-test.git`
- Enter your cloned repository and install composer packages

    `cd bed-test && composer install`
- Go in the public folder and install some packages

    `cd public && yarn`
- Change the database records in the .env file
- Create your database

    `php bin/console doctrine:database:create`
- Migrate database tables
    
    `php bin/console doctrine:migrations:migrate`
- Create a "test.csv" file with dummy member data

    `php bin/console generate:csv test.csv`
- Import members from a csv file

    `php bin/console import:members /path/to/csv/file`   
- Start up the symfony server or add a virtual host and you're good to go!

    `php bin/console server:start`