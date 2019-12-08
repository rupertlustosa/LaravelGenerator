<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class ServiceProviderMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-service-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a service provider for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'ServiceProvider';

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {

        return $this->getDefaultProvidersNamespace();
    }

    protected function missingDependencies()
    {

        $missing = [];

        return $missing;
    }

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

        $providerNamespace = $this->getDefaultProvidersNamespace();

        $replace = [];
        $replace['DummyProviderNamespace'] = $providerNamespace;
        $replace['DummyServiceProviderClass'] = $this->getServiceProviderName();
        $replace['DummyModuleLowerCase'] = strtolower(Str::snake($this->getModuleName()));
        $replace['DummyModule'] = $this->getModuleName();

        $stub = $this->files->get($this->getStub());

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        $stub = '/stubs/service-provider.stub';

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

        return base_path($this->rootNamespace() . '/' . $this->getModuleName() . '/Providers/' . $this->getServiceProviderName() . '.php');
    }
}