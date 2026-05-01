@extends('layouts.app')

@section('title', 'New Issue')

@section('content')
<a href="{{ route('issues.index') }}" class="back-link">&#8592; All Issues</a>

<div style="max-width: 700px; margin-top: 0.5rem;">
    <h1>Submit New Issue</h1>

    <div class="card">
        <form method="POST" action="{{ route('issues.store') }}">
            @csrf

            <div class="field">
                <label for="title">Title <span class="req">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}"
                       placeholder="Brief, descriptive title for the issue">
                @error('title') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="description">Description <span class="req">*</span></label>
                <textarea id="description" name="description"
                          placeholder="Provide full details — what happened, when it started, and any steps to reproduce.">{{ old('description') }}</textarea>
                @error('description') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="field">
                    <label for="priority">Priority <span class="req">*</span></label>
                    <select id="priority" name="priority">
                        <option value="">Select priority...</option>
                        @foreach (['low', 'medium', 'high'] as $p)
                            <option value="{{ $p }}" @selected(old('priority') === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                    @error('priority') <div class="error-msg">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category"
                           value="{{ old('category') }}" placeholder="e.g. payment, auth, ui">
                    @error('category') <div class="error-msg">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="field">
                <label for="status">Initial Status</label>
                <select id="status" name="status">
                    @foreach (['open', 'in_progress', 'resolved', 'closed'] as $s)
                        <option value="{{ $s }}" @selected(old('status', 'open') === $s)>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
                @error('status') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <hr class="divider">

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">Submit Issue</button>
                <a href="{{ route('issues.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
