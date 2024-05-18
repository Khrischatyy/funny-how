<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressPriceDeleteRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'address_id' => 'required|int|exists:addresses,id',
            'address_prices_id' => 'required|int|exists:address_prices,id',
        ];
    }
}
