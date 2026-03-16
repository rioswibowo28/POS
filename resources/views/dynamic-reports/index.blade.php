@extends('layouts.app')

@section('title', 'User Defined Reports')
@section('header', 'User Defined Reports')

@section('content')
<div class="px-6 py-4">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="mb-4 flex justify-end">
        @if(auth()->user()->isAdmin())
        <a href="{{ route('dynamic-reports.create') }}" class="btn-primary">
            <i class="fas fa-plus mr-2"></i> Create Report Config
        </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($reports as $report)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $report->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2 h-10">{{ $report->description ?? 'No description provided.' }}</p>
                    <div class="text-xs text-gray-500 mb-4 bg-gray-50 p-2 rounded break-all">
                        <i class="fas fa-database mr-1"></i> VIEW: {{ $report->view_name }}
                    </div>
                </div>
                
                <div class="flex items-center justify-between border-t border-gray-100 pt-4 mt-2">
                    <a href="{{ route('dynamic-reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm flex items-center">
                        <i class="fas fa-eye mr-2"></i> View Report
                    </a>

                    @if(auth()->user()->isAdmin())
                    <div class="flex space-x-3">
                        <a href="{{ route('dynamic-reports.edit', $report->id) }}" class="text-blue-500 hover:text-blue-700">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('dynamic-reports.destroy', $report->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this report configuration?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" title="Delete Config">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                <i class="fas fa-chart-bar text-4xl mb-3 text-gray-300"></i>
                <p>No user defined reports configured yet.</p>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('dynamic-reports.create') }}" class="text-indigo-600 hover:underline mt-2 inline-block">Create your first report</a>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection
