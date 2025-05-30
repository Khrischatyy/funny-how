<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomPricesRequest extends FormRequest
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
            'hours' => 'required|integer|in:1,4,8,12,24',
            'total_price' => 'required|numeric|min:0',
            'is_enabled' => 'required|boolean',
            'id' => 'sometimes|integer'
        ];
    }
}
