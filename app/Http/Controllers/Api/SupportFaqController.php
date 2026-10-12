<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SupportFaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['nullable', 'in:web,app'],
        ]);
        $platform = $validated['platform'] ?? 'web';

        $siteKey = (string) config('sites.current_key', config('sites.default', 'play-online-khaiwal'));
        $faqs = Cache::remember("support-faqs:{$siteKey}:{$platform}", now()->addMinutes(5), function () use ($platform) {
            return Faq::query()
                ->whereIn('platform', [$platform, 'both'])
                ->orderBy('id')
                ->get(['id', 'question', 'answer', 'platform'])
                ->map(fn (Faq $faq) => [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'platform' => $faq->platform ?: 'both',
                ])
                ->values();
        });

        return response()->json(['success' => true, 'data' => $faqs]);
    }
}
