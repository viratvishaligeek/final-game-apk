<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::latest()->get();
        $pageName = 'FAQs Management';
        return view('backend.faqs.index', compact('faqs', 'pageName'));
    }

    public function create(): View
    {
        $pageName = 'Create FAQ';
        return view('backend.faqs.create', compact('pageName'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'platform' => ['required', 'in:web,app,both'],
        ]);
        Faq::create($validated);
        $this->clearFaqCache();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function edit(Faq $faq): View
    {
        $pageName = 'Edit FAQ';
        return view('backend.faqs.edit', compact('faq', 'pageName'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'platform' => ['required', 'in:web,app,both'],
        ]);
        $faq->update($validated);
        $this->clearFaqCache();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();
        $this->clearFaqCache();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted successfully.');
    }

    private function clearFaqCache(): void
    {
        $siteKey = (string) config('sites.current_key', config('sites.default', 'play-online-khaiwal'));
        Cache::forget("support-faqs:{$siteKey}:web");
        Cache::forget("support-faqs:{$siteKey}:app");
    }
}
