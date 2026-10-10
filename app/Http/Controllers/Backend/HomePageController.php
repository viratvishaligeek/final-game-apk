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
            'first_place_another' => 'Just After First One',
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
            'short_desc' => ['nullable', 'string', 'max:10000'],
            'content' => ['nullable', 'string', 'max:50000'],
            'status' => ['required', 'in:active,inactive',],
            'location' => ['required', 'in:first_place,first_place_another,second_place,second_place_another,third_place,third_place_another,before_footer,after_footer'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'telegram' => ['nullable', 'string', 'max:255'],
            'background' => ['nullable', 'string', 'max:255'],
        ]);
        HomePage::create($validated);
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
            'first_place_another' => 'Just After First One',
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
            'short_desc' => ['required', 'string',],
            'content' => ['required', 'string',],
            'status' => ['required', 'in:active,inactive',],
            'location' => ['required', 'string', 'max:255',],
            'whatsapp' => ['required', 'string', 'max:255',],
            'phone' => ['required', 'string', 'max:255',],
            'telegram' => ['required', 'string', 'max:255',],
            'background' => ['required', 'string', 'max:255',],
        ]);

        $homePage->update($validated);

        return redirect()
            ->route('admin.home-page.index')
            ->with('success', 'Home page section updated successfully.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $homePage = HomePage::findOrFail($id);
        $homePage->delete();
        return redirect()
            ->route('admin.home-page.index')
            ->with('success', 'Home page section deleted successfully.');
    }
}
