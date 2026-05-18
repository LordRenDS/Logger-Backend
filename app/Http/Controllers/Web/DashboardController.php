<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = session('per_page', 15);

        if ($user->role === 'admin') {
            $users = User::withCount('pcs')->paginate($perPage);
            return view('dashboard', compact('users'));
        }

        $pcs = $user->pcs()->paginate($perPage);
        return view('dashboard', compact('pcs'));
    }

    public function updatePerPage(Request $request)
    {
        $request->validate(['per_page' => 'required|integer|min:1|max:100']);
        session(['per_page' => $request->per_page]);
        return back();
    }
}
