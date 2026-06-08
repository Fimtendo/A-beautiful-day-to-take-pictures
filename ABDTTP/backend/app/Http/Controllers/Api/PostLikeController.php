<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhotoPost;
use App\Models\PhotoPostLike;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function storeLike(Request $request, PhotoPost $photo_post)
    {
        $user = $request->user();

        PhotoPostLike::firstOrCreate([
            'user_id' => $user->id,
            'post_id' => $photo_post->id,
        ]);

        $photo_post->loadCount(['likes', 'bookmarks']);
        $photo_post->liked_by_user = true;
        $photo_post->bookmarked_by_user = $photo_post->bookmarks()->where('user_id', $user->id)->exists();

        return response()->json($photo_post, 201);
    }

    public function destroyLike(Request $request, PhotoPost $photo_post)
    {
        $user = $request->user();

        PhotoPostLike::where('user_id', $user->id)->where('post_id', $photo_post->id)->delete();

        $photo_post->loadCount(['likes', 'bookmarks']);
        $photo_post->liked_by_user = false;
        $photo_post->bookmarked_by_user = $photo_post->bookmarks()->where('user_id', $user->id)->exists();

        return response()->json($photo_post);
    }
}