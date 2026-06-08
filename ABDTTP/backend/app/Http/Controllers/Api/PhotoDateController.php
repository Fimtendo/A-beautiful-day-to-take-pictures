<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhotoDate;
use App\Http\Requests\StorePhotoDateRequest;
use App\Http\Requests\UpdatePhotoDateRequest;

class PhotoDateController extends Controller
{
    public function index()
    {
        return PhotoDate::orderBy('start_time', 'asc')->get();
    }

    public function store(StorePhotoDateRequest $request)
    {
        $data = $request->validated();

        $data['created_by'] = $request->user()->id;
        $data['created_by_username'] = $request->user()->username ?? $request->user()->email;

        $photoDate = PhotoDate::create($data);

        return response()->json($photoDate, 201);
    }

    public function show(PhotoDate $photoDate)
    {
        return $photoDate;
    }

    public function update(UpdatePhotoDateRequest $request, PhotoDate $photoDate)
    {
        $data = $request->validated();

        $photoDate->update($data);

        return response()->json($photoDate);
    }
}
