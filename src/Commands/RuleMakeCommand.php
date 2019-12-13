<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Symfony\Component\Console\Input\InputOption;

class RuleMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-rule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new rule class for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Rule';

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

        if ($this->option('resource')) {

            //$this->createModel();
            $this->createStoreUpdate();
        }
        $stub = '/stubs/rule.plain.stub';
        return __DIR__ . $stub;
    }

    /**
     * Create a model.
     *
     * @return void
     */
    protected function createModel()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-model', [
            'module' => $this->getModuleInput(),
            'name' => $modelName,
        ]);
    }

    /**
     * Create a StoreRequest and UpdateRequest.
     *
     * @return void
     */
    protected function createStoreUpdate()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-store-request', [
            'module' => $this->getModuleInput(),
            'name' => $modelName,
        ]);

        $this->call('rlustosa:make-update-request', [
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
            //['model', null, InputOption::VALUE_OPTIONAL, 'Indicates if the generated resource should be a resource "resource"'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Indicates if the generated rule should be a resource "rule"'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
