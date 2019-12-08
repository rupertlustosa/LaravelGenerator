<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ServiceMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new service for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Service';

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass()
    {

        $serviceNamespace = $this->getDefaultNamespace();

        $replace = [];
        $replace['DummyServiceNamespace'] = $serviceNamespace;
        $replace['DummyServiceClass'] = $this->getServiceName();

        if ($this->option('model')) {

            $replace = $this->buildModelReplacements($replace);
        }

        $stub = $this->files->get($this->getStub());

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {

        return $this->getDefaultServiceNamespace();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        if ($this->option('model')) {

            $stub = '/stubs/service-resource.stub';
        } else {

            $stub = '/stubs/service-generic.stub';
        }

        return __DIR__ . $stub;
    }

    protected function missingDependencies()
    {

        $missing = [];

        $model = $this->option('model');
        $modelClass = $this->parseModel($model);

        if (!class_exists($modelClass)) {

            $missing[] = 'php artisan rlustosa:make-model ' . $this->getModuleInput() . ' ' . $this->getNameInput();
            $this->warn("A {$modelClass} model does not exist.", true);
        }

        return $missing;
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
            ['name', InputArgument::REQUIRED, 'The name of the service class.'],
        ];
    }

    protected function alreadyExists()
    {

        return $this->files->exists($this->getDestinationFilePath());
    }

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getDestinationFilePath()
    {

        return base_path($this->rootNamespace() . '/' . $this->getModuleName() . '/Services/' . $this->getServiceName() . '.php');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource service for the given model.'],
        ];
    }

    protected function createdSuccessfully()
    {
        $this->info($this->type . ' created successfully.');
    }
}