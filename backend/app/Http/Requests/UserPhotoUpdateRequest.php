<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserPhotoUpdateRequest extends FormRequest
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
            'photo' => 'required|file|mimes:jpeg,png,jpg,gif,heic,heif|max:5120',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->hasFile('photo') && in_array($this->file('photo')->getClientOriginalExtension(), ['heic', 'heif'])) {
            $heifPath = $this->file('photo')->getPathname();
            $jpegPath = $heifPath . '.jpg';

            $command = "heif-convert $heifPath $jpegPath";
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw ValidationException::withMessages(['photo' => 'Failed to convert HEIC/HEIF to JPEG.']);
            }

            $this->replace(['photo' => new \Illuminate\Http\UploadedFile($jpegPath, $this->file('photo')->getClientOriginalName() . '.jpg')]);
        }
    }
}
