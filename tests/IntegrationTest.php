<?php

namespace Spatie\ServerMonitor\Test;

use Spatie\ServerMonitor\Models\Check;
use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class IntegrationTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Host */
    protected $host;

    public function setUp()
    {
        parent::setUp();

        $this->host = $this->createHost('localhost', 65000, ['diskspace']);
    }

    /** @test */
    public function it_can_run_a_successful_check()
    {
        $this->letSshServerRespondWithDiskspaceUsagePercentage(40);

        Artisan::call('server-monitor:run-checks');

        $check = Check::where('host_id', $this->host->id)->where('type', 'diskspace')->first();

        $this->assertEquals('usage at 40%', $check->message);
        $this->assertEquals(CheckStatus::SUCCESS, $check->status);
    }

    /** @test */
    public function it_can_run_a_check_that_issues_a_warning()
    {
        $this->letSshServerRespondWithDiskspaceUsagePercentage(85);

        Artisan::call('server-monitor:run-checks');

        $check = Check::where('host_id', $this->host->id)->where('type', 'diskspace')->first();

        $this->assertEquals('usage at 85%', $check->message);
        $this->assertEquals(CheckStatus::WARNING, $check->status);
    }

    /** @test */
    public function it_can_run_a_failing_check()
    {
        $this->letSshServerRespondWithDiskspaceUsagePercentage(95);

        Artisan::call('server-monitor:run-checks');

        $check = Check::where('host_id', $this->host->id)->where('type', 'diskspace')->first();

        $this->assertEquals('usage at 95%', $check->message);
        $this->assertEquals(CheckStatus::FAILED, $check->status);
    }
}
