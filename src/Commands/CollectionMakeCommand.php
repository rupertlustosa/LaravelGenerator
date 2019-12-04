<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CollectionMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-collection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new collection for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Collection';

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

        $model = $this->option('model');

        $modelClass = $this->parseModel($model);

        if (!class_exists($modelClass)) {

            $this->warn("A {$modelClass} model does not exist.", true);
            exit(1);
        } else {

            $resourceNamespace = $this->getDefaultNamespace();

            $replace = [];
            $replace['DummyResourceNamespace'] = $resourceNamespace;
            $replace['DummyResourceClass'] = $this->getResourceName();
            $replace['DummyCollectionClass'] = $this->getCollectionName();
            $stub = $this->files->get($this->getStub());

            return str_replace(
                array_keys($replace), array_values($replace), $stub
            );
        }

    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {

        return $this->getDefaultResourceNamespace();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        $stub = '/stubs/collection.stub';

        return __DIR__ . $stub;
    }

    protected function geCodeToArray($model, $structure)
    {

        $columns = [];
        foreach (array_keys($structure['columns']) as $column) {

            if (!in_array($column,
                [
                    $model::CREATED_AT,
                    $model::UPDATED_AT,
                    $model->getDeletedAtColumn(),
                ]
            )) {

                $columns[] = "'{$column}' => \$this->{$column},";
            }
        }

        return $columns;
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

        return base_path($this->rootNamespace() . '/' . $this->getModuleName() . '/Resources/' . $this->getCollectionName() . '.php');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Generate a resource service for the given model.'],
        ];
    }

    protected function createdSuccessfully()
    {
        $this->info($this->type . ' created successfully.');
    }
}