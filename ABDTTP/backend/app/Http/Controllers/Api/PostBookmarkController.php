<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhotoPost;
use App\Models\PhotoPostBookmark;
use Illuminate\Http\Request;

class PostBookmarkController extends Controller
{
    public function storeBookmark(Request $request, PhotoPost $photo_post)
    {
        $user = $request->user();

        PhotoPostBookmark::firstOrCreate([
            'user_id' => $user->id,
            'post_id' => $photo_post->id,
        ]);

        $photo_post->loadCount(['likes', 'bookmarks']);
        $photo_post->liked_by_user = $photo_post->likes()->where('user_id', $user->id)->exists();
        $photo_post->bookmarked_by_user = true;

        return response()->json($photo_post, 201);
    }

    public function destroyBookmark(Request $request, PhotoPost $photo_post)
    {
        $user = $request->user();

        PhotoPostBookmark::where('user_id', $user->id)->where('post_id', $photo_post->id)->delete();

        $photo_post->loadCount(['likes', 'bookmarks']);
        $photo_post->liked_by_user = $photo_post->likes()->where('user_id', $user->id)->exists();
        $photo_post->bookmarked_by_user = false;

        return response()->json($photo_post);
    }
}
