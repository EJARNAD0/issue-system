<?php

namespace Tests\Feature;

use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable AI so tests are deterministic and don't hit the network.
        config(['services.openrouter.key' => null]);
    }

    public function test_browser_issue_list_renders_blade_ui(): void
    {
        Issue::create([
            'title'       => 'Payment gateway timeout',
            'description' => 'Customers see payment timeout errors during checkout.',
            'priority'    => 'high',
            'category'    => 'payment',
            'status'      => 'open',
        ]);

        $this->get('/issues')
            ->assertOk()
            ->assertViewIs('issues.index')
            ->assertViewHas('issues')
            ->assertSee('Payment gateway timeout');
    }

    public function test_api_issue_list_returns_json_with_data_and_meta(): void
    {
        Issue::create([
            'title'       => 'Login failure',
            'description' => 'Users cannot login with valid credentials.',
            'priority'    => 'medium',
            'category'    => 'auth',
            'status'      => 'open',
        ]);

        $this->getJson('/api/issues')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total', 'last_page']])
            ->assertJsonPath('data.0.title', 'Login failure');
    }

    public function test_json_create_request_validates_required_fields(): void
    {
        $this->postJson('/api/issues', ['priority' => 'urgent'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description', 'priority']);
    }

    public function test_create_generates_summary_action_and_escalation_flag(): void
    {
        $this->postJson('/api/issues', [
            'title'       => 'Payment gateway timeout',
            'description' => 'Customers are seeing payment timeout errors during checkout.',
            'priority'    => 'high',
            'category'    => 'payment',
            'status'      => 'open',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Issue created successfully.')
            ->assertJsonPath('data.summary', 'Payment-related issue detected')
            ->assertJsonPath('data.suggested_action', 'Check transaction logs and payment gateway status')
            ->assertJsonPath('data.is_escalated', true);

        $this->assertDatabaseHas('issues', [
            'title'            => 'Payment gateway timeout',
            'summary'          => 'Payment-related issue detected',
            'suggested_action' => 'Check transaction logs and payment gateway status',
            'is_escalated'     => true,
        ]);
    }

    public function test_api_issue_list_filters_by_status_priority_and_category(): void
    {
        Issue::create([
            'title'       => 'Matching payment issue',
            'description' => 'Payment error during checkout.',
            'priority'    => 'high',
            'category'    => 'payment',
            'status'      => 'open',
        ]);

        Issue::create([
            'title'       => 'Unrelated auth issue',
            'description' => 'Login error during authentication.',
            'priority'    => 'low',
            'category'    => 'auth',
            'status'      => 'closed',
        ]);

        $this->getJson('/api/issues?status=open&priority=high&category=pay')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Matching payment issue');
    }

    public function test_update_recalculates_escalation_when_issue_is_closed(): void
    {
        $issue = Issue::create([
            'title'        => 'Major system outage',
            'description'  => 'The application returns server errors for all requests.',
            'priority'     => 'high',
            'category'     => 'system',
            'status'       => 'open',
            'is_escalated' => true,
        ]);

        $this->putJson("/api/issues/{$issue->id}", ['status' => 'closed'])
            ->assertOk()
            ->assertJsonPath('message', 'Issue updated successfully.')
            ->assertJsonPath('data.status', 'closed')
            ->assertJsonPath('data.is_escalated', false);

        $this->assertDatabaseHas('issues', [
            'id'           => $issue->id,
            'status'       => 'closed',
            'is_escalated' => false,
        ]);
    }

    public function test_api_returns_404_for_missing_issue(): void
    {
        $this->getJson('/api/issues/99999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Issue not found.');
    }
}
