<?php

namespace App\Http\Controllers\Backend;

use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PagesController extends Controller
{
    public function index()
    {
        $pageName = 'Pages List';
        $pages = Page::get();
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
            'name'    => 'required|string|max:255|unique:pages,name',
            'status'  => 'required|in:active,inactive',
            'content' => 'nullable|string',
        ]);

        Page::create([
            'name'    => $request->name,
            'slug'    => Str::slug($request->name),
            'status'  => $request->status,
            'content' => $request->content,
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

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:pages,name,' . $page->id,
            'status'  => 'required|in:active,inactive',
            'content' => 'nullable|string',
        ]);
        $page->update([
            'name'    => $request->name,
            'slug'    => Str::slug($request->name),
            'status'  => $request->status,
            'content' => $request->content,
        ]);
        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }
    public function destroy(string $id)
    {
        $page = Page::findOrFail($id);
        $page->delete();
        return redirect()->back()->with('success', 'Page deleted.');
    }
}
