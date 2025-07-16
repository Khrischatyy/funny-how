<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageHistoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'recipient_id' => 'required|integer|min:1',
            'address_id' => 'required|integer|min:1',
        ];
    }
} 