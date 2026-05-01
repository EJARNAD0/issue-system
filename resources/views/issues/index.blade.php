@extends('layouts.app')

@section('title', 'Issues')

@section('content')

<div class="page-header">
    <h1>Issues</h1>
    <a href="{{ route('issues.create') }}" class="btn btn-primary">+ New Issue</a>
</div>

{{-- Stats strip --}}
<div class="stats-strip">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Issues</div>
    </div>
    <div class="stat-card stat-primary">
        <div class="stat-value">{{ $stats['open'] }}</div>
        <div class="stat-label">Open</div>
    </div>
    <div class="stat-card stat-danger">
        <div class="stat-value">{{ $stats['escalated'] }}</div>
        <div class="stat-label">Escalated</div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-value">{{ $stats['resolved'] }}</div>
        <div class="stat-label">Resolved / Closed</div>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="padding: 1rem 1.25rem; margin-bottom: 1rem;">
    <form method="GET" action="{{ route('issues.index') }}">
        <div class="filter-bar">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">All Statuses</option>
                    @foreach (['open', 'in_progress', 'resolved', 'closed'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Priority</label>
                <select name="priority">
                    <option value="">All Priorities</option>
                    @foreach (['low', 'medium', 'high'] as $p)
                        <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Category</label>
                <input type="text" name="category" value="{{ request('category') }}" placeholder="e.g. payment, auth">
            </div>

            <div style="display: flex; gap: 0.5rem; align-self: flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if (request()->hasAny(['status', 'priority', 'category']))
                    <a href="{{ route('issues.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card" style="padding: 0; overflow: hidden;">
    @if ($issues->isEmpty())
        <div style="padding: 3.5rem 2rem; text-align: center;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4;">&#128273;</div>
            <p style="font-weight: 700; color: var(--text); margin-bottom: 0.4rem; font-size: 1rem;">No issues found</p>
            <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 1.25rem;">
                {{ request()->hasAny(['status','priority','category']) ? 'No issues match your current filters.' : 'Create your first issue to get started.' }}
            </p>
            @if (request()->hasAny(['status','priority','category']))
                <a href="{{ route('issues.index') }}" class="btn btn-secondary btn-sm">Clear filters</a>
            @else
                <a href="{{ route('issues.create') }}" class="btn btn-primary btn-sm">+ New Issue</a>
            @endif
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 48px;">#</th>
                    <th>Title</th>
                    <th style="width: 100px;">Priority</th>
                    <th style="width: 110px;">Category</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 110px;">Escalated</th>
                    <th style="width: 120px;">Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($issues as $issue)
                <tr @if($issue->is_escalated) style="border-left: 3px solid var(--danger);" @endif>
                    <td class="text-muted" style="font-size: 0.78rem; font-weight: 600;">#{{ $issue->id }}</td>
                    <td>
                        <a href="{{ route('issues.show', $issue) }}"
                           style="color: var(--primary-dark); font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                            {{ $issue->title }}
                        </a>
                        @if ($issue->summary)
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem; line-height: 1.4;">
                                {{ $issue->summary }}
                            </div>
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $issue->priority }}">{{ ucfirst($issue->priority) }}</span></td>
                    <td>
                        @if ($issue->category)
                            <span style="font-size: 0.8rem; color: var(--text-secondary); background: var(--bg); padding: 0.15rem 0.55rem; border-radius: 9999px; border: 1px solid var(--border);">
                                {{ $issue->category }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $issue->status }}">{{ ucfirst(str_replace('_', ' ', $issue->status)) }}</span></td>
                    <td>
                        @if ($issue->is_escalated)
                            <span class="badge badge-escalated">&#9650; Escalated</span>
                        @else
                            <span class="text-muted" style="font-size: 0.8rem;">—</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size: 0.8rem;">{{ $issue->created_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@if ($issues->hasPages())
    <div class="pagination">{{ $issues->links() }}</div>
@endif

@endsection
