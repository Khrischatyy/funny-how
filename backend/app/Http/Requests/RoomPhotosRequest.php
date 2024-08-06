<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomPhotosRequest extends FormRequest
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
            'room_id' => 'required|exists:rooms,id',
            'photos' => 'required|array',
            'photos.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg,heic,heif|max:5120',
        ];
    }
}
