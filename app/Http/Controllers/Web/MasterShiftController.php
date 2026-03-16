<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MasterShift;
use Illuminate\Http\Request;

class MasterShiftController extends Controller
{
    public function index()
    {
        $masterShifts = MasterShift::all();
        return view('master-shifts.index', compact('masterShifts'));
    }

    public function create()
    {
        return view('master-shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        MasterShift::create($validated);

        return redirect()->route('master-shifts.index')->with('success', 'Master Shift created successfully.');
    }

    public function edit(MasterShift $masterShift)
    {
        return view('master-shifts.edit', compact('masterShift'));
    }

    public function update(Request $request, MasterShift $masterShift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $masterShift->update($validated);

        return redirect()->route('master-shifts.index')->with('success', 'Master Shift updated successfully.');
    }

    public function destroy(MasterShift $masterShift)
    {
        $masterShift->delete();
        return redirect()->route('master-shifts.index')->with('success', 'Master Shift deleted successfully.');
    }
}
