<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhotoPost;
use App\Http\Requests\StorePhotoPostRequest; // <-- Importeer je nieuwe Request
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            
            // Voeg de volledige, absolute URL toe voor Vue (poort 8000 proof!)
            if ($post->image_url && !str_starts_with($post->image_url, 'http')) {
                $post->display_image = asset(Storage::url($post->image_url));
            } else {
                $post->display_image = $post->image_url;
            }
            
            return $post;
        });
    }

    public function store(StorePhotoPostRequest $request)
    {
        $data = $request->validated();

        // Fysieke bestandsupload verwerken voor de fotopost
        if ($request->hasFile('image')) {
            // Sla op in storage/app/public/posts/
            $path = $request->file('image')->store('posts', 'public');
            $data['image_url'] = $path;
        }

        $data['created_by'] = $request->user()->id;
        $data['created_by_username'] = $request->user()->username ?? $request->user()->email;

        $post = PhotoPost::create($data);

        // Voeg direct display_image toe aan de respons
        if ($post->image_url) {
            $post->display_image = asset(Storage::url($post->image_url));
        }

        return response()->json($post, 201);
    }

    public function show(Request $request, PhotoPost $photoPost)
    {
        $user = Auth::guard('sanctum')->user() ?: $request->user();
        $userId = $user ? $user->id : null;

        $photoPost->loadCount(['likes', 'bookmarks']);
        $photoPost->liked_by_user = $userId ? $photoPost->likes()->where('user_id', $userId)->exists() : false;
        $photoPost->bookmarked_by_user = $userId ? $photoPost->bookmarks()->where('user_id', $userId)->exists() : false;

        if ($photoPost->image_url && !str_starts_with($photoPost->image_url, 'http')) {
            $photoPost->display_image = asset(Storage::url($photoPost->image_url));
        } else {
            $photoPost->display_image = $photoPost->image_url;
        }

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

        if ($photoPost->image_url && Storage::disk('public')->exists($photoPost->image_url)) {
            Storage::disk('public')->delete($photoPost->image_url);
        }

        $photoPost->delete();

        return response()->json(['message' => 'FotoPost succesvol verwijderd.']);
    }
}
