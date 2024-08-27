<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomPriceDeleteRequest extends FormRequest
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
            'room_id' => 'required|int|exists:rooms,id',
            'room_price_id' => 'required|int|exists:room_prices,id',
        ];
    }
}
