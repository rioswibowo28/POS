<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Order;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with(['openedBy', 'closedBy'])
            ->orderBy('shift_date', 'desc')
            ->orderBy('shift_number', 'desc')
            ->paginate(20);

        $currentShift = Shift::getCurrentShift();

        return view('shifts.index', compact('shifts', 'currentShift'));
    }

    public function show(Shift $shift)
    {
        $shift->load(['openedBy', 'closedBy', 'orders.items', 'payments']);

        $cashPayments = $shift->payments()
            ->where('method', 'cash')
            ->where('status', PaymentStatus::PAID)
            ->get();

        $nonCashPayments = $shift->payments()
            ->where('method', '!=', 'cash')
            ->where('status', PaymentStatus::PAID)
            ->get();

        $pendingOrders = $shift->orders()
            ->where('status', 'pending')
            ->with('items')
            ->get();

        return view('shifts.show', compact(
            'shift',
            'cashPayments',
            'nonCashPayments',
            'pendingOrders'
        ));
    }

    public function create()
    {
        // Check if there's already an open shift
        $currentShift = Shift::getCurrentShift();

        if ($currentShift) {
            return redirect()->route('shifts.index')
                ->with('error', 'There is already an open shift. Please close it before opening a new one.');
        }

        return view('shifts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check again if there's an open shift
        $currentShift = Shift::getCurrentShift();

        if ($currentShift) {
            return back()->with('error', 'There is already an open shift.');
        }

        $shift = Shift::create([
            'shift_number' => Shift::getShiftNumber(),
            'shift_date' => Shift::getShiftDate(),
            'opened_by' => Auth::id(),
            'opened_at' => now(),
            'opening_cash' => $request->opening_cash,
            'status' => 'open',
            'notes' => $request->notes,
        ]);

        // Transfer any orphaned pending orders (from closed shifts) to this new shift
        Order::where('status', 'pending')
            ->whereNull('shift_id')
            ->update(['shift_id' => $shift->id]);

        return redirect()->route('shifts.index')
            ->with('success', 'Shift opened successfully!');
    }

    public function closeForm(Shift $shift)
    {
        if ($shift->isClosed()) {
            return redirect()->route('shifts.show', $shift)
                ->with('error', 'This shift is already closed.');
        }

        // Calculate expected cash
        $cashPayments = $shift->payments()
            ->where('method', 'cash')
            ->where('status', PaymentStatus::PAID)
            ->sum('amount');

        $expectedCash = $shift->opening_cash + $cashPayments;

        // Get pending orders
        $pendingOrders = $shift->orders()
            ->where('status', 'pending')
            ->with('items')
            ->get();

        return view('shifts.close', compact('shift', 'expectedCash', 'pendingOrders'));
    }

    public function close(Request $request, Shift $shift)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($shift->isClosed()) {
            return back()->with('error', 'This shift is already closed.');
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $totalSales = $shift->payments()
                ->where('status', PaymentStatus::PAID)
                ->sum('amount');

            $totalOrders = $shift->orders()->count();

            $cashPayments = $shift->payments()
                ->where('method', 'cash')
                ->where('status', PaymentStatus::PAID)
                ->sum('amount');

            $expectedCash = $shift->opening_cash + $cashPayments;
            $cashDifference = $request->closing_cash - $expectedCash;

            // Update shift
            $shift->update([
                'closed_by' => Auth::id(),
                'closed_at' => now(),
                'closing_cash' => $request->closing_cash,
                'expected_cash' => $expectedCash,
                'cash_difference' => $cashDifference,
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'status' => 'closed',
                'notes' => $request->notes ? $shift->notes . "\n\nClosing Notes: " . $request->notes : $shift->notes,
            ]);

            // Transfer pending orders to next shift if exists, or leave for next opened shift
            $pendingOrders = $shift->orders()->where('status', 'pending')->get();
            
            if ($pendingOrders->count() > 0) {
                // These will be transferred when next shift opens
                foreach ($pendingOrders as $order) {
                    $order->update(['shift_id' => null]);
                }
            }

            DB::commit();

            return redirect()->route('shifts.show', $shift)
                ->with('success', 'Shift closed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error closing shift: ' . $e->getMessage());
            return back()->with('error', 'Failed to close shift: ' . $e->getMessage());
        }
    }

    public function print(Shift $shift)
    {
        $shift->load(['openedBy', 'closedBy', 'orders.items', 'payments']);

        $cashPayments = $shift->payments()
            ->where('method', 'cash')
            ->where('status', PaymentStatus::PAID)
            ->get();

        $nonCashPayments = $shift->payments()
            ->where('method', '!=', 'cash')
            ->where('status', PaymentStatus::PAID)
            ->get();

        $paymentsByMethod = $shift->payments()
            ->where('status', PaymentStatus::PAID)
            ->get()
            ->groupBy('method');

        return view('shifts.print', compact(
            'shift',
            'cashPayments',
            'nonCashPayments',
            'paymentsByMethod'
        ));
    }
}

