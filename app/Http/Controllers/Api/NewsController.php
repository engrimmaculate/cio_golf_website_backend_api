<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'image' => 'nullable|string',
            'category' => 'required|string',
            'published_at' => 'nullable|date',
            'featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $news = News::create($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_news',
            'model_type' => News::class,
            'model_id' => $news->id,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'News article created.', 'news' => $news], 201);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string',
            'image' => 'nullable|string',
            'category' => 'sometimes|string',
            'published_at' => 'nullable|date',
            'featured' => 'boolean',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $news->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_news',
            'model_type' => News::class,
            'model_id' => $news->id,
            'old_values' => $news->getOriginal(),
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'News article updated.', 'news' => $news->fresh()]);
    }

    public function destroy(Request $request, $id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_news',
            'model_type' => News::class,
            'model_id' => $id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'News article deleted.']);
    }
}
