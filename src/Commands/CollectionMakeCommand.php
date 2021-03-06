<?php

namespace Rlustosa\LaravelGenerator\Commands;

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
    protected $description = 'Create a new collection class for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Collection';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        if (!empty($this->option('model'))) {

            $classExists = $this->classExists('Model', $this->option('model'));

            if (!$classExists) {

                $this->createModel();
            }

            $classExists = $this->classExists('Resource', $this->option('model'));

            if (!$classExists) {

                $this->createResource();
            }

            $stub = '/stubs/collection.model.stub';
        } else {

            $stub = '/stubs/collection.plain.stub';
        }

        return __DIR__ . $stub;
    }

    /**
     * Create a model.
     *
     * @return void
     */
    protected function createModel()
    {

        $modelName = $this->qualifyClass($this->option('model'));

        $this->call('rlustosa:make-model', [
            'module' => $this->getModuleInput(),
            'name' => $modelName,
        ]);
    }

    /**
     * Create a resource for the collection.
     *
     * @return void
     */
    protected function createResource()
    {

        $modelName = $this->qualifyClass($this->option('model'));

        $this->call('rlustosa:make-resource', [
            'module' => $this->getModuleInput(),
            'name' => $modelName,
        ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'Indicates if the generated resource should be a resource "resource"'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
