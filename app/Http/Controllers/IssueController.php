<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Services\IssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function __construct(private IssueService $service) {}

    public function index(Request $request)
    {
        $query = Issue::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->category . '%');
        }

        $issues = $query->latest()->paginate(15)->withQueryString();

        if ($this->expectsJson($request)) {
            return $this->paginatedResponse($issues);
        }

        $stats = [
            'total'     => Issue::count(),
            'open'      => Issue::where('status', 'open')->count(),
            'escalated' => Issue::where('is_escalated', true)->count(),
            'resolved'  => Issue::whereIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('issues.index', compact('issues', 'stats'));
    }

    public function create()
    {
        return view('issues.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10',
            'priority'    => 'required|in:low,medium,high',
            'category'    => 'nullable|string|max:100',
            'status'      => 'nullable|in:open,in_progress,resolved,closed',
        ], [
            'title.required'   => 'A title is required.',
            'title.min'        => 'Title must be at least 3 characters.',
            'description.required' => 'A description is required.',
            'description.min'  => 'Please provide a meaningful description (at least 10 characters).',
            'priority.required' => 'Priority is required.',
            'priority.in'      => 'Priority must be low, medium, or high.',
            'status.in'        => 'Status must be one of: open, in_progress, resolved, closed.',
        ]);

        $validated['status'] = $validated['status'] ?? 'open';

        $issue = $this->service->create($validated);

        if ($this->expectsJson($request)) {
            return $this->jsonResponse($issue, 'Issue created successfully.', 201);
        }

        return redirect()->route('issues.show', $issue)
            ->with('success', 'Issue created successfully.');
    }

    public function show(Request $request, Issue $issue)
    {
        if ($this->expectsJson($request)) {
            return $this->jsonResponse($issue);
        }

        return view('issues.show', compact('issue'));
    }

    public function edit(Issue $issue)
    {
        return view('issues.edit', compact('issue'));
    }

    public function update(Request $request, Issue $issue)
    {
        $validated = $request->validate([
            'title'       => 'sometimes|required|string|min:3|max:255',
            'description' => 'sometimes|required|string|min:10',
            'priority'    => 'sometimes|required|in:low,medium,high',
            'category'    => 'nullable|string|max:100',
            'status'      => 'nullable|in:open,in_progress,resolved,closed',
        ], [
            'title.min'        => 'Title must be at least 3 characters.',
            'description.min'  => 'Please provide a meaningful description (at least 10 characters).',
            'priority.in'      => 'Priority must be low, medium, or high.',
            'status.in'        => 'Status must be one of: open, in_progress, resolved, closed.',
        ]);

        $issue = $this->service->update($issue, $validated);

        if ($this->expectsJson($request)) {
            return $this->jsonResponse($issue, 'Issue updated successfully.');
        }

        return redirect()->route('issues.show', $issue)
            ->with('success', 'Issue updated successfully.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function expectsJson(Request $request): bool
    {
        return $request->is('api/*') || $request->wantsJson();
    }

    private function jsonResponse(mixed $data, string $message = '', int $status = 200): JsonResponse
    {
        $body = ['data' => $data];

        if ($message !== '') {
            $body['message'] = $message;
        }

        return response()->json($body, $status);
    }

    private function paginatedResponse($paginator): JsonResponse
    {
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }
}
