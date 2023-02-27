<?php

namespace App\Http\Requests\DeclaredJurisdiction;

use Illuminate\Foundation\Http\FormRequest;


class EditRequest extends FormRequest
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
            'weight' => 'required',
            'bin_box' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'weight' => 'peso declarado',
            'bin_box' => 'cajas/bines declarados',
        ];
    }


}
