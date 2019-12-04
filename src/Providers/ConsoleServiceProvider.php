<?php

namespace Rlustosa\LaravelGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Rlustosa\LaravelGenerator\Commands\CodingMakeCommand;
use Rlustosa\LaravelGenerator\Commands\CollectionMakeCommand;
use Rlustosa\LaravelGenerator\Commands\ControllerMakeCommand;
use Rlustosa\LaravelGenerator\Commands\ModelMakeCommand;
use Rlustosa\LaravelGenerator\Commands\ModuleMakeCommand;
use Rlustosa\LaravelGenerator\Commands\PolicyMakeCommand;
use Rlustosa\LaravelGenerator\Commands\ResourceMakeCommand;
use Rlustosa\LaravelGenerator\Commands\RouteApiMakeCommand;
use Rlustosa\LaravelGenerator\Commands\RouteServiceProviderMakeCommand;
use Rlustosa\LaravelGenerator\Commands\RuleMakeCommand;
use Rlustosa\LaravelGenerator\Commands\ServiceMakeCommand;
use Rlustosa\LaravelGenerator\Commands\ServiceProviderMakeCommand;
use Rlustosa\LaravelGenerator\Commands\StoreRequestMakeCommand;
use Rlustosa\LaravelGenerator\Commands\UpdateRequestMakeCommand;
use Rlustosa\LaravelGenerator\Commands\ValidatorsMakeCommand;

class ConsoleServiceProvider extends ServiceProvider
{

    /**
     * The available commands
     *
     * @var array
     */
    protected $commands = [
        ModuleMakeCommand::class,
        ControllerMakeCommand::class,
        ModelMakeCommand::class,
        ServiceMakeCommand::class,
        PolicyMakeCommand::class,
        ValidatorsMakeCommand::class,
        RuleMakeCommand::class,
        StoreRequestMakeCommand::class,
        UpdateRequestMakeCommand::class,
        ServiceProviderMakeCommand::class,
        RouteServiceProviderMakeCommand::class,
        RouteApiMakeCommand::class,
        CodingMakeCommand::class,
        ResourceMakeCommand::class,
        CollectionMakeCommand::class,
    ];

    /**
     * Register the commands.
     */
    public function register()
    {

        //dd(__METHOD__);
        //$this->commands($this->commands);
    }

    /**
     * @return array
     */
    public function provides()
    {
        $provides = $this->commands;

        return $provides;
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //dd(__METHOD__);
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }
}