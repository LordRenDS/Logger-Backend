<?php

namespace App\Services;

use App\Models\Pc;
use App\Models\PcStatus;
use App\Models\Process;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class SyncService
{
    /**
     * Sync processes activity logs using batch insertion.
     *
     * @param Pc $pc
     * @param array $data
     * @return int
     */
    public function syncProcesses(Pc $pc, array $data): int
    {
        $records = array_map(function ($item) use ($pc) {
            return [
                'pc_id' => $pc->id,
                'process_start' => $item['process_start'],
                'process_name' => $item['process_name'],
                'window_name' => $item['window_name'],
                'duration' => $item['duration'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $data);

        return DB::transaction(function () use ($records) {
            Process::insert($records);
            return count($records);
        });
    }

    /**
     * Sync PC schedules (status history) using batch insertion and status mapping cache.
     *
     * @param Pc $pc
     * @param array $data
     * @return int
     */
    public function syncSchedules(Pc $pc, array $data): int
    {
        // Cache statuses to avoid N+1 queries
        $statuses = PcStatus::all()->pluck('id', 'status')->toArray();
        $records = [];

        foreach ($data as $item) {
            if (isset($statuses[$item['status']])) {
                $records[] = [
                    'pc_id' => $pc->id,
                    'timestamp' => $item['timestamp'],
                    'pc_status_id' => $statuses[$item['status']],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($records)) {
            return 0;
        }

        return DB::transaction(function () use ($records) {
            Schedule::insert($records);
            return count($records);
        });
    }
}
