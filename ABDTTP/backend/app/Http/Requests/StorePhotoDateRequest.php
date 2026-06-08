<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhotoDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time'  => 'required|date|after_or_equal:now',
            'end_time'    => 'required|date|after:start_time',
            'capacity'    => 'nullable|integer|min:1',
            'marker_id'   => 'nullable|exists:markers,id',
            'marker_name' => 'nullable|string',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'attendees'   => 'nullable|array',
        ];
    }
}
