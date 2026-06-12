<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    public function index()
    {
        $paymentTypes = PaymentType::withCount('payments')->latest()->paginate(15);
        return view('payment-types.index', compact('paymentTypes'));
    }

    public function create()
    {
        return view('payment-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:payment_types,name',
            'description' => 'nullable|string|max:500',
            'amount'      => 'required|numeric|min:0|max:99999',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        PaymentType::create($validated);

        return redirect()->back()
            ->with('success', 'Payment type created successfully!');
    }

    public function edit(PaymentType $paymentType)
    {
        return view('payment-types.edit', compact('paymentType'));
    }

    public function update(Request $request, PaymentType $paymentType)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:payment_types,name,' . $paymentType->id,
            'description' => 'nullable|string|max:500',
            'amount'      => 'required|numeric|min:0|max:99999',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $paymentType->update($validated);

        return redirect()->back()
            ->with('success', 'Payment type updated successfully!');
    }

    public function destroy(PaymentType $paymentType)
    {
        if ($paymentType->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete: this payment type has existing payments.');
        }
        $paymentType->delete();
        return redirect()->back()
            ->with('success', 'Payment type deleted successfully!');
    }
}
