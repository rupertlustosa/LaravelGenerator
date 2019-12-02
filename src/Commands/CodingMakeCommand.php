<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Symfony\Component\Console\Input\InputArgument;

class CodingMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-coding';

    protected $excludeDates = ['created_at', 'updated_at', ''];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate code for the specified model.';


    public function handle()
    {

        $model = $this->argument('model');

        $modelClass = $this->parseModel($model);

        if (!class_exists($modelClass)) {

            $this->warn("A {$modelClass} model does not exist.", true);
        } else {

            $this->info('', true);

            $myModel = new $modelClass();
            $table = $myModel->getTable();
            $structure = rl_load_table_structure($table);

            $fillable = $this->generateFillable($myModel, $structure);
            $this->info('### FILLABLE ###', true);
            $this->info(implode(', ', array_values($fillable)), true);
            $this->info('', true);

            $rules = $this->generateRules($myModel, $structure);
            $this->info('### RULES ###', true);
            $this->info(implode(', ', array_values($fillable)), true);
            $this->info('', true);

        }

        dd('FIM');
    }

    private function generateFillable($model, $structure)
    {

        //dd($model->getKeyName(), $model->getDates(), $model::CREATED_AT, $model::UPDATED_AT, $model->getDeletedAtColumn());
        $columns = [];
        foreach (array_keys($structure['columns']) as $column) {

            if (!in_array($column,
                [
                    $model->getKeyName(),
                    $model::CREATED_AT,
                    $model::UPDATED_AT,
                    $model->getDeletedAtColumn(),
                    'password',
                    'remember_token',
                    'email_verified_at',
                ]
            )) {

                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function generateRules($model, $structure)
    {
        dd($structure);
        $columns = [];
        foreach ($structure['columns'] as $column) {

            if (!in_array($column['name'],
                [
                    //$model->getKeyName(),
                    $model::CREATED_AT,
                    $model::UPDATED_AT,
                    $model->getDeletedAtColumn(),
                    //'password',
                    'remember_token',
                    'email_verified_at',
                ]
            )) {

                //notnull type boolean string
                //email
                //nullable|exists:sources,id,deleted_at,NULL
                //required|exists:cities,id
                //required|min:2|max:255
                //required|image|mimes:jpeg,png,jpg,gif,svg|max:2048
                //nullable|max:15|regex:/^\(?\d{2}\)?[\s-]?\d{4,5}-?\d{4}$/i
                //'id' => $this->rules['id']

                $nullableOrRequired = $column['notnull'] ? 'required' : 'nullable';

                if ($column['name'] == $model->getKeyName()) {

                    $columns[] = 'required|integer|exists:' . $model->getTable() . ',id,deleted_at,NULL';
                } elseif (in_array($column['name'], array_keys($structure['foreignKeys']))) {

                    $referenceTable = $structure['foreignKeys'][$column['name']];

                    $columns[] = $nullableOrRequired . '|integer|exists:' . $referenceTable . ',id,deleted_at,NULL';
                } elseif ($column['type'] == 'string') {

                    $columns[] = $nullableOrRequired . '|max:' . $column['length'];
                } else {

                    $columns[] = $nullableOrRequired . '|' . $column['name'];
                }

                // Tratar boolean datetime ou date email image ou phone
            }
        }
        dd($columns);
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
            ['model', InputArgument::REQUIRED, 'The name of model will be used.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            //['model', 'm', InputOption::VALUE_REQUIRED, 'Generate a resource controller for the given model.'],
        ];
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }

    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath()
    {
        // TODO: Implement getDestinationFilePath() method.
    }

    protected function alreadyExists()
    {
        // TODO: Implement alreadyExists() method.
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        // TODO: Implement getDefaultNamespace() method.
    }
}