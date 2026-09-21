@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="edit"></i></div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a class="btn btn-sm btn-light text-secondary" href="{{ route('admin.games.index') }}">
                            <i class="me-1" data-feather="arrow-left"></i>
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">Edit Game Details</div>
            <div class="card-body">
                <form action="{{ route('admin.games.update', $game->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row gx-3 mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="name">Game Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" type="text" value="{{ old('name', $game->name) }}"
                                required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="serial">Serial / Position <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" name="serial" type="number"
                                value="{{ old('serial', $game->serial) }}" required />
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status"
                                required>
                                <option value="active" {{ old('status', $game->status) === 'active' ? 'selected' : '' }}>
                                    Active</option>
                                <option value="inactive"
                                    {{ old('status', $game->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="row gx-3 mb-3">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="play_start">Play Start Time <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" name="play_start" type="time"
                                value="{{ old('play_start', \Carbon\Carbon::parse($game->play_start)->format('H:i')) }}"
                                required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="play_end">Play End Time <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" name="play_end" type="time"
                                value="{{ old('play_end', \Carbon\Carbon::parse($game->play_end)->format('H:i')) }}"
                                required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="result_time">Result Time <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" name="result_time" type="time"
                                value="{{ old('result_time', \Carbon\Carbon::parse($game->result_time)->format('H:i')) }}"
                                required />
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Update Game</button>
                </form>
            </div>
        </div>
    </div>
@endsection
