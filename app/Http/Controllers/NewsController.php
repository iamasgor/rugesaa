<?php

namespace App\Http\Controllers;

use App\Enums\NewsCategory;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $category = NewsCategory::tryFrom((string) $request->query('category'));
        $isFirstPage = (int) $request->query('page', '1') <= 1;

        $featured = $category === null && $isFirstPage
            ? Post::query()->published()->where('is_featured', true)->first() ?? Post::query()->published()->first()
            : null;

        $posts = Post::query()
            ->published()
            ->when($category, fn ($query, NewsCategory $category) => $query->where('category', $category))
            ->when($featured, fn ($query) => $query->whereKeyNot($featured->id))
            ->paginate(9)
            ->withQueryString();

        return view('news.index', [
            'featured' => $featured,
            'posts' => $posts,
            'activeCategory' => $category,
        ]);
    }

    public function show(Request $request, Post $post): View
    {
        abort_unless($post->isPublished() || $request->user()?->is_admin, 404);

        return view('news.show', [
            'post' => $post,
            'related' => Post::query()
                ->published()
                ->where('category', $post->category)
                ->whereKeyNot($post->id)
                ->limit(3)
                ->get(),
        ]);
    }
}
