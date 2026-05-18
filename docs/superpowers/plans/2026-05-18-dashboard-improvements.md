# Dashboard Improvements Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Improve dashboard by adding admin user management, separating PC and activity views, and introducing filtering/pagination.

**Architecture:** We will separate responsibilities into `DashboardController` (main entry point), `UserController` (admin views a user's PCs and deletes users), and `PcActivityController` (viewing PC activities with filters). State management for pagination will use the Laravel Session.

**Tech Stack:** PHP 8.4, Laravel 12, Blade, Tailwind CSS.

---

### Task 1: Update Routes

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Add the new routes**

Add routes for the new controllers inside the `auth` middleware group in `routes/web.php`.

```php
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\PcActivityController;

// ... inside Route::middleware(['auth'])->group(function () {

    Route::post('/dashboard/per-page', [DashboardController::class, 'updatePerPage'])->name('dashboard.per-page');

    Route::middleware(['can:admin'])->group(function () {
        Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });

    Route::get('/pcs/{pc}/activities', [PcActivityController::class, 'index'])->name('pcs.activities');

// ...
```

*Note: Since we are adding `can:admin`, we'll need to define that Gate or simply use a middleware/inline check in the controllers. For simplicity, we will rely on controller logic if the Gate is not defined, or define it in AuthServiceProvider.* Let's define the Gate in `AppServiceProvider`.

- [ ] **Step 2: Define Admin Gate**

Modify `app/Providers/AppServiceProvider.php` to define the 'admin' Gate.

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Gate;
use App\Models\User;

// inside boot()
Gate::define('admin', function (User $user) {
    return $user->role === 'admin';
});
```

- [ ] **Step 3: Commit**

```bash
git add routes/web.php app/Providers/AppServiceProvider.php
git commit -m "feat: add routes and admin gate for dashboard improvements"
```

### Task 2: Update DashboardController

**Files:**
- Modify: `app/Http/Controllers/Web/DashboardController.php`

- [ ] **Step 1: Update DashboardController logic**

```php
// app/Http/Controllers/Web/DashboardController.php
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
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Web/DashboardController.php
git commit -m "feat: refactor DashboardController for pagination and separate PC views"
```

### Task 3: Create UserController

**Files:**
- Create: `app/Http/Controllers/Web/UserController.php`

- [ ] **Step 1: Write UserController**

```php
// app/Http/Controllers/Web/UserController.php
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
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Web/UserController.php
git commit -m "feat: create UserController for admin user management"
```

### Task 4: Create PcActivityController

**Files:**
- Create: `app/Http/Controllers/Web/PcActivityController.php`

- [ ] **Step 1: Write PcActivityController**

```php
// app/Http/Controllers/Web/PcActivityController.php
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
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Web/PcActivityController.php
git commit -m "feat: create PcActivityController with filtering and sorting"
```

### Task 5: Refactor Dashboard View

**Files:**
- Modify: `resources/views/dashboard.blade.php`
- Create: `resources/views/components/per-page-selector.blade.php`

- [ ] **Step 1: Create per_page selector component**

```html
<!-- resources/views/components/per-page-selector.blade.php -->
<form action="{{ route('dashboard.per-page') }}" method="POST" class="flex items-center space-x-2">
    @csrf
    <label for="per_page" class="text-sm font-medium text-gray-700">Per page:</label>
    <select name="per_page" id="per_page" onchange="this.form.submit()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
        @foreach([10, 15, 25, 50, 100] as $val)
            <option value="{{ $val }}" {{ session('per_page', 15) == $val ? 'selected' : '' }}>{{ $val }}</option>
        @endforeach
    </select>
</form>
```

- [ ] **Step 2: Update dashboard.blade.php**

```html
<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if(session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('status') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">{{ Auth::user()->role === 'admin' ? 'Admin Dashboard - All Users' : 'Your Devices' }}</h1>
        <x-per-page-selector />
    </div>

    @if(Auth::user()->role === 'admin')
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Devices</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->pcs_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View PCs</a>
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $users->links() }}
            </div>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PC Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unique ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Seen</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pcs as $pc)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $pc->name ?? 'Unnamed PC' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pc->unique_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pc->last_seen_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('pcs.activities', $pc) }}" class="text-indigo-600 hover:text-indigo-900">View Activities</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $pcs->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard.blade.php resources/views/components/per-page-selector.blade.php
git commit -m "feat: update dashboard view to show simplified lists and pagination"
```

### Task 6: Create User Show View (Admin)

**Files:**
- Create: `resources/views/users/show.blade.php`

- [ ] **Step 1: Write users.show view**

```html
<!-- resources/views/users/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block">&larr; Back to Dashboard</a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}'s Devices</h1>
        </div>
        <x-per-page-selector />
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PC Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unique ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Seen</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($pcs as $pc)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $pc->name ?? 'Unnamed PC' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pc->unique_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pc->last_seen_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('pcs.activities', $pc) }}" class="text-indigo-600 hover:text-indigo-900">View Activities</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $pcs->links() }}
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/users/
git commit -m "feat: add admin view for user devices"
```

### Task 7: Create PC Activities View

**Files:**
- Create: `resources/views/pcs/activities.blade.php`

- [ ] **Step 1: Write pcs.activities view**

```html
<!-- resources/views/pcs/activities.blade.php -->
@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ auth()->user()->role === 'admin' ? route('admin.users.show', $pc->user_id) : route('dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block">&larr; Back to Devices</a>
            <h1 class="text-3xl font-bold text-gray-900">Activities: {{ $pc->name ?? $pc->unique_id }}</h1>
        </div>
        <x-per-page-selector />
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 shadow sm:rounded-lg">
        <form method="GET" action="{{ route('pcs.activities', $pc) }}" class="flex gap-4">
            <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
            <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
            
            <div class="flex-1">
                <label for="process_name" class="block text-sm font-medium text-gray-700">Process Name</label>
                <input type="text" name="process_name" id="process_name" value="{{ request('process_name') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="e.g. chrome.exe">
            </div>
            <div class="flex-1">
                <label for="window_name" class="block text-sm font-medium text-gray-700">Window Title</label>
                <input type="text" name="window_name" id="window_name" value="{{ request('window_name') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="e.g. YouTube">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Filter</button>
                <a href="{{ route('pcs.activities', $pc) }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Reset</a>
            </div>
        </form>
    </div>

    @php
        function sortUrl($column) {
            $currentSort = request('sort_by');
            $currentDir = request('sort_dir', 'desc');
            $newDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
            
            return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newDir]);
        }
        function sortIcon($column) {
            if (request('sort_by') !== $column) return '';
            return request('sort_dir', 'desc') === 'asc' ? ' ↑' : ' ↓';
        }
    @endphp

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ sortUrl('process_start') }}" class="hover:text-gray-900">Start Time{{ sortIcon('process_start') }}</a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ sortUrl('process_name') }}" class="hover:text-gray-900">Process{{ sortIcon('process_name') }}</a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ sortUrl('window_name') }}" class="hover:text-gray-900">Window Title{{ sortIcon('window_name') }}</a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ sortUrl('duration') }}" class="hover:text-gray-900">Duration{{ sortIcon('duration') }}</a>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($activities as $process)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $process->process_start->format('Y-m-d H:i:s') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $process->process_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs" title="{{ $process->window_name }}">{{ Str::limit($process->window_name, 50) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ gmdate("H:i:s", $process->duration) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No activity recorded matching criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/pcs/
git commit -m "feat: add pc activities view with filtering and sorting"
```
