<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold">Admin Panel</h1>
                    <div class="flex space-x-4">
                        <a href="{{ route('admin.categories.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Categories</a>
                        <a href="{{ route('admin.products.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Products</a>
                        <a href="{{ route('admin.stocks.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Stocks</a>
                        <a href="{{ route('admin.transactions.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Transactions</a>
                        <a href="{{ route('admin.statistics.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded bg-blue-600">Statistics</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('shop.index') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v13a2 2 0 002 2z"></path>
                        </svg>
                        <span>Go to Shop</span>
                    </a>
                    <span>{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>