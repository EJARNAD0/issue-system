@extends('layouts.app')

@section('title', 'Edit Issue #' . $issue->id)

@section('content')
<a href="{{ route('issues.show', $issue) }}" class="back-link">&#8592; Back to Issue</a>

<div style="max-width: 700px; margin-top: 0.5rem;">
    <h1>Edit Issue <span style="color: var(--text-muted); font-weight: 400;">#{{ $issue->id }}</span></h1>

    <div class="card">
        <form method="POST" action="{{ route('issues.update', $issue) }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="title">Title <span class="req">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $issue->title) }}">
                @error('title') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="description">Description <span class="req">*</span></label>
                <textarea id="description" name="description">{{ old('description', $issue->description) }}</textarea>
                @error('description') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="field">
                    <label for="priority">Priority <span class="req">*</span></label>
                    <select id="priority" name="priority">
                        @foreach (['low', 'medium', 'high'] as $p)
                            <option value="{{ $p }}" @selected(old('priority', $issue->priority) === $p)>
                                {{ ucfirst($p) }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority') <div class="error-msg">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category"
                           value="{{ old('category', $issue->category) }}"
                           placeholder="e.g. payment, auth, ui">
                    @error('category') <div class="error-msg">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    @foreach (['open', 'in_progress', 'resolved', 'closed'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $issue->status) === $s)>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
                @error('status') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <hr class="divider">

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('issues.show', $issue) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
