<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\Game;
use App\Services\AppSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(): View
    {
        $posts = CommunityPost::query()
            ->where('status', 'approved')
            ->with([
                'game:id,name,slug',
                'author:id,name',
                'replies' => function ($query) {
                    $query->with('author:id,name')
                        ->latest('created_at')
                        ->limit(3);
                },
            ])
            ->withCount('replies')
            ->latest('created_at')
            ->paginate(15);

        return view('frontend.community.index', compact('posts'));
    }
    public function create(): View
    {
        $games = Game::query()->where('status', 'active')->orderBy('serial')->orderBy('name')->get(['id', 'name']);

        return view('frontend.community.create', compact('games'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'game_id' => ['required', 'integer', Rule::exists('games', 'id')->where(fn($query) => $query->where('status', 'active')->whereNull('deleted_at'))],
            'title' => ['required', 'string', 'min:4', 'max:120'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $title = trim(strip_tags($data['title']));
        $body = trim(strip_tags($data['body']));
        if ($title === '' || $body === '') {
            return back()->withInput()->withErrors(['body' => 'Please enter a title and meaningful written content.']);
        }

        $policy = app(AppSettingsService::class)->value('community_post_approval', 'manual');
        $status = $policy === 'automatic' ? 'approved' : 'pending';
        $post = CommunityPost::create([
            'game_id' => (int) $data['game_id'],
            'user_id' => Auth::guard('web')->id(),
            'title' => $title,
            'body' => $body,
            'status' => $status,
        ]);

        return $status === 'approved'
            ? redirect()->route('community.show', $post)->with('success', 'Your post has been published.')
            : redirect()->route('community.index')->with('success', 'Your post was submitted for administrator approval.');
    }

    public function show(int $post): View
    {
        $post = CommunityPost::query()
            ->where('status', 'approved')
            ->with(['game:id,name,slug', 'author:id,name', 'replies.author:id,name'])
            ->withCount('replies')
            ->findOrFail($post);

        return view('frontend.community.show', compact('post'));
    }

    public function reply(Request $request, int $post): RedirectResponse
    {
        $post = CommunityPost::query()->where('status', 'approved')->findOrFail($post);
        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:2000']]);
        $body = trim(strip_tags($data['body']));
        if ($body === '') {
            return back()->withInput()->withErrors(['body' => 'Reply content cannot be empty.']);
        }

        $post->replies()->create([
            'user_id' => Auth::guard('web')->id(),
            'body' => $body,
        ]);

        return redirect()->route('community.show', $post->id)->with('success', 'Your reply was added.');
    }
}
