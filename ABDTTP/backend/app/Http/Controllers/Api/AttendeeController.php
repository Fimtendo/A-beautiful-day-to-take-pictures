<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhotoDate;
use Illuminate\Http\Request;

class PhotoDateController extends Controller
{
    public function addAttendee(Request $request, PhotoDate $photoDate)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $attendees = is_array($photoDate->attendees) ? $photoDate->attendees : [];
        $alreadyJoined = collect($attendees)->contains(fn ($attendee) => isset($attendee['id']) && $attendee['id'] === $user->id);

        if ($alreadyJoined) {
            return $photoDate;
        }

        if ($photoDate->capacity && count($attendees) >= $photoDate->capacity) {
            return response()->json(['message' => 'PhotoDate is full'], 422);
        }

        $attendees[] = [
            'id' => $user->id,
            'username' => $user->username ?? $user->email,
        ];

        $photoDate->attendees = $attendees;
        $photoDate->save();

        return $photoDate;
    }

    public function removeAttendee(Request $request, PhotoDate $photoDate)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $attendees = is_array($photoDate->attendees) ? $photoDate->attendees : [];
        $attendees = array_values(array_filter($attendees, fn ($attendee) => isset($attendee['id']) && $attendee['id'] !== $user->id));

        $photoDate->attendees = $attendees;
        $photoDate->save();

        return $photoDate;
    }

    public function destroy(PhotoDate $photoDate)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Niet ingelogd.'], 401);
        }
        if ($photoDate->created_by !== auth()->id()) {
            return response()->json(['error' => 'Je bent niet de maker van deze PhotoDate.'], 403);
        }

        $photoDate->delete();

        return response()->json(['message' => 'Photo date deleted']);
    }
}