<?php

namespace Database\Seeders;

use App\Models\Issue;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IssueSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $issues = [
            // --- Escalated: high priority + open ---
            [
                'title'            => 'Payment gateway timeout on checkout',
                'description'      => 'Customers are experiencing payment timeouts during checkout. The error appears intermittently and affects all card types. Started after the payment provider maintenance window last night.',
                'priority'         => 'high',
                'category'         => 'payment',
                'status'           => 'open',
                'summary'          => 'Payment-related issue detected',
                'suggested_action' => 'Check transaction logs and payment gateway status',
                'is_escalated'     => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],

            // --- Escalated: high priority + in_progress ---
            [
                'title'            => 'Login failures for enterprise accounts',
                'description'      => 'Enterprise users with SSO enabled cannot authenticate. Auth service returns a 401 even with valid credentials. Affects roughly 30 accounts.',
                'priority'         => 'high',
                'category'         => 'auth',
                'status'           => 'in_progress',
                'summary'          => 'Authentication or access issue reported',
                'suggested_action' => 'Verify credentials, session config, and auth middleware',
                'is_escalated'     => true,
                'created_at'       => $now->copy()->subHours(5),
                'updated_at'       => $now->copy()->subHours(2),
            ],

            // --- Escalated: time-based (72 hours old, medium priority, still open) ---
            [
                'title'            => 'API returning 500 errors on report endpoint',
                'description'      => 'The /api/reports endpoint throws a 500 internal server error for all requests. The error log shows a database query exception related to a missing index.',
                'priority'         => 'medium',
                'category'         => 'system',
                'status'           => 'open',
                'summary'          => 'System error or exception encountered',
                'suggested_action' => 'Check application logs and reproduce the issue in staging',
                'is_escalated'     => true,
                'created_at'       => $now->copy()->subHours(72),
                'updated_at'       => $now->copy()->subHours(72),
            ],

            // --- Escalated: time-based (96 hours old, low priority, still open) ---
            [
                'title'            => 'Email notifications not being delivered',
                'description'      => 'Users are not receiving system notification emails for new assignments. The mail queue shows jobs completing successfully but no emails arrive. Issue is not affecting password resets.',
                'priority'         => 'low',
                'category'         => 'system',
                'status'           => 'open',
                'summary'          => 'Email delivery failure for system notifications',
                'suggested_action' => 'Review the full description and investigate further',
                'is_escalated'     => true,
                'created_at'       => $now->copy()->subHours(96),
                'updated_at'       => $now->copy()->subHours(96),
            ],

            // --- Not escalated: medium priority, in_progress, recent ---
            [
                'title'            => 'Dashboard charts loading slowly',
                'description'      => 'The analytics dashboard takes over 12 seconds to load charts for date ranges longer than 30 days. The issue appears to be a slow SQL query with no index on the date column.',
                'priority'         => 'medium',
                'category'         => 'performance',
                'status'           => 'in_progress',
                'summary'          => 'Performance or timeout issue reported',
                'suggested_action' => 'Check database queries, cache hits, and server load',
                'is_escalated'     => false,
                'created_at'       => $now->copy()->subHours(10),
                'updated_at'       => $now->copy()->subHours(1),
            ],

            // --- Not escalated: low priority, open ---
            [
                'title'            => 'Sidebar collapses unexpectedly on mobile',
                'description'      => 'On mobile devices in landscape mode, the sidebar navigation collapses automatically when the user begins scrolling. The issue is cosmetic but has been reported by multiple users.',
                'priority'         => 'low',
                'category'         => 'ui',
                'status'           => 'open',
                'summary'          => 'The sidebar navigation collapses automatically when the user begins scrolling.',
                'suggested_action' => 'Review the full description and investigate further',
                'is_escalated'     => false,
                'created_at'       => $now->copy()->subHours(6),
                'updated_at'       => $now->copy()->subHours(6),
            ],

            // --- Not escalated: high priority, resolved ---
            [
                'title'            => 'Database connection pool exhausted under load',
                'description'      => 'During peak traffic the application threw "too many connections" errors. The database connection pool limit was set too low for the current user volume.',
                'priority'         => 'high',
                'category'         => 'system',
                'status'           => 'resolved',
                'summary'          => 'System error or exception encountered',
                'suggested_action' => 'Check application logs and reproduce the issue in staging',
                'is_escalated'     => false,
                'created_at'       => $now->copy()->subDays(3),
                'updated_at'       => $now->copy()->subDay(),
            ],

            // --- Not escalated: medium priority, resolved ---
            [
                'title'            => 'Duplicate charges appearing on checkout',
                'description'      => 'A subset of customers were charged twice for a single order. The issue was traced to a double form submission caused by a missing disabled state on the submit button.',
                'priority'         => 'medium',
                'category'         => 'payment',
                'status'           => 'resolved',
                'summary'          => 'Payment-related issue detected',
                'suggested_action' => 'Check transaction logs and payment gateway status',
                'is_escalated'     => false,
                'created_at'       => $now->copy()->subDays(5),
                'updated_at'       => $now->copy()->subDays(2),
            ],

            // --- Not escalated: low priority, closed ---
            [
                'title'            => 'Password reset email arrives with a delay',
                'description'      => 'Users report that password reset emails take 5–10 minutes to arrive. The mail queue was processing slowly due to a misconfigured worker count.',
                'priority'         => 'low',
                'category'         => 'auth',
                'status'           => 'closed',
                'summary'          => 'Authentication or access issue reported',
                'suggested_action' => 'Verify credentials, session config, and auth middleware',
                'is_escalated'     => false,
                'created_at'       => $now->copy()->subDays(7),
                'updated_at'       => $now->copy()->subDays(6),
            ],

            // --- Not escalated: medium priority, closed ---
            [
                'title'            => 'Export to CSV fails for large datasets',
                'description'      => 'Attempting to export more than 10,000 records as CSV results in a timeout error. The feature works correctly for smaller datasets. A chunked export approach has been implemented as a fix.',
                'priority'         => 'medium',
                'category'         => 'performance',
                'status'           => 'closed',
                'summary'          => 'Performance or timeout issue reported',
                'suggested_action' => 'Check database queries, cache hits, and server load',
                'is_escalated'     => false,
                'created_at'       => $now->copy()->subDays(10),
                'updated_at'       => $now->copy()->subDays(9),
            ],
        ];

        foreach ($issues as $issue) {
            Issue::create($issue);
        }
    }
}
