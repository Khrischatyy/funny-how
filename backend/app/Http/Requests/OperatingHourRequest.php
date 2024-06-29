<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OperatingHourRequest extends FormRequest
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
            'mode_id' => 'required|integer',
            'address_id' => 'required|integer|exists:addresses,id',
            'hours' => [
                Rule::requiredIf(function () {
                    return (int) $this->input('mode_id') == 3;
                }),
                'array'
            ],
            'hours.*.day_of_week' => [
                Rule::requiredIf(function () {
                    return (int) $this->input('mode_id') == 3;
                }),
                'integer',
                'between:0,6'
            ],
            'hours.*.open_time' => [
                Rule::requiredIf(function () {
                    return (int) $this->input('mode_id') == 3;
                }),
                'date_format:H:i'
            ],
            'hours.*.close_time' => [
                Rule::requiredIf(function () {
                    return (int) $this->input('mode_id') == 3;
                }),
                'date_format:H:i'
            ],
            'hours.*.is_closed' => 'sometimes|boolean',
            'open_time' => [
                Rule::requiredIf(function () {
                    return (int) $this->input('mode_id') == 2;
                }),
                'date_format:H:i'
            ],
            'close_time' => [
                Rule::requiredIf(function () {
                    return (int) $this->input('mode_id') == 2;
                }),
                'date_format:H:i'
            ],
        ];
    }
}
