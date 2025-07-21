<?php

declare(strict_types=1);

namespace App\Brain\Integration\Processes;

use App\Brain\Integration\Tasks\ExportOrdersTask;
use App\Brain\Integration\Tasks\FetchReleasedCollectOrdersTask;
use Brain\Process;

class IntegrationProcess extends Process
{
    protected array $tasks = [
        ExportOrdersTask::class,
        FetchReleasedCollectOrdersTask::class,
    ];
}
