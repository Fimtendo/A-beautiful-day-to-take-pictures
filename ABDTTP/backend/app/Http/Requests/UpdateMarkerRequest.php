<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMarkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $marker = $this->route('marker');
        
        return $marker && $this->user()->id === $marker->created_by;
    }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'sometimes|integer',
            'popup'       => 'nullable|string',
            'lat'         => 'sometimes|numeric|between:-90,90',
            'lng'         => 'sometimes|numeric|between:-180,180',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        ];
    }
}
