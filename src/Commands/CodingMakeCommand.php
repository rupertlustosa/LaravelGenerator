<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Support\Str;
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
            //$this->info(implode(', ', array_values($fillable)), true);
            echo "    protected \$fillable = [\r\n";
            echo "        '" . implode("', '", array_values($fillable)) . "'\r\n";
            echo "    ];\r\n";
            echo "\r\n";
            $this->info('', true);

            $rules = $this->generateRules($myModel, $structure);
            $this->info('### RULES ###', true);
            //print_r($rules);
            echo "    protected static \$rules = [\r\n";
            foreach ($rules as $key => $rule) {

                echo "        '{$key}' => '{$rule}',\r\n";
            }
            echo '    ];';
            echo "\r\n";
            echo "\r\n";
            echo "        return [\r\n";
            foreach ($rules as $key => $rule) {

                echo "            '{$key}' => self::\$rules['{$key}'],\r\n";
            }
            echo '        ];';
            //$this->info(implode(', ', array_values($fillable)), true);
            $this->info('', true);

            $formHtml = $this->generateFormHtml($myModel, $structure);
            $this->info('### FORM HTML ###', true);

            foreach ($formHtml as $html) {

                echo "                                <div class=\"form-group col-6\">\r\n";
                echo $html['label'] . "\r\n";
                echo $html['input'] . "\r\n";
                echo "                                </div>\r\n\r\n";
            }


        }

        dd('FIM');
    }

    protected function missingDependencies()
    {

        $missing = [];

        return $missing;
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
        //dd($structure);
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

                $nullableOrRequired = $column['notnull'] ? 'required' : 'nullable';

                if ($column['name'] == $model->getKeyName()) {

                    $columns[$column['name']] = 'required|integer|exists:' . $model->getTable() . ',id,deleted_at,NULL';
                } elseif (in_array($column['name'], array_keys($structure['foreignKeys']))) {

                    $referenceTable = $structure['foreignKeys'][$column['name']];

                    $columns[$column['name']] = $nullableOrRequired . '|integer|exists:' . $referenceTable . ',id,deleted_at,NULL';
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'password')) {

                    $columns[$column['name']] = $nullableOrRequired . '|string|min:8|confirmed';
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'email')) {

                    $columns[$column['name']] = $nullableOrRequired . '|email|max:' . $column['length'];
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'image')) {

                    $columns[$column['name']] = $nullableOrRequired . '|image|mimes:jpeg,png,jpg,gif,svg|max:4096';
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'phone')) {

                    $columns[$column['name']] = $nullableOrRequired . '|max:' . $column['length'] . '|regex:/^\(?\d{2}\)?[\s-]?\d{4,5}-?\d{4}$/i';
                } elseif ($column['type'] == 'string') {

                    $columns[$column['name']] = $nullableOrRequired . '|max:' . $column['length'];
                } elseif ($column['type'] == 'boolean') {

                    $columns[$column['name']] = $nullableOrRequired . '|boolean';
                } elseif (in_array($column['type'], ['bigint', 'integer', 'smallint'])) {

                    $columns[$column['name']] = $nullableOrRequired . '|numeric';
                } elseif ($column['type'] == 'datetime') {

                    $columns[$column['name']] = $nullableOrRequired . '|date_format:d/m/Y H:i';
                } elseif ($column['type'] == 'date') {

                    $columns[$column['name']] = $nullableOrRequired . '|date_format:d/m/Y';
                } elseif ($column['type'] == 'time') {

                    $columns[$column['name']] = $nullableOrRequired . '|date_format:H:i';
                } elseif ($column['type'] == 'text') {

                    $columns[$column['name']] = $nullableOrRequired;
                } else {

                    $columns[$column['name']] = $nullableOrRequired . '|' . $column['name'];
                }
            }
        }

        return $columns;
    }

    private function contains($string, $needles)
    {

        return Str::contains(strtolower($string), $needles);
    }

    private function generateFormHtml($model, $structure)
    {
        $code = [];
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

                if ($column['name'] == $model->getKeyName()) {


                } elseif (in_array($column['name'], array_keys($structure['foreignKeys']))) {

                    $referenceTable = $structure['foreignKeys'][$column['name']];

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'text', 'select2'),
                    ];
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'password')) {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'password'),
                    ];
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'email')) {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'email'),
                    ];
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'image')) {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'file'),
                    ];
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'phone')) {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'text', 'mask_phone'),
                    ];
                } elseif ($column['type'] == 'boolean') {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'number'),
                    ];
                } elseif (in_array($column['type'], ['bigint', 'integer', 'smallint'])) {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'number'),
                    ];
                } elseif ($column['type'] == 'datetime') {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'text', 'mask_datetime'),
                    ];
                } elseif ($column['type'] == 'date') {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'text', 'mask_date'),
                    ];
                } elseif ($column['type'] == 'time') {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'text', 'mask_time'),
                    ];
                } elseif ($column['type'] == 'text') {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column, 'text', 'mask_phone'),
                    ];
                } else {

                    $code[$column['name']] = [
                        'label' => $this->returnLabel($column),
                        'input' => $this->returnDefaultInput($column),
                    ];
                }

            }
        }

        return $code;
    }

    private function returnLabel($column)
    {

        return '                                    <label for="' . $column['name'] . '">' . Str::upper($column['name']) . '</label>';
    }

    private function returnDefaultInput($column, $type = 'text', $extraClass = null)
    {

        $class = 'form-control';
        if ($extraClass) {

            $class .= ' ' . $extraClass;
        }
        return '                                    <input type="text" v-model="form.' . $column['name'] . '" class="' . $class . '">';
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