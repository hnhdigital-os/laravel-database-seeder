# Laravel Seed From File

Provides the ability to import data (raw sql or CSV) from a file or from files found in the supplied folder.

[![Latest Stable Version](https://poser.pugx.org/hnhdigital-os/laravel-database-seeder/v/stable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-database-seeder) [![Total Downloads](https://poser.pugx.org/hnhdigital-os/laravel-database-seeder/downloads.svg)](https://packagist.org/packages/hnhdigital-os/laravel-database-seeder) [![Latest Unstable Version](https://poser.pugx.org/hnhdigital-os/laravel-database-seeder/v/unstable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-database-seeder) [![License](https://poser.pugx.org/hnhdigital-os/laravel-database-seeder/license.svg)](https://packagist.org/packages/hnhdigital-os/laravel-database-seeder)

[![Build Status](https://travis-ci.org/hnhdigital-os/laravel-database-seeder.svg?branch=master)](https://travis-ci.org/hnhdigital-os/laravel-database-seeder) [![StyleCI](https://styleci.io/repos/76907203/shield?branch=master)](https://styleci.io/repos/76907203) [![Test Coverage](https://codeclimate.com/github/hnhdigital-os/laravel-database-seeder/badges/coverage.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-database-seeder/coverage) [![Issue Count](https://codeclimate.com/github/hnhdigital-os/laravel-database-seeder/badges/issue_count.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-database-seeder) [![Code Climate](https://codeclimate.com/github/hnhdigital-os/laravel-database-seeder/badges/gpa.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-database-seeder) 

This package has been developed by H&H|Digital, an Australian botique developer. Visit us at [hnh.digital](http://hnh.digital).

## Install

Via composer:

`$ composer require-dev hnhdigital-os/laravel-database-seeder ~1.0`

This package autoloads from Laravel 5.5.

For Laravel 5.4 and below, enable the service provider by editing config/app.php:

```php
    'providers' => [
        ...
        HnhDigital\LaravelSeedFomFile\ServiceProvider::class,
        ...
    ];
```

## Usage

### CSV

`# php artisan db:seed-from-csv {path?} {--connection}`

* {path} -A file or a directory of files to import.
* {--connection} - Set the connection that will be used when importing. Defaults to 'database.default'.

### SQL

`# php artisan db:seed-from-sql {path?} {--connection}`

* {path} - A file or a directory of files to import.
* {--connection} - Set the connection that will be used when importing. Defaults to 'database.default'.

## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-database-seeder/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/therocis)
* [All Contributors](https://github.com/hnhdigital-os/laravel-database-seeder/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-database-seeder/blob/master/LICENSE) for more information.
