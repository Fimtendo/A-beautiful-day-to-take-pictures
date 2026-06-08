<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoDateRequest extends FormRequest
{

    public function authorize(): bool
    {
        $photoDate = $this->route('photoDate');

        return $photoDate && $this->user()->id === $photoDate->created_by;
    }

    public function rules(): array
    {
        return [
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_time'  => 'sometimes|date',
            'end_time'    => 'sometimes|date|after:start_time',
            'capacity'    => 'nullable|integer|min:1',
            'marker_id'   => 'nullable|exists:markers,id',
            'marker_name' => 'nullable|string',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'attendees'   => 'nullable|array',
        ];
    }
}
