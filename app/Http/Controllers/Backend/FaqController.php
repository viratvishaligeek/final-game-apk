<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Services\FaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __construct(
        private FaqService $faqService
    ) {}

    public function index(): View
    {
        $faqs = $this->faqService->getAll();
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
        ]);
        $this->faqService->create($validated);
        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ created successfully.');
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
        ]);

        $this->faqService->update($faq, $validated);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $this->faqService->delete($faq);
        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ deleted successfully.');
    }
}
