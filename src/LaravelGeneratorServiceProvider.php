<?php

namespace Rlustosa\LaravelGenerator;

use Config;
use Illuminate\Support\ServiceProvider;
use Rlustosa\LaravelGenerator\Providers\ConsoleServiceProvider;
use Rlustosa\LaravelGenerator\Providers\RouteServiceProvider;

class LaravelGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {

        //dd('Aaaaaaaaaa');
        /*$this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path('Tip', 'Database/Migrations'));*/
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views', 'lustosa-generator'
        );

        $this->registerPublishing();

    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            /*$this->publishes([
                __DIR__.'/Storage/migrations' => database_path('migrations'),
            ], 'telescope-migrations');*/

            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/rlustosa'),
            ], 'rlustosa-assets');

            /*$this->publishes([
                __DIR__.'/../config/telescope.php' => config_path('telescope.php'),
            ], 'telescope-config');

            $this->publishes([
                __DIR__.'/../stubs/TelescopeServiceProvider.stub' => app_path('Providers/TelescopeServiceProvider.php'),
            ], 'telescope-provider');*/
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->registerProviders();
    }

    /**
     * Register providers.
     */
    protected function registerProviders()
    {

        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ConsoleServiceProvider::class);
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/tip');

        $sourcePath = module_path('Tip', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/tip';
        }, Config::get('view.paths')), [$sourcePath]), 'tip');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/tip');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'tip');
        } else {
            $this->loadTranslationsFrom(module_path('Tip', 'Resources/lang'), 'tip');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (!app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('Tip', 'Database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('Tip', 'Config/config.php') => config_path('tip.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('Tip', 'Config/config.php'), 'tip'
        );
    }
}
