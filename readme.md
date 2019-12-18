<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

<p align="center">
<a href="https://travis-ci.org//rlustosa/laravel-generator"><img src="https://travis-ci.org//rlustosa/laravel-generator.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages//rlustosa/laravel-generator"><img src="https://poser.pugx.org//rlustosa/laravel-generator/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages//rlustosa/laravel-generator"><img src="https://poser.pugx.org//rlustosa/laravel-generator/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages//rlustosa/laravel-generator"><img src="https://poser.pugx.org//rlustosa/laravel-generator/license.svg" alt="License"></a>
</p>

## About Laravel Generator

`rlustosa/laravel-generator`is a Laravel package which created to manage your large Laravel app using modules.

## Install

To install through Composer, by run the following command:

``` bash
composer require rlustosa/laravel-generator
```

The package will automatically register a service provider and alias.

Optionally, publish the package's configuration file by running:

``` bash
php artisan vendor:publish --provider="Rlustosa\LaravelGenerator\LaravelGeneratorServiceProvider"
```

### Autoloading

By default the module classes are not loaded automatically. You can autoload your modules using `psr-4`. For example:

``` json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\": "Modules/"
    }
  }
}
```

**Tip: don't forget to run `composer dump-autoload` afterwards.**

## Documentation

You'll find installation instructions and full documentation on ###########.

## Credits

- [Rupert Brasil Lustosa](https://github.com/rupertlustosa)

## BUGS

- php artisan rlustosa:make-collection user userType --force --model User && chmod 777 -R ./ (Aponta para Modules\User\Resources\UserTypeResource e deveriaser Modules\User\Resources\UserResource visto que estamos passando o nome do Mode = User) 


## TODO

- Gerar views html
- Atualizar arquivo de rotas do Laravel (Atualmente está sendo sobrescrito)
- Gerar código de Validação para: StoreRequest, UpdateRequest e Rule

## COMANDOS ÚTEIS

- ***php artisan rlustosa:make-module product product --force && chmod 777 -R ./*** Gerar Módulo completo
- ***php artisan rlustosa:make-code product product -s --force && chmod 777 -R ./*** Gerar Scaffold de código
- ***php artisan rlustosa:make-code product product -c --force && chmod 777 -R ./ && npm run watch*** Gerar os ".Vue" funcionais

## About Rupert Brasil Lustosa

Rupert Lustosa is a freelance web developer specialising on the PHP and Laravel framework ############.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.