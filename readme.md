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

## TODO

- Gerar views html
- Gerar views com os componentes .Vue
- Gerar as rules no arquivo de Rule
- Resolver problema de autoload no comando "rlustosa:make-controller-rest"
- Ao gerar um novo controlador para um módulo, inserir a nova rota  
- implementar missingDependencies() para ele se adequar aos parâmetros opcionais

## About Rupert Brasil Lustosa

Rupert Lustosa is a freelance web developer specialising on the PHP and Laravel framework ############.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.