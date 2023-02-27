<?php

namespace App\Http\Requests\Neppex;

use Illuminate\Foundation\Http\FormRequest;


class CreateNeppexRequest extends FormRequest
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
            'codaut' => 'nullable|unique:neppex_controls,codaut',
            'transfer_code' => 'required|unique:neppex_controls,transfer_code',
        ];
    }

    public function attributes()
    {
        return [
            'codaut' => 'numero de neppex/codaut',
            'transfer_code' => 'codigo de traspaso',
        ];
    }


}
