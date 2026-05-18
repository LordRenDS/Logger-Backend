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
        if (!function_exists('sortUrl')) {
            function sortUrl($column) {
                $currentSort = request('sort_by');
                $currentDir = request('sort_dir', 'desc');
                $newDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
                
                return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newDir]);
            }
        }
        if (!function_exists('sortIcon')) {
            function sortIcon($column) {
                if (request('sort_by') !== $column) return '';
                return request('sort_dir', 'desc') === 'asc' ? ' ↑' : ' ↓';
            }
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
