<?php

namespace Rlustosa\LaravelGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Rlustosa\LaravelGenerator\Commands\ControllerMakeCommand;
use Rlustosa\LaravelGenerator\Commands\ModelMakeCommand;
use Rlustosa\LaravelGenerator\Commands\PolicyMakeCommand;
use Rlustosa\LaravelGenerator\Commands\ServiceMakeCommand;

class ConsoleServiceProvider extends ServiceProvider
{

    /**
     * The available commands
     *
     * @var array
     */
    protected $commands = [
        ControllerMakeCommand::class,
        ModelMakeCommand::class,
        ServiceMakeCommand::class,
        PolicyMakeCommand::class,
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