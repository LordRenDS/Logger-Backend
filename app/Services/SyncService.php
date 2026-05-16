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
     * Sync processes activity logs.
     *
     * @param Pc $pc
     * @param array $data
     * @return int
     */
    public function syncProcesses(Pc $pc, array $data): int
    {
        return DB::transaction(function () use ($pc, $data) {
            $count = 0;
            foreach ($data as $item) {
                Process::create([
                    'pc_id' => $pc->id,
                    'process_start' => $item['process_start'],
                    'process_name' => $item['process_name'],
                    'window_name' => $item['window_name'],
                    'duration' => $item['duration'],
                ]);
                $count++;
            }
            return $count;
        });
    }

    /**
     * Sync PC schedules (status history).
     *
     * @param Pc $pc
     * @param array $data
     * @return int
     */
    public function syncSchedules(Pc $pc, array $data): int
    {
        return DB::transaction(function () use ($pc, $data) {
            $count = 0;
            foreach ($data as $item) {
                $status = PcStatus::where('status', $item['status'])->first();
                if ($status) {
                    Schedule::create([
                        'pc_id' => $pc->id,
                        'timestamp' => $item['timestamp'],
                        'pc_status_id' => $status->id,
                    ]);
                    $count++;
                }
            }
            return $count;
        });
    }
}
