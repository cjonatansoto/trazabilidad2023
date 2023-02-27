<?php

namespace App\Http\Requests\Neppex;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class EditNeppexRequest extends FormRequest
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
            'codaut' => ['nullable',
                Rule::unique('neppex_controls')->ignore($this->request->get('id'))],
            'transfer_code' => [
                'required',
                Rule::unique('neppex_controls')->ignore($this->request->get('id'))
            ],
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
