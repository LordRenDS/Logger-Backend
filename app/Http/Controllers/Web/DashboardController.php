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
     * Dashboard: list own PCs or all users based on role.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $users = User::with(['pcs' => function ($query) {
                $query->withCount('processes');
            }])->get();

            return view('dashboard', compact('users'));
        }

        $pcs = $user->pcs()->with(['processes' => function ($query) {
            $query->latest('process_start')->limit(10);
        }])->get();

        return view('dashboard', compact('pcs'));
    }
}
