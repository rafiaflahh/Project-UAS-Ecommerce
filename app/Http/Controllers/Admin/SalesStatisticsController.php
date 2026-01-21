<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SalesStatisticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesStatisticsController extends Controller
{
    protected $salesStatisticsService;

    public function __construct(SalesStatisticsService $salesStatisticsService)
    {
        $this->salesStatisticsService = $salesStatisticsService;
    }

    /**
     * Display the sales statistics dashboard
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date'))->startOfDay() 
            : null;
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date'))->endOfDay() 
            : null;

        $statistics = $this->salesStatisticsService->getDashboardStats($startDate, $endDate);

        return view('admin.statistics.index', compact('statistics', 'startDate', 'endDate'));
    }

    /**
     * Get daily statistics for AJAX requests
     */
    public function dailyStats(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $stats = $this->salesStatisticsService->getDailyStats($startDate, $endDate);

        return response()->json($stats);
    }

    /**
     * Get monthly statistics for AJAX requests
     */
    public function monthlyStats(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $stats = $this->salesStatisticsService->getMonthlyStats($startDate, $endDate);

        return response()->json($stats);
    }

    /**
     * Get yearly statistics
     */
    public function yearlyStats()
    {
        $stats = $this->salesStatisticsService->getYearlyStats();

        return response()->json($stats);
    }

    /**
     * Get best selling products
     */
    public function bestSellingProducts(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $limit = $request->get('limit', 10);

        $stats = $this->salesStatisticsService->getBestSellingProducts($startDate, $endDate, $limit);

        return response()->json($stats);
    }

    /**
     * Get category performance
     */
    public function categoryPerformance(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $stats = $this->salesStatisticsService->getCategoryPerformance($startDate, $endDate);

        return response()->json($stats);
    }

    /**
     * Export statistics to CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:daily,monthly,yearly,products,categories',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $data = [];
        $filename = '';

        switch ($request->type) {
            case 'daily':
                $data = $this->salesStatisticsService->getDailyStats($startDate, $endDate);
                $filename = 'daily_stats_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
                break;
            case 'monthly':
                $data = $this->salesStatisticsService->getMonthlyStats($startDate, $endDate);
                $filename = 'monthly_stats_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
                break;
            case 'products':
                $products = $this->salesStatisticsService->getBestSellingProducts($startDate, $endDate, 100);
                $data = array_merge($products['by_quantity']->toArray(), $products['by_revenue']->toArray());
                $filename = 'best_selling_products_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
                break;
            case 'categories':
                $data = $this->salesStatisticsService->getCategoryPerformance($startDate, $endDate);
                $filename = 'category_performance_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
                break;
        }

        // Generate CSV content
        $csvContent = $this->generateCSV($data, $request->type);

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Generate CSV content from data
     */
    private function generateCSV($data, string $type): string
    {
        $csv = '';
        
        switch ($type) {
            case 'daily':
                $csv .= "Date,Transactions,Revenue (IDR),Average Order Value (IDR),Customers,Items Sold,Profit (IDR)\n";
                foreach ($data as $row) {
                    $csv .= "{$row['date']},{$row['total_transactions']},\"Rp " . number_format($row['total_revenue'], 0, ',', '.') . "\",\"Rp " . number_format($row['average_order_value'], 0, ',', '.') . "\",{$row['unique_customers']},{$row['total_items_sold']},\"Rp " . number_format($row['profit'], 0, ',', '.') . "\"\n";
                }
                break;
            case 'monthly':
                $csv .= "Month,Year,Transactions,Revenue (IDR),Average Order Value (IDR),Customers,Items Sold,Profit (IDR)\n";
                foreach ($data as $row) {
                    $csv .= "{$row['month']},{$row['year']},{$row['total_transactions']},\"Rp " . number_format($row['total_revenue'], 0, ',', '.') . "\",\"Rp " . number_format($row['average_order_value'], 0, ',', '.') . "\",{$row['unique_customers']},{$row['total_items_sold']},\"Rp " . number_format($row['profit'], 0, ',', '.') . "\"\n";
                }
                break;
            case 'products':
                $csv .= "Product Name,Category,Quantity Sold,Revenue (IDR),Orders,Average Price (IDR),Profit (IDR)\n";
                foreach ($data as $row) {
                    $csv .= "\"{$row['product_name']}\",\"{$row['category_name']}\",{$row['total_quantity']},\"Rp " . number_format($row['total_revenue'], 0, ',', '.') . "\",{$row['total_orders']},\"Rp " . number_format($row['average_price'], 0, ',', '.') . "\",\"Rp " . number_format($row['profit'], 0, ',', '.') . "\"\n";
                }
                break;
            case 'categories':
                $csv .= "Category,Quantity Sold,Revenue (IDR),Orders,Average Price (IDR),Customers,Profit (IDR)\n";
                foreach ($data as $row) {
                    $csv .= "\"{$row['category_name']}\",{$row['total_quantity']},\"Rp " . number_format($row['total_revenue'], 0, ',', '.') . "\",{$row['total_orders']},\"Rp " . number_format($row['average_price'], 0, ',', '.') . "\",{$row['unique_customers']},\"Rp " . number_format($row['profit'], 0, ',', '.') . "\"\n";
                }
                break;
        }

        return $csv;
    }
}
