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

    public function generateFormHtml(Collection $fields)
    {

        $middleContent = '';

        $countFields = $fields->count();

        switch ($countFields) {

            case 1:
                $col = 12;
                break;
            case 2:
                $col = 6;
                break;
            case 3:
                $col = 4;
                break;
            case 4:
                $col = 3;
                break;
            default:
                $col = 6;
                break;

        }

        foreach ($fields as $field) {

            $middleContent .= '
                                <div class="form-group col-md-' . $col . '">
                                    <label>' . $field->label . '</label>
                                    <input type="text" v-model="form.' . $field->id . '" class="form-control">
                                    <form-error-component v-if="errors.' . $field->id . '" :errors="errors">
                                        {{ errors.' . $field->id . '[0] }}
                                    </form-error-component>
                                </div>            
            ';
        }

        return '
                            <div class="form-row">' . $middleContent . '
                            </div>        
        ';
    }

    public function generateHtmlSearch($field)
    {

        return '
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-form-label">' . $field->label . '</label>
                                            <input type="text" class="form-control" v-model="form.' . $field->id . '">
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