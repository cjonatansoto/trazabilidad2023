<?php

namespace App\Rules;

use App\Models\DispatchGuide;
use Illuminate\Contracts\Validation\Rule;

class CheckDispatchGuide implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
                                                                                                                                                                                                                      DispatchGuide::where('inactivo', $value)->where('saas', $value)->where('dds',$value)->get();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
