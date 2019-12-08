<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ResourceMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new resource for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Resource';

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

        /*if (!class_exists($modelClass)) {

            $this->warn("A {$modelClass} model does not exist.", true);
            exit(1);
        } else {*/

        $resourceNamespace = $this->getDefaultNamespace();

        $replace = [];
        $replace['DummyResourceNamespace'] = $resourceNamespace;
        $replace['DummyResourceClass'] = $this->getResourceName();

        $replace = $this->buildModelReplacements($replace);

        $modelClass = $this->parseModel($model);
        $myModel = new $modelClass();
        $table = $myModel->getTable();
        $structure = rl_load_table_structure($table);
        $code = $this->geCodeToArray($myModel, $structure);

        $stub = $this->files->get($this->getStub());
        $replace['DummyResourceToArray'] = implode("\r\n            ", $code);

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
        //}

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
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        $stub = '/stubs/resource.stub';

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

        return base_path($this->rootNamespace() . '/' . $this->getModuleName() . '/Resources/' . $this->getResourceName() . '.php');
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