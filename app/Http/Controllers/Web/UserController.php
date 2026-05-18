<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        $perPage = session('per_page', 15);
        $pcs = $user->pcs()->paginate($perPage);
        
        return view('users.show', compact('user', 'pcs'));
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return redirect()->route('dashboard')->with('status', 'User deleted successfully.');
    }
}
