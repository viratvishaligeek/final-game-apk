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
        ]);

        Page::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'status' => $validated['status'],
            'content' => $validated['content'] ?? '',
            'is_editable' => $validated['is_editable'],
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
        ]);

        if ($page->is_editable === 'no') {
            $page->update([
                'content' => $validated['content'] ?? '',
                'is_editable' => $request->is_editable,
            ]);
        } else {
            $page->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'status' => $validated['status'],
                'content' => $validated['content'] ?? '',
                'is_editable' => $request->is_editable,
            ]);
        }
        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
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
