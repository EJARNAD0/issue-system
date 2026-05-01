<?php

namespace Tests\Unit;

use App\Services\IssueService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IssueServiceTest extends TestCase
{
    public function test_rule_based_summary_detects_payment_issues(): void
    {
        $result = $this->callServiceMethod('ruleBasedSummary', [
            'Customer reports a payment timeout at checkout.',
        ]);

        $this->assertSame('Payment-related issue detected', $result['summary']);
        $this->assertSame('Check transaction logs and payment gateway status', $result['suggested_action']);
    }

    public function test_rule_based_summary_detects_error_or_exception_issues(): void
    {
        $result = $this->callServiceMethod('ruleBasedSummary', [
            'The report page throws an exception for every request.',
        ]);

        $this->assertSame('System error or exception encountered', $result['summary']);
        $this->assertSame('Check application logs and reproduce the issue in staging', $result['suggested_action']);
    }

    public function test_rule_based_summary_detects_login_or_auth_issues(): void
    {
        $result = $this->callServiceMethod('ruleBasedSummary', [
            'Users cannot login through the SSO auth flow.',
        ]);

        $this->assertSame('Authentication or access issue reported', $result['summary']);
        $this->assertSame('Verify credentials, session config, and auth middleware', $result['suggested_action']);
    }

    public function test_rule_based_summary_detects_slow_or_timeout_issues(): void
    {
        $result = $this->callServiceMethod('ruleBasedSummary', [
            'Dashboard charts are slow and sometimes timeout.',
        ]);

        $this->assertSame('Performance or timeout issue reported', $result['summary']);
        $this->assertSame('Check database queries, cache hits, and server load', $result['suggested_action']);
    }

    public function test_high_priority_active_issue_escalates(): void
    {
        $result = $this->callServiceMethod('shouldEscalate', [[
            'priority' => 'high',
            'status' => 'open',
        ]]);

        $this->assertTrue($result);
    }

    public function test_closed_or_resolved_issue_does_not_escalate(): void
    {
        $closed = $this->callServiceMethod('shouldEscalate', [[
            'priority' => 'high',
            'status' => 'closed',
            'created_at' => now()->subHours(72),
        ]]);

        $resolved = $this->callServiceMethod('shouldEscalate', [[
            'priority' => 'high',
            'status' => 'resolved',
            'created_at' => now()->subHours(72),
        ]]);

        $this->assertFalse($closed);
        $this->assertFalse($resolved);
    }

    public function test_open_or_in_progress_issue_older_than_48_hours_escalates(): void
    {
        $open = $this->callServiceMethod('shouldEscalate', [[
            'priority' => 'medium',
            'status' => 'open',
            'created_at' => now()->subHours(49),
        ]]);

        $inProgress = $this->callServiceMethod('shouldEscalate', [[
            'priority' => 'low',
            'status' => 'in_progress',
            'created_at' => now()->subHours(49),
        ]]);

        $this->assertTrue($open);
        $this->assertTrue($inProgress);
    }

    public function test_ai_failure_falls_back_to_rule_based_summary(): void
    {
        config(['services.openrouter.key' => 'test-key']);

        Http::fake([
            'openrouter.ai/*' => Http::response(['error' => 'unavailable'], 500),
        ]);

        $result = $this->callServiceMethod('generateSummary', [
            'Payment processing returns an error during checkout.',
        ]);

        $this->assertSame('Payment-related issue detected', $result['summary']);
        $this->assertSame('Check transaction logs and payment gateway status', $result['suggested_action']);
        Http::assertSentCount(1);
    }

    private function callServiceMethod(string $method, array $arguments): mixed
    {
        $reflection = new \ReflectionMethod(IssueService::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(new IssueService(), $arguments);
    }
}
