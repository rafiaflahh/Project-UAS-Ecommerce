<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->with(['transactionItems.product.category'])
            ->latest()
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $transaction->load(['transactionItems.product.category', 'user']);

        return view('transactions.show', compact('transaction'));
    }
}
