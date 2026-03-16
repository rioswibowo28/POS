@extends('layouts.app')

@section('title', 'Edit User Defined Report')
@section('header', 'Edit User Defined Report')

@section('content')
<div class="px-6 py-4 max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('dynamic-reports.update', $dynamicReport->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Report Name</label>
                    <input type="text" name="name" class="input w-full" value="{{ $dynamicReport->name }}" required placeholder="e.g. Monthly Sales Recap">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Database View Name</label>
                    <input type="text" name="view_name" class="input w-full" value="{{ $dynamicReport->view_name }}" required placeholder="e.g. v_sales_monthly">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Description (Optional)</label>
                <textarea name="description" class="input w-full" rows="3">{{ $dynamicReport->description }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Date Column (Optional)</label>
                    <input type="text" name="date_column" class="input w-full" value="{{ $dynamicReport->date_column }}" placeholder="e.g. created_at">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to disable date filtering.</p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Allowed Roles</label>
                    <div class="flex items-center space-x-4 mt-2">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="allowed_roles[]" value="admin" class="form-checkbox h-5 w-5 text-indigo-600" {{ in_array('admin', $dynamicReport->allowed_roles ?? []) ? 'checked' : '' }}>
                            <span>Admin</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="allowed_roles[]" value="cashier" class="form-checkbox h-5 w-5 text-indigo-600" {{ in_array('cashier', $dynamicReport->allowed_roles ?? []) ? 'checked' : '' }}>
                            <span>Cashier</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="show_grand_total" value="1" class="form-checkbox h-5 w-5 text-indigo-600" {{ $dynamicReport->show_grand_total ? 'checked' : '' }}>
                    <span class="font-semibold text-gray-700">Show Grand Total</span>
                </label>
                <p class="text-xs text-gray-500 mt-1">When checked, a sum of all numeric values will be displayed at the bottom of the report.</p>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('dynamic-reports.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update Report</button>
            </div>
        </form>
    </div>
</div>
@endsection