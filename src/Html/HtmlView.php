<?php


namespace Rlustosa\LaravelGenerator\Html;


use Illuminate\Support\Collection;

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

        return '
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">' . $field->label . '</label>
                                            <input type="text" value="" class="form-control"
                                                    v-model="form.' . $field->id . '" placeholder="' . $field->label . '">
                                        </div>
                                    </div>
        ';
    }

    public function generateTableListTh(Collection $fields)
    {

        return '                                        <th>' . implode(' / ', $fields->pluck('label')->toArray()) . '</th>';
    }

    public function generateTableListTd(Collection $fields)
    {


        if ($fields->count() == 1) {

            $field = $fields->first();
            return '                                        <td>{{ item.' . $field->id . ' }}</td>';
        } else {

            $html = [];
            foreach ($fields as $field) {

                $html[] = '<strong>' . $field->label . ':</strong> {{ item.' . $field->id . ' }}';
            }
            return '                                        <td>' . implode('<br>', $html) . '</td>';
        }
        //
    }
}