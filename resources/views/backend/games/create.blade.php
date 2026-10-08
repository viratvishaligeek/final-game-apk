@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="play-circle"></i></div>
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
            <div class="card-header">Game Details</div>
            <div class="card-body">
                <form action="{{ route('admin.games.store') }}" method="POST">
                    @csrf
                    <div class="row gx-3 mb-3">
                        <div class="col-md-5 mb-3">
                            <label class="small mb-1" for="name">Game Name <span class="text-danger">*</span></label>
                            <input class="form-control" id="name" name="name" type="text"
                                placeholder="e.g. Gali, Disawar" value="{{ old('name') }}" required />
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="small mb-1" for="reward">Reward <span class="text-danger">*</span></label>
                            <input class="form-control" id="reward" name="reward" type="number" step="0.01"
                                min="1" placeholder="1" value="{{ old('reward', 98) }}" required />
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="small mb-1" for="serial">Serial / Position <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" id="serial" name="serial" type="number" placeholder="1"
                                value="{{ old('serial', 1) }}" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status"
                                required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row gx-3 mb-3">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="play_start">Play Start Time <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" id="play_start" name="play_start" type="time"
                                value="{{ old('play_start') }}" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="play_end">Play End Time <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" id="play_end" name="play_end" type="time"
                                value="{{ old('play_end') }}" required />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="result_time">Result Time <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" id="result_time" name="result_time" type="time"
                                value="{{ old('result_time') }}" required />
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Add Game</button>
                </form>
            </div>
        </div>
    </div>
@endsection
