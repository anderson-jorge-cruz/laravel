<?php

namespace App\Jobs;

use App\Brain\Integration\Processes\IntegrationProcess;
use App\Models\IntegrationConfig;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunIntegration implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public IntegrationConfig $integration
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        IntegrationProcess::dispatch([
            'integrationConfig' => $this->integration,
        ]);
    }
}
