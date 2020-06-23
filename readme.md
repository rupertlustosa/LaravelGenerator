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
composer require rlustosa/laravel-generator:dev-master
```

``` bash
composer remove rlustosa/laravel-generator && composer require rlustosa/laravel-generator:dev-master -vvv
```

``` bash
composer require rlustosa/laravel-generator:~0.0.2
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

## 


## Preparing to Vue
1 - Install JS packages with:
```
- composer require laravel/ui --dev
- php artisan ui bootstrap
- php artisan ui vue
- npm install
- npm install @ckeditor/ckeditor5-build-classic @ckeditor/ckeditor5-vue coffee-script luxon v-money vue-awesome-notifications vue-datetime": "^ vue-filter-date-format vue-filter-date-parse vue-i18n vue-router vue-select vue-the-mask vuejs-datepicker vuejs-loading-plugin vuex weekstart --save-dev
```
2 - Modify resources/js/app.js to:
```
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import Vue from "vue";
import Routes from "./router";
import App from "./components/App";
import PaginateComponent from "./components/layout/PaginateComponent";
import VueLoading from 'vuejs-loading-plugin'
import VueFilterDateFormat from 'vue-filter-date-format';
import VueFilterDateParse from 'vue-filter-date-parse'
import VueAWN from "vue-awesome-notifications"
import i18n from './i18n';

// overwrite defaults
Vue.use(VueLoading, {
    dark: false, // default false
    text: 'Loading...', // default 'Loading'
    loading: false, // default false
    //customLoader: myVueComponent, // replaces the spinner and text with your own
    //background: 'rgb(47, 64, 80)', // set custom background
    classes: ['loading-screen-inspinia', 'animated', 'fadeIn'] // array, object or string
});

Vue.use(VueFilterDateFormat, {
    dayOfWeekNames: [
        'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'
    ],
    dayOfWeekNamesNamesShort: [
        'Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'
    ],
    monthNames: [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ],
    monthNamesShort: [
        'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
        'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
    ]
});

Vue.use(VueFilterDateParse);

Vue.component('paginate-component', PaginateComponent);

let optionsVueAWN = {
    position: "top-right",
    clean: true,
    labels: {
        tip: '',
        info: '',
        success: '',
        warning: '',
        alert: '',
        async: 'Loading',
        confirm: 'Confirmation required',
    },
    icons: {
        enabled: false,
    }
};

Vue.use(VueAWN, optionsVueAWN);

Vue.filter('capitalize', function (value) {
    if (!value) return '';
    value = value.toString();
    return value.charAt(0).toUpperCase() + value.slice(1) + '.....';
});

Vue.filter('currencydecimal', function (value) {
    if (!value) return '-';
    return value.toFixed(2)
});

Vue.filter('fromBoolean', function (value) {
    return value == 1 ? 'Yes' : 'No';
});

const app = new Vue({
    i18n,
    el: '#app',
    router: Routes,
    render: h => h(App),
    data() {
        return {
            componentKey: 0,
            viewKey: 1
        };
    },
    methods: {
        scrollToTop() {
            window.scrollTo({
                top: 0,
                left: 0,
                behavior: 'smooth'
            });
        },
        updateViewKey() {
            this.viewKey += 1;
        }
    }
});

$('#app').tooltip({
    selector: "[data-toggle=tooltip]",
    container: "body"
});

$('.popover-dismiss').popover({
    trigger: 'focus'
});


```

3 - Create resources/js/router.js with:
```
import Vue from "vue";
import VueRouter from "vue-router";
import DashboardComponent from "./components/dashboard/DashboardComponent";
import NotFoundComponent from "./components/NotFoundComponent";

Vue.use(VueRouter);

const router = new VueRouter({
    mode: "history", // hash history abstract
    routes: [
        {
            path: "/",
            name: "dashboard",
            component: DashboardComponent,
            meta: {
                auth: true
            }
        },
        {
            name: 'not-found',
            path: '/not-found',
            component: NotFoundComponent,
        },
        {
            path: '*',
            redirect: '/not-found',
        }
    ],
    scrollBehavior(to, from, savedPosition) {
        return {x: 0, y: 0};
    }
});

router.beforeEach((to, from, next) => {
    console.info(to.meta.auth ? 'Precisa estar logado' : 'Não Precisa estar logado');
    /*if (to.meta.auth) {
        return router.push({name: 'login'});
    }*/
    next();
});

export default router;

```
4 - App.vue example:
```
<template>
    <div id="wrapper">
        <router-view/>
    </div>
</template>

<script>
    export default {
        name: "App",
        mixins: {},
        components: {},
        data() {
            
        },
    }
</script>

<style scoped>

</style>

```

5 - Configure your web.php:
```
Route::get('/{any}', 'PanelController@index')->where('any', '^(?!api).*$');

Route::get('/', function(){
    return view('errors.500');
})->name('login');
```

6 - Configure your PanelController.php:
```
namespace App\Http\Controllers;


class PanelController extends Controller
{
    public function index() {
        return view('panel');
    }
}
```

7 - Configure your initial view:
```
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel</title>
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
</head>

<body>

<div>
    <v-app id="app">

    </v-app>

</div>

<script src="{{ mix('/js/app.js') }}"></script>

</body>
</html>
```

8 - See commands:
```
php artisan rlustosa
```

9 - After generating a module, add the service provider in the providers section of config/app.php

## USEFUL COMMANDS

- ***php artisan rlustosa:make-module product product --force && chmod 777 -R ./*** Gerar Módulo completo
- ***php artisan rlustosa:make-code product product -s --force && chmod 777 -R ./*** Gerar Scaffold de código
- ***php artisan rlustosa:make-code product product -c --force && chmod 777 -R ./ && npm run watch*** Gerar os ".Vue" funcionais


## Documentation

You'll find installation instructions and full documentation on ###########.

## Credits

- [Rupert Brasil Lustosa](https://github.com/rupertlustosa)

## TODO

- Atualizar arquivo de rotas do Laravel (Atualmente está sendo sobrescrito)
- Gerar views html

## About Rupert Brasil Lustosa

Rupert Lustosa is a freelance web developer specialising on the PHP and Laravel framework ############.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.