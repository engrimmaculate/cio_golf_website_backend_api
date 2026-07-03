<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::where('published_at', '<=', now());

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $news = $query->latest('published_at')->paginate($request->get('per_page', 12));

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
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(6)
            ->get();

        return response()->json($news);
    }
}
