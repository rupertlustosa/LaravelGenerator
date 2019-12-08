<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ControllerRestMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-controller-rest';

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
        $defaultApiControllerClass = $this->rootNamespace() . '\Http\ApiController';

        $replace = [];
        $replace['DummyControllerNamespace'] = $controllerNamespace;
        $replace['DummyFullDefaultControllerClass'] = $defaultApiControllerClass;
        $replace['DummyControllerClass'] = $this->getControllerName();
        $replace['DummyRootNamespaceHttp'] = app()->getNamespace() . 'Http';

        if (!class_exists($defaultApiControllerClass)) {

            $fullPath = base_path($this->rootNamespace() . '/Http/Controllers/ApiController.php');

            $this->makeDirectory($fullPath);
            $stubDefaultController = str_replace(
                array_keys($replace), array_values($replace), $this->files->get(__DIR__ . '/stubs/api-controller.stub')
            );

            $this->files->put($fullPath, $this->sortImports($stubDefaultController));
        }

        $replace = $this->buildModelReplacements($replace);
        $replace = $this->buildServiceReplacements($replace);
        $replace = $this->buildPolicyReplacements($replace);
        $replace = $this->buildValidatorRuleReplacements($replace);
        $replace = $this->buildValidatorStoreRequestReplacements($replace);
        $replace = $this->buildValidatorUpdateRequestReplacements($replace);
        $replace = $this->buildResourceReplacements($replace);
        $replace = $this->buildCollectionReplacements($replace);

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

        $stub = '/stubs/controller-rest.stub';

        return __DIR__ . $stub;
    }

    protected function missingDependencies()
    {

        $missing = [];

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
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Generate a resource controller for the given model.'],
        ];
    }

    protected function createdSuccessfully()
    {
        $this->info($this->type . ' created successfully.');
    }
}