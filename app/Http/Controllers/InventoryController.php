<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::orderBy('name')->paginate(20);
        $lowStockCount = InventoryItem::whereColumn('current_quantity', '<=', 'minimum_quantity')->count();

        return view('inventory.index', compact('items', 'lowStockCount'));
    }

    public function create()
    {
        return view('inventory.form', ['item' => new InventoryItem()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'unit'             => ['required', 'string', 'max:50'],
            'current_quantity' => ['required', 'numeric', 'min:0'],
            'minimum_quantity' => ['required', 'numeric', 'min:0'],
            'unit_cost'        => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        InventoryItem::create($validated);

        return redirect()->route('inventory.index')->with('success', 'تم إضافة الصنف بنجاح.');
    }

    public function edit(InventoryItem $item)
    {
        return view('inventory.form', compact('item'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'unit'             => ['required', 'string', 'max:50'],
            'minimum_quantity' => ['required', 'numeric', 'min:0'],
            'unit_cost'        => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $item->update($validated);

        return redirect()->route('inventory.index')->with('success', 'تم تحديث بيانات الصنف.');
    }

    /**
     * تسجيل حركة توريد أو صرف لصنف معين (تحدّث الكمية الحالية تلقائياً عبر الموديل).
     */
    public function storeTransaction(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'transaction_type' => ['required', 'in:in,out'],
            'quantity'         => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'notes'            => ['nullable', 'string', 'max:255'],
        ]);

        $item->transactions()->create($validated);

        return back()->with('success', 'تم تسجيل الحركة وتحديث الكمية بنجاح.');
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();

        return back()->with('success', 'تم حذف الصنف.');
    }
}
