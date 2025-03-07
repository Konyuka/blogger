<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostView;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::all();
        return response()->json($posts);
    }

    public function featured()
    {
        // Latest post
        $latestPost = Post::where('active', '=', 1)
            ->whereDate('published_at', '<', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->limit(1)
            ->first();

        // Show the most popular 3 posts based on upvotes
        $popularPosts = Post::query()
            ->leftJoin('upvote_downvotes', 'posts.id', '=', 'upvote_downvotes.post_id')
            ->select('posts.*', DB::raw('COUNT(upvote_downvotes.id) as upvote_count'))
            ->where(function ($query) {
                $query->whereNull('upvote_downvotes.is_upvote')
                    ->orWhere('upvote_downvotes.is_upvote', '=', 1);
            })
            ->where('active', '=', 1)
            ->whereDate('published_at', '<', Carbon::now())
            ->orderByDesc('upvote_count')
            ->groupBy([
                'posts.id',
                'posts.title',
                'posts.slug',
                'posts.thumbnail',
                'posts.body',
                'posts.active',
                'posts.published_at',
                'posts.user_id',
                'posts.created_at',
                'posts.updated_at',
                'posts.meta_title',
                'posts.meta_description',
            ])
            ->limit(5)
            ->get();

        $recommendedPosts = Post::query()
            ->leftJoin('post_views', 'posts.id', '=', 'post_views.post_id')
            ->select('posts.*', DB::raw('COUNT(post_views.id) as view_count'))
            ->where('active', '=', 1)
            ->whereDate('published_at', '<', Carbon::now())
            ->orderByDesc('view_count')
            ->groupBy([
                'posts.id',
                'posts.title',
                'posts.slug',
                'posts.thumbnail',
                'posts.body',
                'posts.active',
                'posts.published_at',
                'posts.user_id',
                'posts.created_at',
                'posts.updated_at',
                'posts.meta_title',
                'posts.meta_description',
            ])
            ->limit(3)
            ->get();

        return response()->json([
            'latest' => $latestPost,
            'popular' => $popularPosts,
            'recommended' => $recommendedPosts,
        ]);
    }

    public function show(string $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $postView = new PostView();
        $postView->post_id = $post->id;
        $postView->ip_address = request()->ip();
        $postView->user_agent = request()->userAgent();
        $postView->save();

        return response()->json($post);
    }

    public function byCategory(Category $category)
    {
        // dd($category);
        $posts = Post::query()
            ->join('category_post', 'posts.id', '=', 'category_post.post_id')
            ->where('category_post.category_id', '=', $category->id)
            ->where('active', '=', true)
            ->whereDate('published_at', '<=', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json([
            'posts' => $posts,
            'category' => $category,
        ]);

    }

}
