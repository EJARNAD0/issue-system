# Issue Intake and Smart Summary System

A support/operations tool for submitting, tracking, and triaging issues. Each issue is automatically summarized and given a suggested next action via an AI integration (OpenRouter) with a structured rules-based fallback when AI is unavailable.

---

## Tech Stack

| Layer | Choice | Reason |
|---|---|---|
| Framework | Laravel 13 (PHP 8.4) | Preferred stack per requirements; strong conventions for clean architecture |
| Database | MySQL | Relational structure suits structured issue records with clear fields and filter queries |
| Frontend | Blade templates | Minimal, server-rendered UI — no unnecessary frontend complexity |
| AI | OpenRouter API | OpenAI-compatible, model-agnostic; easy to swap models without code changes |
| HTTP Client | Laravel `Http` facade | Built-in, no extra packages needed |

---

## Requirements

- PHP 8.3+
- Composer
- MySQL 8+
- An OpenRouter API key (optional — fallback logic works without one)

---

## Setup

### 1. Clone and install dependencies

```bash
git clone <repository-url>
cd issue-system
composer install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=issue_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

To enable AI summarization, add your OpenRouter key:

```env
OPENROUTER_API_KEY=your_key_here
OPENAI_MODEL=gpt-4o-mini
```

Leave `OPENROUTER_API_KEY` blank to use the rules-based fallback instead.

### 3. Create the database

```sql
CREATE DATABASE issue_system;
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Seed sample data

```bash
php artisan db:seed
```

This creates 10 realistic issues across different priorities, statuses, and categories — ready to test filters, escalation, and AI summaries immediately.

### 6. Start the development server

```bash
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000)

---

## Usage

### Web UI

| URL | Description |
|---|---|
| `GET /issues` | List all issues with filters |
| `GET /issues/create` | Create issue form |
| `GET /issues/{id}` | View issue detail |
| `GET /issues/{id}/edit` | Edit issue form |

Filter by status, priority, or category using the query string:

```
/issues?status=open&priority=high
/issues?category=payment
```

### JSON API

API routes are available under `/api/issues` and return JSON. The shared controller also respects `Accept: application/json` for clients that request JSON explicitly.

#### List issues

```bash
curl http://localhost:8000/api/issues \
  -H "Accept: application/json"
```

Filter:

```bash
curl "http://localhost:8000/api/issues?status=open&priority=high" \
  -H "Accept: application/json"
```

#### Create an issue

Successful creates return `201 Created`.

```bash
curl -X POST http://localhost:8000/api/issues \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Payment gateway timeout",
    "description": "Checkout fails with a timeout error when processing card payments over $500.",
    "priority": "high",
    "category": "payment",
    "status": "open"
  }'
```

#### View an issue

```bash
curl http://localhost:8000/api/issues/1 \
  -H "Accept: application/json"
```

#### Update an issue

```bash
curl -X PUT http://localhost:8000/api/issues/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"status": "resolved"}'
```

#### Validation error response

```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."],
    "priority": ["Priority must be low, medium, or high."]
  }
}
```

The list endpoint returns paginated results (15 per page). Use `page` query parameter to navigate:

```bash
curl "http://localhost:8000/api/issues?page=2" -H "Accept: application/json"
```

Response includes a `meta` block with `current_page`, `per_page`, `total`, and `last_page`.

Indexes on `status`, `priority`, `category`, and `is_escalated` support the main filter and dashboard queries.

---

## Testing

```bash
php artisan test
```

The test suite covers:

| Test | What it verifies |
|---|---|
| Browser issue list renders Blade UI | `GET /issues` returns the index view |
| API issue list returns JSON with data and meta | Pagination envelope shape |
| JSON create request validates required fields | 422 + validation errors |
| Create generates summary, action, and escalation flag | Full enrichment pipeline via rules fallback |
| API list filters by status, priority, and category | Query filters work correctly |
| Update recalculates escalation when issue is closed | Escalation de-escalates on close |
| API returns 404 for missing issue | JSON 404 handler |

AI is disabled in tests (`OPENROUTER_API_KEY` set to `null`) so tests are deterministic and require no network access.

---

## Architecture

```
app/
├── Http/
│   └── Controllers/
│       └── IssueController.php   ← validates input, calls service, returns response
├── Models/
│   └── Issue.php                 ← Eloquent model, fillable fields, casts
└── Services/
    └── IssueService.php          ← all business logic lives here
```

### Separation of concerns

**Controller** — one responsibility: receive the HTTP request, validate it, hand off to the service, return the response. No business logic, no database queries.

**IssueService** — owns all business logic:
- Enriches issue data before saving (summary, suggested action, escalation flag)
- Decides which summarization path to use (AI or rules)
- Applies escalation rules
- Exposes `reEvaluateEscalation()` for the `php artisan issues:escalate` sweep command

**Model** — declares the schema contract (`$fillable`, `$casts`). Nothing else.

This structure means the service can be tested independently of HTTP, and the controller can be tested independently of business logic.

---

## AI Integration

### Flow

```
IssueService::generateSummary()
    │
    ├─ tryAiSummarize()          ← attempt OpenRouter API call
    │       │
    │       ├─ success           → return AI summary + action
    │       └─ fail / no key     → return null
    │
    └─ ruleBasedSummary()        ← fallback: keyword matching
            │
            ├─ "payment"         → "Payment-related issue detected"
            ├─ "error/exception" → "System error or exception encountered"
            ├─ "login/auth"      → "Authentication or access issue reported"
            ├─ "slow/timeout"    → "Performance or timeout issue reported"
            └─ (default)         → truncated description + "Investigate further"
```

### OpenRouter call

- Endpoint: `https://openrouter.ai/api/v1/chat/completions`
- Auth: `Authorization: Bearer {OPENROUTER_API_KEY}`
- Response format: `json_object` — returns `{ "summary": "...", "action": "..." }`
- Timeout: 8 seconds
- Any network failure, bad response, or missing key silently falls back to rules

This keeps issue creation reliable even when the AI provider is unavailable.

### Summary handling

All summaries (AI and rules) are capped at 160 characters and truncated at the nearest word boundary — never mid-word.

---

## Escalation Logic

An issue is flagged `is_escalated = true` when either condition is met:

| Rule | Condition |
|---|---|
| Priority-based | `priority = high` AND status is not resolved/closed |
| Time-based | Status is `open` or `in_progress` AND issue is older than 48 hours |

Escalation is re-evaluated on every update — closing an issue removes the escalation flag automatically.

---

## Sample Data

The seeder creates 10 issues that cover:

- All priority levels: low, medium, high
- All statuses: open, in_progress, resolved, closed
- Categories: payment, auth, system, performance, ui
- Mix of escalated and non-escalated issues

Run `php artisan db:seed` to load them. Useful for immediately testing filters, the escalated row highlighting in the UI, and AI/fallback summary generation.

---

## What I Would Improve With More Time

1. **Authentication** — issues should be owned by a user; add login so teams can manage their own tickets

2. **API tokens** — the JSON API currently relies on session/CSRF for web; a proper token-based auth layer (Laravel Sanctum) would make the API usable by external clients

3. **Scheduled escalation sweep** — `reEvaluateEscalation()` exists in the service but nothing calls it automatically; a daily console command would catch issues that pass the 48-hour threshold without being touched

4. **Test coverage** — `IssueService` contains all the logic that matters and is fully unit-testable without HTTP; adding feature tests for the API endpoints would catch regressions early

5. **Audit log** — a simple `issue_events` table tracking status changes and who made them would be valuable for a real support team

6. **AI prompt tuning** — the current prompt is functional but a few rounds of testing with real support data would produce significantly better summaries
