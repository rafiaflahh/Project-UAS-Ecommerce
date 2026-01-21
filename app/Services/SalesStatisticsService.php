<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesStatisticsService
{
    /**
     * Get comprehensive sales statistics dashboard data
     */
    public function getDashboardStats(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: now()->subDays(30)->startOfDay();
        $endDate = $endDate ?: now()->endOfDay();

        return [
            'overview' => $this->getOverviewStats($startDate, $endDate),
            'daily_stats' => $this->getDailyStats($startDate, $endDate),
            'monthly_stats' => $this->getMonthlyStats($startDate, $endDate),
            'yearly_stats' => $this->getYearlyStats($startDate, $endDate),
            'best_selling_products' => $this->getBestSellingProducts($startDate, $endDate, 10),
            'revenue_trends' => $this->getRevenueTrends($startDate, $endDate),
            'category_performance' => $this->getCategoryPerformance($startDate, $endDate),
        ];
    }

    /**
     * Get overview statistics for the period
     */
    public function getOverviewStats(Carbon $startDate, Carbon $endDate): array
    {
        $baseQuery = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'COMPLETED');

        $totalRevenue = $baseQuery->sum('total_amount');
        $totalProfit = $this->calculateProfitForPeriod($startDate, $endDate);

        return [
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
            'total_transactions' => $baseQuery->count(),
            'average_order_value' => $baseQuery->avg('total_amount'),
            'total_items_sold' => TransactionItem::whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'COMPLETED');
            })->sum('quantity'),
            'total_customers' => $baseQuery->distinct('user_id')->count('user_id'),
            'conversion_rate' => $this->calculateConversionRate($startDate, $endDate),
            'turnover' => $this->calculateTurnover($startDate, $endDate),
        ];
    }

    /**
     * Get daily sales statistics
     */
    public function getDailyStats(Carbon $startDate, Carbon $endDate): Collection
    {
        return Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'COMPLETED')
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_transactions,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value,
                COUNT(DISTINCT user_id) as unique_customers
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($stat) {
                return [
                    'date' => $stat->date,
                    'total_transactions' => (int) $stat->total_transactions,
                    'total_revenue' => (float) $stat->total_revenue,
                    'average_order_value' => (float) $stat->average_order_value,
                    'unique_customers' => (int) $stat->unique_customers,
                    'total_items_sold' => $this->getItemsSoldForDate($stat->date),
                    'profit' => $this->calculateProfitForDate($stat->date),
                ];
            });
    }

    /**
     * Get monthly sales statistics
     */
    public function getMonthlyStats(Carbon $startDate, Carbon $endDate): Collection
    {
        return Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'COMPLETED')
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                YEAR(created_at) as year,
                MONTHNAME(created_at) as month_name,
                COUNT(*) as total_transactions,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value,
                COUNT(DISTINCT user_id) as unique_customers
            ')
            ->groupBy('month', 'year', 'month_name')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($stat) {
                return [
                    'month' => $stat->month,
                    'month_name' => $stat->month_name,
                    'year' => (int) $stat->year,
                    'total_transactions' => (int) $stat->total_transactions,
                    'total_revenue' => (float) $stat->total_revenue,
                    'average_order_value' => (float) $stat->average_order_value,
                    'unique_customers' => (int) $stat->unique_customers,
                    'total_items_sold' => $this->getItemsSoldForMonth($stat->month),
                    'profit' => $this->calculateProfitForMonth($stat->month),
                ];
            });
    }

    /**
     * Get yearly sales statistics
     */
    public function getYearlyStats(): Collection
    {
        return Transaction::where('status', 'COMPLETED')
            ->selectRaw('
                YEAR(created_at) as year,
                COUNT(*) as total_transactions,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value,
                COUNT(DISTINCT user_id) as unique_customers
            ')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(function ($stat) {
                return [
                    'year' => (int) $stat->year,
                    'total_transactions' => (int) $stat->total_transactions,
                    'total_revenue' => (float) $stat->total_revenue,
                    'average_order_value' => (float) $stat->average_order_value,
                    'unique_customers' => (int) $stat->unique_customers,
                    'total_items_sold' => $this->getItemsSoldForYear($stat->year),
                    'profit' => $this->calculateProfitForYear($stat->year),
                ];
            });
    }

    /**
     * Get best-selling products by quantity and revenue
     */
    public function getBestSellingProducts(Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        $bestByQuantity = TransactionItem::selectRaw('
            product_id,
            SUM(quantity) as total_quantity,
            SUM(subtotal) as total_revenue,
            COUNT(DISTINCT transaction_id) as total_orders,
            AVG(unit_price) as average_price
        ')
            ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'COMPLETED');
            })
            ->with('product.category')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? 'Deleted Product',
                    'category_name' => $item->product?->category?->name ?? 'N/A',
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                    'total_orders' => (int) $item->total_orders,
                    'average_price' => (float) $item->average_price,
                    'profit' => $this->calculateProductProfit($item->product_id, $item->total_quantity),
                ];
            });

        $bestByRevenue = TransactionItem::selectRaw('
            product_id,
            SUM(quantity) as total_quantity,
            SUM(subtotal) as total_revenue,
            COUNT(DISTINCT transaction_id) as total_orders,
            AVG(unit_price) as average_price
        ')
            ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'COMPLETED');
            })
            ->with('product.category')
            ->groupBy('product_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? 'Deleted Product',
                    'category_name' => $item->product?->category?->name ?? 'N/A',
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                    'total_orders' => (int) $item->total_orders,
                    'average_price' => (float) $item->average_price,
                    'profit' => $this->calculateProductProfit($item->product_id, $item->total_quantity),
                ];
            });

        return [
            'by_quantity' => $bestByQuantity,
            'by_revenue' => $bestByRevenue,
        ];
    }

    /**
     * Get revenue trends for charts
     */
    public function getRevenueTrends(Carbon $startDate, Carbon $endDate): array
    {
        $days = $startDate->diffInDays($endDate) + 1;
        
        if ($days <= 31) {
            // Daily data for short periods
            return $this->getDailyTrends($startDate, $endDate);
        } elseif ($days <= 365) {
            // Weekly data for medium periods
            return $this->getWeeklyTrends($startDate, $endDate);
        } else {
            // Monthly data for long periods
            return $this->getMonthlyTrends($startDate, $endDate);
        }
    }

    /**
     * Get category performance statistics
     */
    public function getCategoryPerformance(Carbon $startDate, Carbon $endDate): Collection
    {
        return TransactionItem::selectRaw('
            p.category_id,
            c.name as category_name,
            SUM(ti.quantity) as total_quantity,
            SUM(ti.subtotal) as total_revenue,
            COUNT(DISTINCT ti.transaction_id) as total_orders,
            AVG(ti.unit_price) as average_price,
            COUNT(DISTINCT t.user_id) as unique_customers
        ')
            ->from('transaction_items as ti')
            ->join('transactions as t', 'ti.transaction_id', '=', 't.id')
            ->join('products as p', 'ti.product_id', '=', 'p.id')
            ->join('categories as c', 'p.category_id', '=', 'c.id')
            ->whereBetween('t.created_at', [$startDate, $endDate])
            ->where('t.status', 'COMPLETED')
            ->groupBy('p.category_id', 'c.name')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->map(function ($stat) {
                return [
                    'category_id' => $stat->category_id,
                    'category_name' => $stat->category_name,
                    'total_quantity' => (int) $stat->total_quantity,
                    'total_revenue' => (float) $stat->total_revenue,
                    'total_orders' => (int) $stat->total_orders,
                    'average_price' => (float) $stat->average_price,
                    'unique_customers' => (int) $stat->unique_customers,
                    'profit' => $this->calculateCategoryProfit($stat->category_id),
                ];
            });
    }

    /**
     * Calculate profit for a specific date
     */
    private function calculateProfitForDate(string $date): float
    {
        $items = TransactionItem::whereHas('transaction', function ($query) use ($date) {
                $query->whereDate('created_at', $date)
                    ->where('status', 'COMPLETED');
            })->with('product')->get();

        $totalProfit = 0;
        foreach ($items as $item) {
            if ($item->product && $item->product->cost_price) {
                $totalProfit += ($item->product->price - $item->product->cost_price) * $item->quantity;
            } else {
                $totalProfit += $item->subtotal * 0.7; // Fallback to 70% margin
            }
        }

        return $totalProfit;
    }

    /**
     * Calculate profit for a specific month
     */
    private function calculateProfitForMonth(string $month): float
    {
        return TransactionItem::whereHas('transaction', function ($query) use ($month) {
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
                    ->where('status', 'COMPLETED');
            })->sum('subtotal') * 0.7;
    }

    /**
     * Calculate profit for a specific year
     */
    private function calculateProfitForYear(int $year): float
    {
        return TransactionItem::whereHas('transaction', function ($query) use ($year) {
                $query->whereYear('created_at', $year)
                    ->where('status', 'COMPLETED');
            })->sum('subtotal') * 0.7;
    }

    /**
     * Calculate profit for a specific product
     */
    private function calculateProductProfit(int $productId, int $quantity): float
    {
        $product = Product::find($productId);
        if (!$product) return 0;
        
        // Use actual cost price if available, otherwise estimate 70% margin
        if ($product->cost_price) {
            return ($product->price - $product->cost_price) * $quantity;
        } else {
            return ($product->price * $quantity) * 0.7;
        }
    }

    /**
     * Calculate profit for a specific category
     */
    private function calculateCategoryProfit(int $categoryId): float
    {
        return TransactionItem::whereHas('product', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })->whereHas('transaction', function ($query) {
                $query->where('status', 'COMPLETED');
            })->sum('subtotal') * 0.7;
    }

    /**
     * Get total items sold for a specific date
     */
    private function getItemsSoldForDate(string $date): int
    {
        return TransactionItem::whereHas('transaction', function ($query) use ($date) {
                $query->whereDate('created_at', $date)
                    ->where('status', 'COMPLETED');
            })->sum('quantity');
    }

    /**
     * Get total items sold for a specific month
     */
    private function getItemsSoldForMonth(string $month): int
    {
        return TransactionItem::whereHas('transaction', function ($query) use ($month) {
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
                    ->where('status', 'COMPLETED');
            })->sum('quantity');
    }

    /**
     * Get total items sold for a specific year
     */
    private function getItemsSoldForYear(int $year): int
    {
        return TransactionItem::whereHas('transaction', function ($query) use ($year) {
                $query->whereYear('created_at', $year)
                    ->where('status', 'COMPLETED');
            })->sum('quantity');
    }

    /**
     * Calculate conversion rate (completed transactions / total created)
     */
    private function calculateConversionRate(Carbon $startDate, Carbon $endDate): float
    {
        $totalCreated = Transaction::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalCompleted = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'COMPLETED')
            ->count();

        if ($totalCreated === 0) return 0;

        return ($totalCompleted / $totalCreated) * 100;
    }

    /**
     * Get daily trends for charts
     */
    private function getDailyTrends(Carbon $startDate, Carbon $endDate): array
    {
        return Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'COMPLETED')
            ->selectRaw('
                DATE(created_at) as period,
                SUM(total_amount) as revenue,
                COUNT(*) as transactions
            ')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => $item->period,
                    'revenue' => (float) $item->revenue,
                    'transactions' => (int) $item->transactions,
                ];
            })
            ->toArray();
    }

    /**
     * Get weekly trends for charts
     */
    private function getWeeklyTrends(Carbon $startDate, Carbon $endDate): array
    {
        return Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'COMPLETED')
            ->selectRaw('
                CONCAT(YEAR(created_at), "-W", WEEK(created_at)) as period,
                SUM(total_amount) as revenue,
                COUNT(*) as transactions
            ')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => $item->period,
                    'revenue' => (float) $item->revenue,
                    'transactions' => (int) $item->transactions,
                ];
            })
            ->toArray();
    }

    /**
     * Get monthly trends for charts
     */
    private function getMonthlyTrends(Carbon $startDate, Carbon $endDate): array
    {
        return Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'COMPLETED')
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as period,
                SUM(total_amount) as revenue,
                COUNT(*) as transactions
            ')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => $item->period,
                    'revenue' => (float) $item->revenue,
                    'transactions' => (int) $item->transactions,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate profit for a specific period
     */
    private function calculateProfitForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        $items = TransactionItem::whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'COMPLETED');
            })->with('product')->get();

        $totalProfit = 0;
        foreach ($items as $item) {
            if ($item->product && $item->product->cost_price) {
                $totalProfit += ($item->product->price - $item->product->cost_price) * $item->quantity;
            } else {
                // Fallback to estimated profit margin
                $totalProfit += $item->subtotal * 0.7;
            }
        }

        return $totalProfit;
    }

    /**
     * Calculate business turnover (revenue - cost of goods sold)
     */
    private function calculateTurnover(Carbon $startDate, Carbon $endDate): float
    {
        return $this->calculateProfitForPeriod($startDate, $endDate);
    }
}