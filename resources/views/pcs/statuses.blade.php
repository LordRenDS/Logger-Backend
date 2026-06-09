@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.users.show', $pc->user_id) : route('dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block">&larr; Back to Devices</a>
        <h1 class="text-3xl font-bold text-gray-900">Status History: {{ $pc->name ?? $pc->unique_id }}</h1>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($statuses as $schedule)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $schedule->timestamp ? $schedule->timestamp->format('Y-m-d H:i:s') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php
                                $statusVal = strtolower($schedule->pcStatus?->status ?? 'unknown');
                            @endphp
                            @if($statusVal === 'on')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 uppercase">
                                    On
                                </span>
                            @elseif($statusVal === 'off')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 uppercase">
                                    Off
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 uppercase">
                                    {{ $schedule->pcStatus?->status ?? 'Unknown' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No status records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $statuses->links() }}
        </div>
    </div>
</div>
@endsection
