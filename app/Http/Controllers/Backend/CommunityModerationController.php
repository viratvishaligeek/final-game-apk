<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\Setting;
use App\Services\AppSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunityModerationController extends Controller
{
    public function index(): View
    {
        $posts = CommunityPost::query()
            ->with(['game:id,name', 'author:id,name'])
            ->latest('created_at')
            ->paginate(25);

        return view('backend.community.index', compact('posts'));
    }

    public function update(Request $request, CommunityPost $post): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'rejected', 'pending'])]]);
        $post->forceFill([
            'status' => $data['status'],
            'moderated_by' => auth('admin')->id(),
            'moderated_at' => now(),
        ])->save();

        return back()->with('success', 'Community post moderation status updated.');
    }

    public function updatePolicy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'community_post_approval' => ['required', Rule::in(['manual', 'automatic'])],
        ]);
        Setting::updateOrCreate(
            ['option' => 'community_post_approval'],
            ['value' => $data['community_post_approval']]
        );
        app(AppSettingsService::class)->forgetCache();

        return back()->with('success', 'Community post approval policy saved. Existing pending posts remain pending and are not automatically published.');
    }
}
