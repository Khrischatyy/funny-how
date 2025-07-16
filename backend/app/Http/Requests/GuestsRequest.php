<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'address_id' => 'required|integer|min:1',
        ];
    }
} 