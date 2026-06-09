<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhotoPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Alleen ingelogde gebruikers mogen een fotopost plaatsen
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'marker_id'   => 'nullable|exists:markers,id',
            'marker_name' => 'nullable|string|max:255',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'caption'     => 'nullable|string|max:1000',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:8192',
        ];
    }
}
