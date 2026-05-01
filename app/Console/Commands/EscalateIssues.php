<?php

namespace App\Console\Commands;

use App\Models\Issue;
use App\Services\IssueService;
use Illuminate\Console\Command;

class EscalateIssues extends Command
{
    protected $signature = 'issues:escalate';

    protected $description = 'Re-evaluate escalation flags for active issues.';

    public function handle(IssueService $service): int
    {
        $processed = 0;

        Issue::whereIn('status', ['open', 'in_progress'])
            ->each(function (Issue $issue) use ($service, &$processed): void {
                $service->reEvaluateEscalation($issue);
                $processed++;
            });

        $this->info("Processed {$processed} active issue(s).");

        return self::SUCCESS;
    }
}
