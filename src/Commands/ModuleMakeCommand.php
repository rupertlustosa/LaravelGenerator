<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate resource module for the specified module.';


    public function handle()
    {

        $model = $this->option('model');

        $this->info('--> make-model');
        $this->call('rlustosa:make-model', ['module' => $this->getModuleInput(), 'name' => $this->getNameInput()]);

        $this->info('--> make-service');
        $this->call('rlustosa:make-service', ['module' => $this->getModuleInput(), 'name' => $this->getNameInput(), '--model' => $model]);

        $this->info('--> make-policy');
        $this->call('rlustosa:make-policy', ['module' => $this->getModuleInput(), 'name' => $this->getNameInput(), '--model' => $model]);

        /*$this->info('--> make-validators');
        $this->call('rlustosa:make-validators', ['module' => $this->getModuleInput(), '--model' => $model]);*/

        $this->info('--> make-rule');
        $this->call('rlustosa:make-rule', ['module' => $this->getModuleInput(), '--model' => $model]);

        $this->info('--> make-store-request');
        $this->call('rlustosa:make-store-request', ['module' => $this->getModuleInput(), '--model' => $model]);

        $this->info('--> make-update-request');
        $this->call('rlustosa:make-update-request', ['module' => $this->getModuleInput(), '--model' => $model]);

        $this->info('--> make-controller');
        $this->call('rlustosa:make-controller', ['module' => $this->getModuleInput(), 'name' => $this->getNameInput(), '--model' => $model]);

        $this->info('--> make-service-provider');
        $this->call('rlustosa:make-service-provider', ['module' => $this->getModuleInput(), 'name' => $this->getNameInput()]);

        $this->info('--> make-route-service-provider');
        $this->call('rlustosa:make-route-service-provider', ['module' => $this->getModuleInput(), 'name' => $this->getNameInput()]);

        $this->info('--> make-route-api');
        $this->call('rlustosa:make-route-api', ['module' => $this->getModuleInput(), 'name' => $this->getNameInput()]);

        $this->info('--- FIM ---');

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
            ['name', InputArgument::REQUIRED, 'The name of the controller class.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Generate a resource policy for the given model.'],
        ];
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }

    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath()
    {
        // TODO: Implement getDestinationFilePath() method.
    }

    protected function alreadyExists()
    {
        // TODO: Implement alreadyExists() method.
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        // TODO: Implement getDefaultNamespace() method.
    }
}