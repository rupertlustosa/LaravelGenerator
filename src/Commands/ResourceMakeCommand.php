<?php

namespace Rlustosa\LaravelGenerator\Commands;

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
    protected $description = 'Create a new resource class for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Resource';

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
            } else {
                $this->warn('COMO A CLASSE EXISTE, VERIFICAR SE A TABELA EXISTE PARA CRIAR O CÓDIGO CENTRAL');
            }
            $stub = '/stubs/resource.model.stub';
        } else {

            $stub = '/stubs/resource.plain.stub';
        }

        return __DIR__ . $stub;
    }

    /**
     * Create a controller for the model.
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
