<?php

namespace App\Http\Requests\Lot;

use Illuminate\Foundation\Http\FormRequest;


class CreateLotRequest extends FormRequest
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
            'dispatch_guide_id' => 'required',
            'quantity_type_id'  => 'required',
            'items'             => 'required',
            'lot_id'            => 'required',
        ];
    }

    public function messages()
    {
        return [
            'dispatch_guide_id.required'            => 'El campo es requerido',
            'quantity_type_id.required'             => 'El campo es requerido',
            'items.required'                        => 'El campo es requerido',
        ];
    }
}
