<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
            $query->where('process_name', 'ilike', '%' . $request->process_name . '%');
        }

        if ($request->filled('window_name')) {
            $query->where('window_name', 'ilike', '%' . $request->window_name . '%');
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
}
