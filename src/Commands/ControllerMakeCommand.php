<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ControllerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new restful controller for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

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

        $controllerNamespace = $this->getDefaultNamespace();

        $replace = [];
        $replace['DummyControllerNamespace'] = $controllerNamespace;
        $replace['RootController'] = 'Rlustosa\LaravelGenerator\BaseModule\BaseModuleController';
        $replace['DummyControllerClass'] = $this->getControllerName();

        if ($this->option('model')) {

            $replace = $this->buildModelReplacements($replace);
            $replace = $this->buildServiceReplacements($replace);

            $model = $this->option('model');

            $policyNamespace = $this->getDefaultPolicyNamespace();
            $policyClass = $policyNamespace . '\\' . $this->getPolicyName();

            if (!class_exists($policyClass)) {
                if ($this->confirm("A {$policyClass} policy does not exist. Do you want to generate it?", true)) {
                    $this->call('rlustosa:make-policy', ['module' => $this->getModuleInput(), 'name' => $model, '--model' => $model]);
                }
            }

            $this->call('rlustosa:make-validators', ['module' => $this->getModuleInput(), '--model' => $model]);
        }

        $replace["use {$controllerNamespace}\Controller;\n"] = '';

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

        return $this->getDefaultControllerNamespace();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        if ($this->option('model')) {

            $stub = '/stubs/controller-resource.stub';
        } else {

            $stub = '/stubs/controller-generic.stub';
        }

        return __DIR__ . $stub;
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

        return base_path($this->rootNamespace() . '/' . $this->getModuleName() . '/Http/Controllers/' . $this->getControllerName() . '.php');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
        ];
    }

    protected function createdSuccessfully()
    {
        $this->info($this->type . ' created successfully.');
    }
}