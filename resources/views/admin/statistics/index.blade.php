@extends('admin.layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Sales Statistics</h1>
    <p class="text-gray-600 mt-2">Comprehensive sales analytics and performance metrics</p>
</div>

<!-- Date Filter Form -->
<div class="bg-white rounded-lg shadow mb-6 p-4">
    <form action="{{ route('admin.statistics.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : now()->subDays(30)->format('Y-m-d') }}" 
                   class="px-3 py-2 border border-gray-300 rounded-md">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : now()->format('Y-m-d') }}" 
                   class="px-3 py-2 border border-gray-300 rounded-md">
        </div>
        
        <div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Update Statistics
            </button>
        </div>
        
        <div>
            <a href="{{ route('admin.statistics.index') }}" class="text-gray-600 hover:text-gray-800">
                Reset to Default
            </a>
        </div>
    </form>
</div>

<!-- Overview Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                <p class="text-xl font-bold text-green-600">Rp {{ number_format($statistics['overview']['total_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Transactions</p>
                <p class="text-xl font-bold text-blue-600">{{ $statistics['overview']['total_transactions'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 rounded-full p-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Avg Order Value</p>
                <p class="text-xl font-bold text-purple-600">Rp {{ number_format($statistics['overview']['average_order_value'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Items Sold</p>
                <p class="text-xl font-bold text-yellow-600">{{ $statistics['overview']['total_items_sold'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-indigo-100 rounded-full p-3">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Customers</p>
                <p class="text-xl font-bold text-indigo-600">{{ $statistics['overview']['total_customers'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Profit</p>
                <p class="text-xl font-bold text-red-600">Rp {{ number_format($statistics['overview']['total_profit'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Additional Metrics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Profit Margin -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Profit Margin</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($statistics['overview']['profit_margin'], 1) }}%</p>
                <p class="text-xs text-gray-500 mt-1">Revenue to Profit Ratio</p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full transition-all duration-500" 
                     style="width: {{ min($statistics['overview']['profit_margin'], 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Business Turnover -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Business Turnover</p>
                <p class="text-2xl font-bold text-purple-600">Rp {{ number_format($statistics['overview']['turnover'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Business Activity</p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Analytics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Revenue Trends Chart -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Revenue Trends</h2>
            <div class="flex space-x-2">
                <button onclick="loadChart('daily')" class="chart-btn px-3 py-1 text-sm rounded bg-blue-500 text-white">Daily</button>
                <button onclick="loadChart('monthly')" class="chart-btn px-3 py-1 text-sm rounded bg-gray-200 text-gray-700">Monthly</button>
                <button onclick="loadChart('yearly')" class="chart-btn px-3 py-1 text-sm rounded bg-gray-200 text-gray-700">Yearly</button>
            </div>
        </div>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Category Performance -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Category Performance</h2>
        <div class="h-64 overflow-y-auto">
            @foreach($statistics['category_performance']->take(8) as $category)
                <div class="mb-3">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium">{{ $category['category_name'] }}</span>
                        <span class="text-sm text-gray-600">Rp {{ number_format($category['total_revenue'], 0, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" 
                             style="width: {{ ($category['total_revenue'] / $statistics['category_performance']->max('total_revenue')) * 100 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Best Selling Products -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Best by Quantity -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Best Sellers by Quantity</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($statistics['best_selling_products']['by_quantity']->take(5) as $product)
                        <tr>
                            <td class="px-3 py-2 text-sm">
                                <div>
                                    <div class="font-medium">{{ Str::limit($product['product_name'], 30) }}</div>
                                    <div class="text-xs text-gray-500">{{ $product['category_name'] }}</div>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-sm font-medium">{{ $product['total_quantity'] }}</td>
                            <td class="px-3 py-2 text-sm font-medium">Rp {{ number_format($product['total_revenue'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-sm font-medium text-green-600">Rp {{ number_format($product['profit'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Best by Revenue -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Best Sellers by Revenue</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($statistics['best_selling_products']['by_revenue']->take(5) as $product)
                        <tr>
                            <td class="px-3 py-2 text-sm">
                                <div>
                                    <div class="font-medium">{{ Str::limit($product['product_name'], 30) }}</div>
                                    <div class="text-xs text-gray-500">{{ $product['category_name'] }}</div>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-sm font-medium">{{ $product['total_quantity'] }}</td>
                            <td class="px-3 py-2 text-sm font-medium">Rp {{ number_format($product['total_revenue'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-sm font-medium text-green-600">Rp {{ number_format($product['profit'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Time-Based Analytics -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Daily Analytics -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Analytics</h3>
        <div class="space-y-3">
            @if($statistics['daily_stats']->count() > 0)
                @php
                    $dailyStats = $statistics['daily_stats']->take(5);
                    $maxDailyRevenue = $dailyStats->max('total_revenue');
                @endphp
                @foreach($dailyStats as $day)
                    <div class="border-b pb-2">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($day['date'])->format('M j') }}</span>
                            <span class="text-sm font-bold">Rp {{ number_format($day['total_revenue'], 0, ',', '.') }}</span>
                        </div>
                        <div class="text-xs text-gray-600">
                            {{ $day['total_transactions'] }} orders • {{ $day['total_items_sold'] }} items
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                            <div class="bg-blue-500 h-1 rounded-full" 
                                 style="width: {{ ($day['total_revenue'] / $maxDailyRevenue) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500 text-sm">No daily data available</p>
            @endif
        </div>
    </div>

    <!-- Monthly Analytics -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Analytics</h3>
        <div class="space-y-3">
            @if($statistics['monthly_stats']->count() > 0)
                @php
                    $monthlyStats = $statistics['monthly_stats']->take(5);
                    $maxMonthlyRevenue = $monthlyStats->max('total_revenue');
                @endphp
                @foreach($monthlyStats as $month)
                    <div class="border-b pb-2">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium">{{ $month['month_name'] }} {{ $month['year'] }}</span>
                            <span class="text-sm font-bold">Rp {{ number_format($month['total_revenue'], 0, ',', '.') }}</span>
                        </div>
                        <div class="text-xs text-gray-600">
                            {{ $month['total_transactions'] }} orders • {{ $month['total_items_sold'] }} items
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                            <div class="bg-green-500 h-1 rounded-full" 
                                 style="width: {{ ($month['total_revenue'] / $maxMonthlyRevenue) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500 text-sm">No monthly data available</p>
            @endif
        </div>
    </div>

    <!-- Yearly Analytics -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Yearly Analytics</h3>
        <div class="space-y-3">
            @if($statistics['yearly_stats']->count() > 0)
                @php
                    $yearlyStats = $statistics['yearly_stats']->take(5);
                    $maxYearlyRevenue = $yearlyStats->max('total_revenue');
                @endphp
                @foreach($yearlyStats as $year)
                    <div class="border-b pb-2">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium">{{ $year['year'] }}</span>
                            <span class="text-sm font-bold">Rp {{ number_format($year['total_revenue'], 0, ',', '.') }}</span>
                        </div>
                        <div class="text-xs text-gray-600">
                            {{ $year['total_transactions'] }} orders • {{ $year['total_items_sold'] }} items
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                            <div class="bg-purple-500 h-1 rounded-full" 
                                 style="width: {{ ($year['total_revenue'] / $maxYearlyRevenue) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500 text-sm">No yearly data available</p>
            @endif
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Export Statistics</h2>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.statistics.export', ['type' => 'daily', 'start_date' => $startDate?->format('Y-m-d'), 'end_date' => $endDate?->format('Y-m-d')]) }}" 
           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
            Export Daily Stats
        </a>
        <a href="{{ route('admin.statistics.export', ['type' => 'monthly', 'start_date' => $startDate?->format('Y-m-d'), 'end_date' => $endDate?->format('Y-m-d')]) }}" 
           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
            Export Monthly Stats
        </a>
        <a href="{{ route('admin.statistics.export', ['type' => 'products', 'start_date' => $startDate?->format('Y-m-d'), 'end_date' => $endDate?->format('Y-m-d')]) }}" 
           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
            Export Best Products
        </a>
        <a href="{{ route('admin.statistics.export', ['type' => 'categories', 'start_date' => $startDate?->format('Y-m-d'), 'end_date' => $endDate?->format('Y-m-d')]) }}" 
           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
            Export Categories
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentChart = null;
let currentChartType = 'daily';

// Initialize chart on page load
document.addEventListener('DOMContentLoaded', function() {
    loadChart('daily');
});

function loadChart(type) {
    // Update button styles
    document.querySelectorAll('.chart-btn').forEach(btn => {
        btn.classList.remove('bg-blue-500', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    
    event.target.classList.remove('bg-gray-200', 'text-gray-700');
    event.target.classList.add('bg-blue-500', 'text-white');
    
    currentChartType = type;
    
    // Load data based on type
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    let url = `{{ route('admin.statistics.daily') }}?start_date=${startDate}&end_date=${endDate}`;
    
    if (type === 'monthly') {
        url = `{{ route('admin.statistics.monthly') }}?start_date=${startDate}&end_date=${endDate}`;
    } else if (type === 'yearly') {
        url = `{{ route('admin.statistics.yearly') }}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            renderChart(data, type);
        })
        .catch(error => console.error('Error loading chart data:', error));
}

function renderChart(data, type) {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    if (currentChart) {
        currentChart.destroy();
    }
    
    const chartData = {
        labels: data.map(item => type === 'yearly' ? item.year : item.date || item.month),
        datasets: [{
            label: 'Revenue',
            data: data.map(item => item.total_revenue),
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            tension: 0.4
        }]
    };
    
    const options = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        }
    };
    
    currentChart = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: options
    });
}
</script>
@endsection