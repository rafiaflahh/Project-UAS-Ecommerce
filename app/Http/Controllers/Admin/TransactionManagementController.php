<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionManagementController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $transactions = Transaction::with(['user', 'transactionItems.product.category'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate(20);

        $statuses = ['PENDING', 'PROCESSING', 'COMPLETED', 'CANCELLED'];
        
        return view('admin.transactions.index', compact('transactions', 'statuses'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['transactionItems.product.category', 'user']);
        
        return view('admin.transactions.show', compact('transaction'));
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status' => 'required|in:PENDING,PROCESSING,COMPLETED,CANCELLED',
        ]);

        try {
            $result = $this->transactionService->updateTransactionStatus(
                $transaction, 
                $request->status
            );

            return redirect()->route('admin.transactions.show', $transaction)
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            return redirect()->route('admin.transactions.show', $transaction)
                ->with('error', $e->getMessage());
        }
    }

    public function cancel(Transaction $transaction)
    {
        try {
            $result = $this->transactionService->cancelTransaction($transaction);

            return redirect()->route('admin.transactions.show', $transaction)
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            return redirect()->route('admin.transactions.show', $transaction)
                ->with('error', $e->getMessage());
        }
    }

    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $report = $this->transactionService->getTransactionReport(
            $request->start_date,
            $request->end_date
        );

        return view('admin.transactions.report', compact('report'));
    }
}
