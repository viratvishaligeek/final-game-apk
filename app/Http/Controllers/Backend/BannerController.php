<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ProjectStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::latest()->get();
        $pageName = 'Banner Management';

        return view('backend.banner.index', compact('banners', 'pageName'));
    }

    public function create(): View
    {
        $pageName = 'Create Banner';

        return view('backend.banner.create', compact('pageName'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'image'  => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:15360'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = app(ProjectStorage::class)->storePublicFile($request->file('image'), 'banners');
        }

        Banner::create($validated);

        return redirect()->route('admin.banner.index')->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner): View
    {
        $pageName = 'Edit Banner';

        return view('backend.banner.edit', compact('banner', 'pageName'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'image'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $oldImage = $banner->image;
        if ($request->hasFile('image')) {
            // Save the replacement first; keep the old file until the record updates.
            $validated['image'] = app(ProjectStorage::class)->storePublicFile($request->file('image'), 'banners');
        } else {
            unset($validated['image']);
        }

        $banner->update($validated);
        if ($request->hasFile('image') && $oldImage && $oldImage !== $banner->image) {
            app(ProjectStorage::class)->deletePublicFile($oldImage);
        }

        return redirect()->route('admin.banner.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        app(ProjectStorage::class)->deletePublicFile($banner->image);

        $banner->delete();

        return redirect()->route('admin.banner.index')->with('success', 'Banner deleted successfully.');
    }
}
