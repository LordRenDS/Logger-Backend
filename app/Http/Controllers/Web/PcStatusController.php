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
}
