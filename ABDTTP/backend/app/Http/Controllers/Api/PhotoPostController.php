<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhotoPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhotoPostController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user() ?: $request->user();
        $userId = $user ? $user->id : null;

        $posts = PhotoPost::withCount(['likes', 'bookmarks'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $posts->map(function (PhotoPost $post) use ($userId) {
            $post->liked_by_user = $userId ? $post->likes()->where('user_id', $userId)->exists() : false;
            $post->bookmarked_by_user = $userId ? $post->bookmarks()->where('user_id', $userId)->exists() : false;
            return $post;
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'marker_id' => 'nullable|exists:markers,id',
            'marker_name' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'image_url' => 'required|string',
            'caption' => 'nullable|string',
        ]);

        $data['created_by'] = $request->user()->id;
        $data['created_by_username'] = $request->user()->username ?? $request->user()->email;

        $post = PhotoPost::create($data);

        return response()->json($post, 201);
    }

    public function show(Request $request, PhotoPost $photoPost)
    {
        $user = Auth::guard('sanctum')->user() ?: $request->user();
        $userId = $user ? $user->id : null;

        $photoPost->loadCount(['likes', 'bookmarks']);
        $photoPost->liked_by_user = $userId ? $photoPost->likes()->where('user_id', $userId)->exists() : false;
        $photoPost->bookmarked_by_user = $userId ? $photoPost->bookmarks()->where('user_id', $userId)->exists() : false;

        return $photoPost;
    }

    public function destroy(PhotoPost $photoPost)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Niet ingelogd.'], 401);
        }
        if ($photoPost->created_by !== Auth::id()) {
            return response()->json(['error' => 'Je bent niet de maker van deze FotoPost.'], 403);
        }

        $photoPost->delete();

        return response()->json(['message' => 'FotoPost succesvol verwijderd.']);
    }
}
