<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Symfony\Component\Console\Input\InputOption;

class UpdateRequestMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-update-request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new UpdateRequest class for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'UpdateRequest';

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

            $this->createRule();
            $stub = '/stubs/update-request.model.stub';
        } else {

            $stub = '/stubs/update-request.plain.stub';
        }
        return __DIR__ . $stub;
    }

    /**
     * Create a model.
     *
     * @return void
     */
    protected function createRule()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-rule', [
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
            ['resource', 'r', InputOption::VALUE_NONE, 'Indicates if the generated rule should be a resource "rule"'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
