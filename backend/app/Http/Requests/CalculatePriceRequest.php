<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculatePriceRequest extends FormRequest
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
            'address_id' => 'required|integer|exists:address_prices,address_id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }

    public function messages()
    {
        return [
            'address_id.required' => 'Address ID is required',
            'address_id.integer' => 'Address ID must be an integer',
            'address_id.exists' => 'Address ID does not exist in the database',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Start time must be in the format HH:mm',
            'end_time.required' => 'End time is required',
            'end_time.date_format' => 'End time must be in the format HH:mm',
            'end_time.after' => 'End time must be after start time',
        ];
    }
}
