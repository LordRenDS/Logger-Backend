<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pc;
use Illuminate\Http\Request;

class PcActivityController extends Controller
{
    public function index(Request $request, Pc $pc)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $pc->user_id) {
            abort(403, 'Unauthorized access to this PC.');
        }

        $perPage = session('per_page', 15);

        $query = $pc->processes();

        if ($request->filled('process_name')) {
            $query->where('process_name', 'ilike', '%'.$request->process_name.'%');
        }

        if ($request->filled('window_name')) {
            $query->where('window_name', 'ilike', '%'.$request->window_name.'%');
        }

        $sortBy = $request->get('sort_by', 'process_start');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSorts = ['process_start', 'process_name', 'window_name', 'duration'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $activities = $query->paginate($perPage)->withQueryString();

        return view('pcs.activities', compact('pc', 'activities', 'sortBy', 'sortDir'));
    }
    public function export(Request $request, Pc $pc)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $pc->user_id) {
            abort(403, 'Unauthorized access to this PC.');
        }

        $fileName = 'pc_' . $pc->id . '_activities_' . now()->format('Y_m_d_His') . '.tsv';

        $headers = [
            'Content-Type' => 'text/tab-separated-values; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->stream(function () use ($pc) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Start Time', 'Process', 'Window Title', 'Duration (HH:MM:SS)'], "\t");

            foreach ($pc->processes()->cursor() as $process) {
                fputcsv($handle, [
                    $process->process_start->format('Y-m-d H:i:s'),
                    $process->process_name,
                    $process->window_name,
                    gmdate("H:i:s", $process->duration)
                ], "\t");
            }
            fclose($handle);
        }, 200, $headers);
    }
}
