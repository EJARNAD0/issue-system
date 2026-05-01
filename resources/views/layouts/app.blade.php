<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Issue Tracker') — Issue Tracker</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:       #6366f1;
            --primary-dark:  #4f46e5;
            --primary-light: #eef2ff;
            --danger:        #ef4444;
            --danger-light:  #fff1f2;
            --success:       #10b981;
            --bg:            #f1f5f9;
            --surface:       #ffffff;
            --border:        #e2e8f0;
            --border-dark:   #cbd5e1;
            --text:          #0f172a;
            --text-secondary:#475569;
            --text-muted:    #94a3b8;
            --radius:        10px;
            --radius-sm:     6px;
            --shadow-sm:     0 1px 3px rgba(0,0,0,0.07), 0 1px 2px rgba(0,0,0,0.05);
            --shadow:        0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -1px rgba(0,0,0,0.04);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            font-size: 15px;
        }

        /* ── Navigation ─────────────────────────────── */
        nav {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            height: 60px;
            gap: 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            text-decoration: none;
            margin-right: auto;
        }

        .nav-brand .nav-icon {
            width: 30px;
            height: 30px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.9rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.45rem 0.85rem;
            border-radius: var(--radius-sm);
            transition: background 0.15s, color 0.15s;
        }

        .nav-links a:hover { background: var(--bg); color: var(--text); }

        .nav-links .btn-nav {
            background: var(--primary);
            color: #fff;
            margin-left: 0.5rem;
        }

        .nav-links .btn-nav:hover { background: var(--primary-dark); color: #fff; }

        /* ── Layout ──────────────────────────────────── */
        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* ── Alerts ──────────────────────────────────── */
        .alert {
            padding: 0.875rem 1.1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert ul { margin-left: 1.1rem; }

        /* ── Cards ───────────────────────────────────── */
        .card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }

        /* ── Headings ────────────────────────────────── */
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }

        h2 {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 1rem;
        }

        /* ── Buttons ─────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1.1rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s;
            line-height: 1;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }

        .btn-secondary {
            background: var(--surface);
            color: var(--text-secondary);
            border-color: var(--border-dark);
        }

        .btn-secondary:hover { background: var(--bg); color: var(--text); }

        .btn-danger { background: var(--danger); color: #fff; border-color: var(--danger); }
        .btn-danger:hover { opacity: 0.88; }

        .btn-sm { padding: 0.3rem 0.75rem; font-size: 0.8rem; }

        /* ── Badges ──────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .badge-low        { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .badge-medium     { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .badge-high       { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-open       { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-in_progress{ background: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe; }
        .badge-resolved   { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .badge-closed     { background: #f8fafc; color: #475569; border: 1px solid var(--border); }
        .badge-escalated  { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* ── Forms ───────────────────────────────────── */
        .field { margin-bottom: 1.25rem; }

        .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .field label .req { color: var(--danger); margin-left: 2px; }

        .field input[type="text"],
        .field textarea,
        .field select {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            background: var(--surface);
            color: var(--text);
            transition: border-color 0.15s, box-shadow 0.15s;
            appearance: none;
        }

        .field input:focus,
        .field textarea:focus,
        .field select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
        }

        .field textarea { resize: vertical; min-height: 130px; }

        .error-msg {
            color: var(--danger);
            font-size: 0.78rem;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* ── Table ───────────────────────────────────── */
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }

        thead th {
            background: var(--bg);
            padding: 0.65rem 1rem;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border);
        }

        tbody td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #fafbfc; }

        /* ── Filters bar ─────────────────────────────── */
        .filter-bar {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group { display: flex; flex-direction: column; gap: 0.3rem; }

        .filter-group label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .filter-group select,
        .filter-group input[type="text"] {
            padding: 0.5rem 0.8rem;
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            background: var(--surface);
            color: var(--text);
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }

        /* ── Stats strip ─────────────────────────────── */
        .stats-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            box-shadow: var(--shadow-sm);
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
            margin-bottom: 0.3rem;
        }

        .stat-card .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-card.stat-danger .stat-value { color: var(--danger); }
        .stat-card.stat-primary .stat-value { color: var(--primary); }
        .stat-card.stat-success .stat-value { color: var(--success); }

        /* ── Detail rows ─────────────────────────────── */
        .detail-row { margin-bottom: 1rem; }
        .detail-row:last-child { margin-bottom: 0; }

        .detail-row .label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .detail-row .value { font-size: 0.9rem; color: var(--text); }

        /* ── Page header ─────────────────────────────── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .page-header h1 { margin-bottom: 0; }

        /* ── Back link ───────────────────────────────── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--text-muted);
            font-size: 0.825rem;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 0.75rem;
            transition: color 0.15s;
        }

        .back-link:hover { color: var(--text); }

        /* ── Pagination ──────────────────────────────── */
        .pagination {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.35rem;
            align-items: center;
            font-size: 0.825rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.4rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-secondary);
            background: var(--surface);
        }

        .pagination a:hover { background: var(--bg); }

        .pagination .active span {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        /* ── Misc ────────────────────────────────────── */
        .text-muted { color: var(--text-muted); }

        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 1.25rem 0;
        }
    </style>
</head>
<body>

<nav>
    <a href="{{ route('issues.index') }}" class="nav-brand">
        <div class="nav-icon">&#9741;</div>
        Issue Tracker
    </a>
    <div class="nav-links">
        <a href="{{ route('issues.index') }}">All Issues</a>
        <a href="{{ route('issues.create') }}" class="btn-nav">+ New Issue</a>
    </div>
</nav>

<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            &#10003; {{ session('success') }}
        </div>
    @endif

    @if (!empty($errors) && $errors->any())
        <div class="alert alert-error">
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin-top: 0.35rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @yield('content')
</div>

</body>
</html>
