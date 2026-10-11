@extends('backend.include.layout')
@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="message-circle"></i></div> Guessing Community
                            Moderation
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection
@section('content')
    <div class="container-fluid px-4">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        <form class="card mb-4" method="POST" action="{{ route('admin.guessing.policy') }}">@csrf
            <div class="card-header">Community Post Approval</div>
            <div class="card-body"><label for="community_post_approval">New post policy</label><select class="form-select"
                    id="community_post_approval" name="community_post_approval">
                    <option value="manual"
                        {{ setting('community_post_approval', 'manual') === 'manual' ? 'selected' : '' }}>Manual Approval
                    </option>
                    <option value="automatic"
                        {{ setting('community_post_approval', 'manual') === 'automatic' ? 'selected' : '' }}>Automatic
                        Approval</option>
                </select><small class="text-muted">Changing this setting affects new posts only. Existing pending posts
                    remain pending until an administrator moderates them.</small>
                @error('community_post_approval')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Save policy</button></div>
        </form>
        <div class="card">
            <div class="card-header">Community posts</div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th>Game</th>
                            <th>Author</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Moderation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td><strong>{{ $post->title }}</strong>
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($post->body, 180) }}
                                    </div>
                                </td>
                                <td>{{ $post->game->name }}</td>
                                <td>{{ $post->author->name }}</td>
                                <td>{{ $post->created_at->format('d M Y H:i') }}</td>
                                <td>{{ ucfirst($post->status) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.guessing.moderate', $post) }}">@csrf
                                        @method('PATCH')<select name="status" class="form-select form-select-sm mb-2"
                                            aria-label="Moderation status for {{ $post->title }}">
                                            <option value="pending" {{ $post->status === 'pending' ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="approved" {{ $post->status === 'approved' ? 'selected' : '' }}>
                                                Approve</option>
                                            <option value="rejected" {{ $post->status === 'rejected' ? 'selected' : '' }}>
                                                Reject</option>
                                        </select><button class="btn btn-sm btn-primary" type="submit">Save</button></form>
                                </td>
                        </tr>@empty<tr>
                                <td colspan="6" class="text-center">No community posts have been submitted.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $posts->links() }}</div>
        </div>
    </div>
@endsection
