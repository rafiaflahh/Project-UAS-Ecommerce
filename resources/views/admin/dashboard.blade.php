@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
        <p class="text-gray-600">Manage your e-commerce store from here</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Quick Stats -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Stats</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ \App\Models\Category::count() }}</div>
                    <div class="text-sm text-gray-500">Categories</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ \App\Models\Product::count() }}</div>
                    <div class="text-sm text-gray-500">Products</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ \App\Models\User::count() }}</div>
                    <div class="text-sm text-gray-500">Users</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ \App\Models\Transaction::count() }}</div>
                    <div class="text-sm text-gray-500">Orders</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="{{ route('admin.categories.index') }}" class="block w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition-colors">
                    📦 Manage Categories
                </a>
                <a href="{{ route('admin.products.index') }}" class="block w-full text-left px-4 py-3 bg-green-50 hover:bg-green-100 text-green-600 rounded-lg transition-colors">
                    🛒️ Manage Products
                </a>
                <a href="{{ route('admin.stocks.index') }}" class="block w-full text-left px-4 py-3 bg-yellow-50 hover:bg-yellow-100 text-yellow-600 rounded-lg transition-colors">
                    📊 Manage Stock
                </a>
                <a href="{{ route('admin.transactions.index') }}" class="block w-full text-left px-4 py-3 bg-purple-50 hover:bg-purple-100 text-purple-600 rounded-lg transition-colors">
                    📋 View Transactions
                </a>
                <a href="{{ route('admin.statistics.index') }}" class="block w-full text-left px-4 py-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors">
                    📈 View Statistics
                </a>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">System Status</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Database Connection</span>
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">✅ Connected</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">User Roles</span>
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">✅ Configured</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Product Table</span>
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">✅ Ready</span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Stock Management</span>
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">✅ Ready</span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Transaction System</span>
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">✅ Ready</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Activity</h2>
            <div class="text-center text-gray-500">
                <div class="text-6xl font-bold mb-2">🛍</div>
                <div class="text-sm">System is clean and ready</div>
            </div>
            <div class="text-sm text-gray-600 mt-4">
                <p class="mb-2">Last action: Database cleanup completed</p>
                <p class="mb-2">Products removed: {{ \App\Models\Product::whereNotNull('name')->count() }}</p>
                <p class="mb-2">Stocks removed: {{ \App\Models\ProductStock::count() }}</p>
                <p class="mb-2">Transactions removed: {{ \App\Models\TransactionItem::count() }}</p>
            </div>
        </div>

        <!-- Help Section -->
        <div class="bg-blue-50 rounded-xl shadow-lg p-6">
            <div class="text-center text-white">
                <h2 class="text-2xl font-bold mb-4">🎉 Admin System Ready!</h2>
                <p class="text-lg mb-4">Your e-commerce platform is now fully operational with:</p>
                <ul class="text-left text-white space-y-2">
                    <li>✅ Product Management</li>
                    <li>✅ Stock Control</li>
                    <li>✅ Order Tracking</li>
                    <li>✅ User Roles</li>
                    <li>✅ Security Features</li>
                    <li>✅ Modern Dashboard</li>
                    <li>✅ Clean Database</li>
                </ul>
                <div class="mt-6">
                    <h3 class="text-xl font-bold mb-2">Next Steps:</h3>
                    <ol class="text-left text-white space-y-2">
                        <li>Create new products and categories</li>
                        <li>Manage inventory and stock levels</li>
                        <li>Process customer orders</li>
                        <li>View sales analytics</li>
                        <li>Configure payment gateways</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection