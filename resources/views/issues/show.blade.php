@extends('layouts.app')

@section('title', $issue->title)

@section('content')

{{-- Escalation banner --}}
@if ($issue->is_escalated)
<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.1rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem; font-size: 0.875rem; color: #991b1b; font-weight: 600;">
    <span style="font-size: 1rem;">&#9888;</span>
    This issue is escalated and requires immediate attention.
</div>
@endif

{{-- Page header --}}
<a href="{{ route('issues.index') }}" class="back-link">&#8592; All Issues</a>

<div class="page-header" style="margin-top: 0.25rem;">
    <div>
        <h1 style="margin-bottom: 0.6rem;">{{ $issue->title }}</h1>
        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
            <span class="badge badge-{{ $issue->priority }}">{{ ucfirst($issue->priority) }}</span>
            <span class="badge badge-{{ $issue->status }}">{{ ucfirst(str_replace('_', ' ', $issue->status)) }}</span>
            @if ($issue->category)
                <span style="font-size: 0.72rem; font-weight: 700; background: var(--bg); color: var(--text-secondary); padding: 0.2rem 0.65rem; border-radius: 9999px; border: 1px solid var(--border); text-transform: uppercase; letter-spacing: 0.04em;">
                    {{ $issue->category }}
                </span>
            @endif
            @if ($issue->is_escalated)
                <span class="badge badge-escalated">&#9650; Escalated</span>
            @endif
        </div>
    </div>
    <a href="{{ route('issues.edit', $issue) }}" class="btn btn-secondary">Edit Issue</a>
</div>

{{-- Two-column layout --}}
<div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: start;">

    {{-- Left: description + summary --}}
    <div>
        <div class="card">
            <h2>Description</h2>
            <p style="color: var(--text-secondary); white-space: pre-wrap; line-height: 1.7; font-size: 0.9rem;">{{ $issue->description }}</p>
        </div>

        <div class="card" style="border-left: 3px solid var(--primary);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                <h2 style="margin-bottom: 0;">Smart Summary</h2>
                @if (config('services.openrouter.key'))
                    <span style="font-size: 0.68rem; font-weight: 700; background: #f5f3ff; color: #5b21b6; padding: 0.2rem 0.6rem; border-radius: 9999px; border: 1px solid #ddd6fe; text-transform: uppercase; letter-spacing: 0.05em;">AI</span>
                @else
                    <span style="font-size: 0.68rem; font-weight: 700; background: var(--bg); color: var(--text-muted); padding: 0.2rem 0.6rem; border-radius: 9999px; border: 1px solid var(--border); text-transform: uppercase; letter-spacing: 0.05em;">Rules</span>
                @endif
            </div>

            <p style="color: var(--text); font-size: 0.9rem; line-height: 1.6;">
                {{ $issue->summary ?? 'No summary generated.' }}
            </p>

            @if ($issue->suggested_action)
                <hr class="divider">
                <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.4rem;">
                    Suggested Action
                </div>
                <div style="font-size: 0.875rem; color: var(--text); background: var(--bg); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    {{ $issue->suggested_action }}
                </div>
            @endif
        </div>
    </div>

    {{-- Right: metadata + quick update --}}
    <div>
        <div class="card">
            <h2>Details</h2>

            <div class="detail-row">
                <div class="label">Issue ID</div>
                <div class="value" style="font-weight: 600;">#{{ $issue->id }}</div>
            </div>
            <div class="detail-row">
                <div class="label">Priority</div>
                <div class="value"><span class="badge badge-{{ $issue->priority }}">{{ ucfirst($issue->priority) }}</span></div>
            </div>
            <div class="detail-row">
                <div class="label">Status</div>
                <div class="value"><span class="badge badge-{{ $issue->status }}">{{ ucfirst(str_replace('_', ' ', $issue->status)) }}</span></div>
            </div>
            <div class="detail-row">
                <div class="label">Category</div>
                <div class="value">{{ $issue->category ?: '—' }}</div>
            </div>
            <div class="detail-row">
                <div class="label">Escalated</div>
                <div class="value">
                    @if ($issue->is_escalated)
                        <span class="badge badge-escalated">&#9650; Yes</span>
                    @else
                        <span class="text-muted">No</span>
                    @endif
                </div>
            </div>

            <hr class="divider">

            <div class="detail-row">
                <div class="label">Created</div>
                <div class="value" style="font-size: 0.825rem;">{{ $issue->created_at->format('M j, Y') }}</div>
            </div>
            <div class="detail-row" style="margin-bottom: 0;">
                <div class="label">Last Updated</div>
                <div class="value" style="font-size: 0.825rem;">{{ $issue->updated_at->diffForHumans() }}</div>
            </div>
        </div>

        <div class="card">
            <h2>Update Status</h2>
            <form method="POST" action="{{ route('issues.update', $issue) }}">
                @csrf
                @method('PUT')
                <div class="field" style="margin-bottom: 0.85rem;">
                    <select name="status">
                        @foreach (['open', 'in_progress', 'resolved', 'closed'] as $s)
                            <option value="{{ $s }}" @selected($issue->status === $s)>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Update Status
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
