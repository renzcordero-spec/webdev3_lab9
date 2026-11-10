<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostApiController extends Controller
{
    public function index()
    {
        return PostResource::collection(
            Post::with('user')->latest()->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $post = $request->user()->posts()->create($data);

        return (new PostResource($post->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Post $post)
    {
        return new PostResource($post->load('user'));
    }

    public function update(Request $request, Post $post)
    {
        abort_unless(
            $request->user()->id === $post->user_id,
            403,
            'You cannot update this post.'
        );

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
        ]);

        $post->update($data);

        return new PostResource($post->load('user'));
    }

    public function destroy(Request $request, Post $post)
    {
        abort_unless(
            $request->user()->id === $post->user_id,
            403,
            'You cannot delete this post.'
        );

        $post->delete();

        return response()->noContent();
    }
}