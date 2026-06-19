<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pc;
use Illuminate\Http\Request;

class PcStatusController extends Controller
{
    public function index(Request $request, Pc $pc)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $pc->user_id) {
            abort(403, 'Unauthorized access to this PC.');
        }

        $perPage = session('per_page', 15);

        $statuses = $pc->schedules()
            ->with('pcStatus')
            ->orderBy('timestamp', 'desc')
            ->paginate($perPage);

        return view('pcs.statuses', compact('pc', 'statuses'));
    }
    public function export(Request $request, Pc $pc)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $pc->user_id) {
            abort(403, 'Unauthorized access to this PC.');
        }

        $fileName = 'pc_' . $pc->id . '_statuses_' . now()->format('Y_m_d_His') . '.tsv';

        $headers = [
            'Content-Type' => 'text/tab-separated-values; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->stream(function () use ($pc) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Timestamp', 'Status'], "\t");

            foreach ($pc->schedules()->with('pcStatus')->cursor() as $schedule) {
                fputcsv($handle, [
                    $schedule->timestamp ? $schedule->timestamp->format('Y-m-d H:i:s') : 'N/A',
                    $schedule->pcStatus?->status ?? 'Unknown'
                ], "\t");
            }
            fclose($handle);
        }, 200, $headers);
    }
}
