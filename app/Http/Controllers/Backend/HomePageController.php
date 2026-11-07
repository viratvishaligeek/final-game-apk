<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\HomePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomePageController extends Controller
{
    public function index(): View
    {
        $pageName = 'Home Page Sections';
        $homePages = HomePage::query()->latest('id')->get();
        return view('backend.home-page.index', compact('pageName', 'homePages'));
    }

    public function create(): View
    {
        $pageName = 'Add Home Page Section';
        $locations = [
            'first_place' => 'First Place Of Top Page',
            'second_place' => 'Second Place',
            'second_place_another' => 'Just After Second One',
            'third_place' => 'Third Place',
            'third_place_another' => 'Just After Third One',
            'before_footer' => 'Before Footer',
            'after_footer' => 'After Footer',
        ];
        return view('backend.home-page.create', compact('pageName', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255',],
            'content' => ['nullable', 'string', 'max:50000'],
            'status' => ['required', 'in:active,inactive',],
            'location' => ['required', 'in:first_place,second_place,second_place_another,third_place,third_place_another,before_footer,after_footer'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'telegram' => ['nullable', 'string', 'max:255'],
            'background' => ['nullable', 'string', 'max:255'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'button_label' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:1000'],
            'button_style' => ['nullable', 'in:primary,secondary,outline,whatsapp,telegram'],
            'layout' => ['nullable', 'in:standard,image-left,image-right,image-top,banner'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
        ]);
        $image = $request->file('image_file')
            ? app(ProjectStorage::class)->storePublicFile($request->file('image_file'), 'homepage-sections')
            : null;
        unset($validated['image_file']);
        if ($image) {
            $validated['image'] = $image;
        }
        return redirect()
            ->route('admin.home-page.index')
            ->with('success', 'Home page section created successfully.');
    }

    public function edit(string $id): View
    {
        $pageName = 'Edit Home Page Section';
        $homePage = HomePage::findOrFail($id);
        $locations = [
            'first_place' => 'First Place Of Top Page',
            'second_place' => 'Second Place',
            'second_place_another' => 'Just After Second One',
            'third_place' => 'Third Place',
            'third_place_another' => 'Just After Third One',
            'before_footer' => 'Before Footer',
            'after_footer' => 'After Footer',
        ];
        return view('backend.home-page.edit', compact('pageName', 'homePage', 'locations'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $homePage = HomePage::findOrFail($id);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string',],
            'status' => ['required', 'in:active,inactive',],
            'location' => ['required', 'string', 'max:255',],
            'whatsapp' => ['nullable', 'string', 'max:255',],
            'phone' => ['nullable', 'string', 'max:255',],
            'telegram' => ['nullable', 'string', 'max:255',],
            'background' => ['nullable', 'string', 'max:255',],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'button_label' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:1000'],
            'button_style' => ['nullable', 'in:primary,secondary,outline,whatsapp,telegram'],
            'layout' => ['nullable', 'in:standard,image-left,image-right,image-top,banner'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $oldImage = $homePage->image;
        if ($request->boolean('remove_image')) {
            $validated['image'] = null;
        }
        if ($request->file('image_file')) {
            $validated['image'] = app(ProjectStorage::class)->storePublicFile($request->file('image_file'), 'homepage-sections');
        }
        unset($validated['image_file'], $validated['remove_image']);
        $homePage->update($validated);
        if ($oldImage && $oldImage !== $homePage->image) {
            app(ProjectStorage::class)->deletePublicFile($oldImage);
        }

        return redirect()
            ->route('admin.home-page.index')
            ->with('success', 'Home page section updated successfully.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $homePage = HomePage::findOrFail($id);
        if ($homePage->image) {
            app(ProjectStorage::class)->deletePublicFile($homePage->image);
        }
        $homePage->delete();
        return redirect()
            ->route('admin.home-page.index')
            ->with('success', 'Home page section deleted successfully.');
    }
}
