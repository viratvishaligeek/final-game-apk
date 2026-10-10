<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PagesController extends Controller
{
    public function index()
    {
        $pageName = 'Pages List';
        $pages = Page::query()->latest('id')->get();
        return view('backend.pages.index', compact('pageName', 'pages'));
    }

    public function create()
    {
        $pageName = 'Add Page';
        return view('backend.pages.create', compact('pageName'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:pages,name'],
            'status' => ['required', 'in:active,inactive'],
            'content' => ['nullable', 'string'],
            'is_editable' => ['required', 'in:yes,no'],
            'menu_visible' => ['sometimes', 'boolean'],
            'menu_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:2000'],
            'meta_keywords' => ['nullable', 'string', 'max:2000'],
            'noindex' => ['sometimes', 'boolean'],
        ]);

        Page::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'status' => $validated['status'],
            'content' => $validated['content'] ?? '',
            'is_editable' => $validated['is_editable'],
            'menu_visible' => $request->boolean('menu_visible', true),
            'menu_order' => $validated['menu_order'] ?? 0,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'noindex' => $request->boolean('noindex'),
        ]);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(string $id)
    {
        $pageName = 'Edit Page';
        $page = Page::findOrFail($id);
        return view('backend.pages.edit', compact('page', 'pageName'));
    }

    public function update(Request $request, string $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:pages,name,' . $page->id],
            'status' => ['required', 'in:active,inactive'],
            'content' => ['nullable', 'string'],
            'is_editable' => ['required', 'in:yes,no'],
            'menu_visible' => ['sometimes', 'boolean'],
            'menu_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:2000'],
            'meta_keywords' => ['nullable', 'string', 'max:2000'],
            'noindex' => ['sometimes', 'boolean'],
        ]);

        $metadata = [
            'menu_visible' => $request->boolean('menu_visible'),
            'menu_order' => $validated['menu_order'] ?? 0,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'noindex' => $request->boolean('noindex'),
        ];

        if ($page->is_editable === 'no') {
            $page->update(array_merge([
                'content' => $validated['content'] ?? '',
            ], $metadata));
        } else {
            $page->update(array_merge([
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name'], (int) $page->id),
                'status' => $validated['status'],
                'content' => $validated['content'] ?? '',
                'is_editable' => $validated['is_editable'],
            ], $metadata));
        }
        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'page';
        $slug = $base;
        $suffix = 2;

        while (Page::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    public function destroy(string $id)
    {
        $page = Page::findOrFail($id);

        if ($page->is_editable === 'yes') {
            $page->delete();
            return redirect()
                ->route('admin.pages.index')
                ->with('success', 'Page deleted successfully.');
        }

        return redirect()
            ->back()
            ->with('error', 'This is a permanent page and cannot be deleted.');
    }
}
