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
        $shifts = Shift::with(['openedBy', 'closedBy', 'masterShift'])
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

        $includeTempOrders = \App\Models\Setting::get('include_temp_orders_in_shift_close', '0') == '1';

        if ($includeTempOrders) {
            $tempOrdersCash = \App\Models\TempOrder::where('shift_id', $shift->id)
                ->where('payment_method', 'cash')
                ->get()
                ->map(function ($order) use ($shift) {
                    $payment = new \App\Models\Payment();
                    $payment->amount = $order->payment_amount ?: $order->total;
                    $payment->method = 'cash';
                    $payment->status = PaymentStatus::PAID;
                    $payment->shift_id = $shift->id;
                    $payment->created_at = $order->created_at;
                    return $payment;
                });

            $cashPayments = $cashPayments->concat($tempOrdersCash);

            $tempOrdersNonCash = \App\Models\TempOrder::where('shift_id', $shift->id)
                ->where('payment_method', '!=', 'cash')
                ->whereNotNull('payment_method')
                ->get()
                ->map(function ($order) use ($shift) {
                    $payment = new \App\Models\Payment();
                    $payment->amount = $order->payment_amount ?: $order->total;
                    $payment->method = $order->payment_method;
                    $payment->status = PaymentStatus::PAID;
                    $payment->shift_id = $shift->id;
                    $payment->created_at = $order->created_at;
                    return $payment;
                });

            $nonCashPayments = $nonCashPayments->concat($tempOrdersNonCash);
        }

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
        // Check if shift system is disabled
        if (\App\Models\Setting::get('use_shifts', '1') != '1') {
            return redirect()->route('shifts.index')
                ->with('error', 'Shift management is currently disabled in settings.');
        }

        // Check if there's already an open shift
        $currentShift = Shift::getCurrentShift();

        if ($currentShift) {
            return redirect()->route('shifts.index')
                ->with('error', 'There is already an open shift. Please close it before opening a new one.');
        }

        $masterShifts = \App\Models\MasterShift::where('is_active', true)->get();

        return view('shifts.create', compact('masterShifts'));
    }

    public function store(Request $request)
    {
        // Check if shift system is disabled
        if (\App\Models\Setting::get('use_shifts', '1') != '1') {
            return redirect()->route('shifts.index')
                ->with('error', 'Shift management is currently disabled in settings.');
        }

        $request->validate([
            'master_shift_id' => 'required|exists:master_shifts,id',
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check again if there's an open shift
        $currentShift = Shift::getCurrentShift();

        if ($currentShift) {
            return back()->with('error', 'There is already an open shift.');
        }

        $masterShift = \App\Models\MasterShift::find($request->master_shift_id);

        $shift = Shift::create([
            'master_shift_id' => $masterShift->id,
            'shift_number' => $masterShift->id, // fallback logic
            'shift_date' => Shift::getShiftDate(), // might need adjusting depending on if we want to change this logic too
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

        // Check if we should include temp orders in calculation
        $includeTempOrders = \App\Models\Setting::get('include_temp_orders_in_shift_close', '0') == '1';

        // Calculate expected cash
        $cashPayments = $shift->payments()
            ->where('method', 'cash')
            ->where('status', PaymentStatus::PAID)
            ->sum('amount');

        $qrisPayments = $shift->payments()
            ->where('method', '!=', 'cash')
            ->whereNotNull('method')
            ->where('status', PaymentStatus::PAID)
            ->sum('amount');

        $totalSales = $shift->payments()
            ->where('status', PaymentStatus::PAID)
            ->sum('amount');
            
        $totalOrders = $shift->orders()->count();

        if ($includeTempOrders) {
            $tempCashPayments = \App\Models\TempOrder::where('shift_id', $shift->id)
                ->where('payment_method', 'cash')
                ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN payment_amount > 0 THEN payment_amount ELSE total END'));
            $cashPayments += $tempCashPayments;

            $tempQrisPayments = \App\Models\TempOrder::where('shift_id', $shift->id)
                ->where('payment_method', '!=', 'cash')
                ->whereNotNull('payment_method')
                ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN payment_amount > 0 THEN payment_amount ELSE total END'));
            $qrisPayments += $tempQrisPayments;

            $tempSales = \App\Models\TempOrder::where('shift_id', $shift->id)
                ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN payment_amount > 0 THEN payment_amount ELSE total END'));
            $totalSales += $tempSales;
            
            $tempOrdersCount = \App\Models\TempOrder::where('shift_id', $shift->id)->count();
            $totalOrders += $tempOrdersCount;
        }

        $expectedCash = $shift->opening_cash + $cashPayments;

        // Get pending orders
        $pendingOrders = $shift->orders()
            ->where('status', 'pending')
            ->with('items')
            ->get();

        return view('shifts.close', compact(
            'shift', 
            'expectedCash', 
            'pendingOrders', 
            'cashPayments', 
            'qrisPayments', 
            'totalSales', 
            'totalOrders'
        ));
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
            // Check if we should include temp orders in calculation
            $includeTempOrders = \App\Models\Setting::get('include_temp_orders_in_shift_close', '0') == '1';

            // Calculate totals
            $totalSales = $shift->payments()
                ->where('status', PaymentStatus::PAID)
                ->sum('amount');

            $totalOrders = $shift->orders()->count();

            $cashPayments = $shift->payments()
                ->where('method', 'cash')
                ->where('status', PaymentStatus::PAID)
                ->sum('amount');

            if ($includeTempOrders) {
                $tempSales = \App\Models\TempOrder::where('shift_id', $shift->id)
                    ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN payment_amount > 0 THEN payment_amount ELSE total END'));
                
                $tempCashPayments = \App\Models\TempOrder::where('shift_id', $shift->id)
                    ->where('payment_method', 'cash')
                    ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN payment_amount > 0 THEN payment_amount ELSE total END'));
                
                $tempOrdersCount = \App\Models\TempOrder::where('shift_id', $shift->id)->count();
                
                $totalSales += $tempSales;
                $cashPayments += $tempCashPayments;
                $totalOrders += $tempOrdersCount;
            }

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

        $paymentsByMethodInfo = $shift->payments()
            ->where('status', PaymentStatus::PAID)
            ->get();

        $includeTempOrders = \App\Models\Setting::get('include_temp_orders_in_shift_close', '0') == '1';

        if ($includeTempOrders) {
            $tempOrdersCash = \App\Models\TempOrder::where('shift_id', $shift->id)
                ->where('payment_method', 'cash')
                ->get()
                ->map(function ($order) use ($shift) {
                    $payment = new \App\Models\Payment();
                    $payment->amount = $order->payment_amount ?: $order->total;
                    $payment->method = 'cash';
                    $payment->status = PaymentStatus::PAID;
                    $payment->shift_id = $shift->id;
                    $payment->created_at = $order->created_at;
                    return $payment;
                });

            $cashPayments = $cashPayments->concat($tempOrdersCash);
            $paymentsByMethodInfo = $paymentsByMethodInfo->concat($tempOrdersCash);

            $tempOrdersNonCash = \App\Models\TempOrder::where('shift_id', $shift->id)
                ->where('payment_method', '!=', 'cash')
                ->whereNotNull('payment_method')
                ->get()
                ->map(function ($order) use ($shift) {
                    $payment = new \App\Models\Payment();
                    $payment->amount = $order->payment_amount ?: $order->total;
                    $payment->method = $order->payment_method;
                    $payment->status = PaymentStatus::PAID;
                    $payment->shift_id = $shift->id;
                    $payment->created_at = $order->created_at;
                    return $payment;
                });

            $nonCashPayments = $nonCashPayments->concat($tempOrdersNonCash);
            $paymentsByMethodInfo = $paymentsByMethodInfo->concat($tempOrdersNonCash);
        }

        $paymentsByMethod = $paymentsByMethodInfo->groupBy('method');

        return view('shifts.print', compact(
            'shift',
            'cashPayments',
            'nonCashPayments',
            'paymentsByMethod'
        ));
    }
}

