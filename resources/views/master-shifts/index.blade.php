@extends('layouts.app')

@section('title', 'Master Shifts')
@section('header', 'Master Shift Management')

@section('content')
<div>
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="card bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">All Master Shifts</h2>
                <p class="text-gray-600 text-sm">Manage shift schedules</p>
            </div>
            <a href="{{ route('master-shifts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> Add Master Shift
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="py-3 px-4 text-sm font-semibold text-gray-600">ID</th>
                        <th class="py-3 px-4 text-sm font-semibold text-gray-600">Name</th>
                        <th class="py-3 px-4 text-sm font-semibold text-gray-600">Start Time</th>
                        <th class="py-3 px-4 text-sm font-semibold text-gray-600">End Time</th>
                        <th class="py-3 px-4 text-sm font-semibold text-gray-600">Status</th>
                        <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($masterShifts as $shift)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 text-gray-700">{{ $shift->id }}</td>
                        <td class="py-3 px-4 font-medium text-gray-900">{{ $shift->name }}</td>
                        <td class="py-3 px-4 text-gray-700">{{ substr($shift->start_time, 0, 5) }}</td>
                        <td class="py-3 px-4 text-gray-700">{{ substr($shift->end_time, 0, 5) }}</td>
                        <td class="py-3 px-4">
                            @if($shift->is_active)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('master-shifts.edit', $shift) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('master-shifts.destroy', $shift) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this shift?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-500">No master shifts configured.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
