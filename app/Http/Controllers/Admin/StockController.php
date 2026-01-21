<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $stocks = ProductStock::with('product.category')
            ->latest()
            ->paginate(10);
        return view('admin.stocks.index', compact('stocks'));
    }

    public function create()
    {
        return redirect()->route('admin.products.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.products.create');
    }

    public function show(ProductStock $stock)
    {
        $stock->load('product.category');
        return view('admin.stocks.show', compact('stock'));
    }

    public function edit(ProductStock $stock)
    {
        $stock->load('product.category');
        return view('admin.stocks.edit', compact('stock'));
    }

    public function update(Request $request, ProductStock $stock)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'adjustment_type' => 'required|in:set,add,subtract',
        ]);

        $newQuantity = $stock->quantity;
        
        switch ($request->adjustment_type) {
            case 'set':
                $newQuantity = $request->quantity;
                break;
            case 'add':
                $newQuantity += $request->quantity;
                break;
            case 'subtract':
                $newQuantity = max(0, $newQuantity - $request->quantity);
                break;
        }

        $stock->update(['quantity' => $newQuantity]);

        return redirect()->route('admin.stocks.index')
            ->with('success', 'Stock updated successfully.');
    }

    public function destroy(ProductStock $stock)
    {
        return redirect()->route('admin.stocks.index')
            ->with('error', 'Cannot delete stock. Delete the product instead.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'stocks' => 'required|array',
            'stocks.*.id' => 'required|exists:product_stocks,id',
            'stocks.*.quantity' => 'required|integer|min:0',
        ]);

        foreach ($request->stocks as $stockData) {
            $stock = ProductStock::find($stockData['id']);
            if ($stock) {
                $stock->update(['quantity' => $stockData['quantity']]);
            }
        }

        return redirect()->route('admin.stocks.index')
            ->with('success', 'Stocks updated successfully.');
    }
}
