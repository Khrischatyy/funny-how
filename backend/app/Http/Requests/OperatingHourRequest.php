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
            'open_time' => 'sometimes|date_format:H:i',
            'close_time' => 'sometimes|date_format:H:i',
            'is_closed' => 'sometimes|boolean',
            'day_of_week' => 'sometimes|integer|between:0,6',

            // Данные, когда необходимо проставить отдельно выходные дни, и отдельно будние
            'open_time_weekend' => [
                Rule::requiredIf(function () {
                    return (int) $this->input('mode_id') == 3;
                }),
                'date_format:H:i'
            ],
            'close_time_weekend' => [
                Rule::requiredIf(function () {
                    return (int) $this->input('mode_id') == 3;
                }),
                'date_format:H:i'
            ],
        ];
    }
}
