<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * User dashboard: list own PCs and their activities.
     */
    public function index()
    {
        $user = Auth::user();
        $pcs = $user->pcs()->with(['processes' => function ($query) {
            $query->latest('process_start')->limit(10);
        }])->get();

        return view('dashboard', compact('pcs'));
    }

    /**
     * Admin dashboard: list all users and their PCs.
     */
    public function adminIndex()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $users = User::with(['pcs' => function ($query) {
            $query->withCount('processes');
        }])->get();

        return view('admin.dashboard', compact('users'));
    }
}
