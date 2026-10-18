<?php

namespace App\Console\Commands;

use App\Support\RecordsAudit;
use Illuminate\Console\Command;

class AuditRecords extends Command
{
    protected $signature = 'records:audit {--limit=50 : Rows shown per section}';

    protected $description = 'Report data-quality problems in subjects, students, enrollments, grades and documents (read-only)';

    public function handle(RecordsAudit $audit): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $total = 0;
        $fixable = 0;

        foreach ($audit->findings() as $finding) {
            $count = $finding['rows']->count();
            $total += $count;

            if ($count === 0) {
                $this->line("<fg=green>✓</> {$finding['title']}: none");
                continue;
            }

            if ($finding['fixable']) {
                $fixable += $count;
            }

            $this->newLine();
            $this->line("<fg=yellow>!</> <options=bold>{$finding['title']}: {$count}</>" . ($finding['fixable'] ? ' <fg=cyan>(records:fix-safe can fix the clear ones)</>' : ' <fg=gray>(fix by hand)</>'));
            $this->table($finding['columns'], $finding['rows']->take($limit)->values()->all());
            if ($count > $limit) {
                $this->line('  … and ' . ($count - $limit) . ' more (use --limit=' . $count . ' to see all)');
            }
        }

        $this->newLine();
        $this->info("Problems found: {$total}" . ($fixable ? " ({$fixable} in sections that records:fix-safe can help with)" : ''));

        return self::SUCCESS;
    }
}
