<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'image'  => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('banners', 'public');
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

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('banners', 'public');
        } else {
            unset($validated['image']);
        }

        $banner->update($validated);

        return redirect()->route('admin.banner.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return redirect()->route('admin.banner.index')->with('success', 'Banner deleted successfully.');
    }
}
