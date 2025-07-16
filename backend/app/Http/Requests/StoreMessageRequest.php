<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'recipient_id' => 'required|integer|exists:users,id',
            'address_id' => 'required|integer|exists:addresses,id',
            'content' => 'required|string|max:2000',
        ];
    }
} 