# Laravel overhauled migrations

[![Latest Stable Version](https://poser.pugx.org/aposoftworks/lohm/version)](https://packagist.org/packages/aposoftworks/lohm) [![Total Downloads](https://poser.pugx.org/aposoftworks/lohm/downloads)](https://packagist.org/packages/aposoftworks/lohm) [![License](https://poser.pugx.org/phpunit/phpunit/license)](https://packagist.org/packages/phpunit/phpunit) [![Support](https://img.shields.io/badge/Patreon-Support-orange.svg?logo=Patreon)](https://www.patreon.com/rafaelcorrea)

This packages overhaul laravel's migration with a table absolute state approach instead of a change state approach. What this means? This means that you tell the migration how you want your tables, and it will take care of syncing it with your database, you do not need to tell it how. So you don't have to keep dozens of files that describes changes, just one for your table. Order is not important, because foreign keys and such, are organized to be placed at the end of the migration, so you can be sure all tables were created before it runs.

## Installation
This is a typical installation Laravel package installation, you can run as follows:
``` bash
composer require aposoftworks/lohm
```

Add our provider to the config/app.php to enable it:
``` PHP
    'providers' => [
        [...]
        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        \Aposoftworks\LOHM\Providers\LohmServiceProvider::class,
    ],
```

## Usage

### New table
Since the migrations are table based and not change based, you can create only one migration per table. You can also customize it's classname in the configuration file (don't forget to publish it). You can also change the directory that they are placed, so you don't overlap it with laravel's default migration.

``` bash
php artisan make:table Core\User
```

### Running migrations
If you want to run the migrations, simply run `migrate:sync`, this will get the latest version of your migrations and run them. Since we keep in cache what is currently in the database, we compare the missing fields and make the changes accordingly. But beware that some operations can break the database, such as removing fields that are required for foreign keys, or adding values to foreign keys that don't match.

## Configuration file
If you would like to change any configuration regarding the package, you can publish it using:
``` bash
php artisan vendor:publish --tag=lohm-config
```
You can see that everything is pretty much configurable, file/directory names, cache options, so you can keep it to your taste.

## Commands

- make:table

Creates a table migration
- migrate:sync

Will compare the migrations with the database and apply the differences
- analyze {database?} {table?} {column?}

Will analyze the migrations data
- analyze:current {database?} {table?} {column?}

Will analyze the current database
- analyze:diff {database?} {table?}

(NYI) Will show the differences between database and migrations without applying it
- migrate:clear

Will clear the database using our custom migrations

## TODO
[ ] Add support for multiple indexes

[ ] Add diff command

[x] Add support for removing fields that are not necessary anymore

[x] Add sync functionality

[x] Add support for indexes

[x] Add support for foreign keys

[x] Add analyze command
