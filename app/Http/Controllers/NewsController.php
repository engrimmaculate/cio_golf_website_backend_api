<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $news = $query->orderByDesc('published_at')
            ->paginate(10);

        return response()->json($news);
    }

    public function show($slug)
    {
        $news = News::where('slug', $slug)->firstOrFail();

        return response()->json($news);
    }

    public function featured()
    {
        $news = News::where('featured', true)
            ->orderByDesc('published_at')
            ->paginate(5);

        return response()->json($news);
    }
}
