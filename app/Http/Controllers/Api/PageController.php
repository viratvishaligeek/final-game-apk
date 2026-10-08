<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Page fetched successfully.',
            'data' => [
                'id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
                'content' => $page->content,
            ],
        ]);
    }
}
