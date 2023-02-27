<?php

namespace App\Http\Requests\DeclaredJurisdiction;

use Illuminate\Foundation\Http\FormRequest;


class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'dispatch_guide' => 'required',
            'date_guide' => 'required',
            'client' => 'required',
            'destination' => 'required',
            'conservation' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'dispatch_guide' => 'guía despacho',
            'date_guide' => 'fecha guía',
            'client' => 'cliente',
            'destination' => 'destino',
            'conservation' => 'conservación',
        ];
    }


}
