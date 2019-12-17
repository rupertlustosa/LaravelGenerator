<?php


namespace Rlustosa\LaravelGenerator\Html;


class HtmlView
{

    public function generateFillable()
    {

    }

    public function generateRules()
    {

    }

    public function generateFormHtml()
    {

    }

    public function generateHtmlSearch($field)
    {

        //dd($field);
        return '
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">' . $field['label'] . '</label>
                                            <input type="text" value="" class="form-control"
                                                    v-model="form.' . $field['field'] . '" placeholder="' . $field['label'] . '">
                                        </div>
                                    </div>
        ';
    }

    public function generateListHtml($mapping, $column)
    {
        $th = $mapping->label;
        $td = '{{ item.' . $mapping->id . ' }}';

        return [
            'label' => $th,
            'field' => $mapping->id,
        ];
    }
}