<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function products(): \Illuminate\Http\JsonResponse
    {
        $products = Product::with('category')->where('active', 1)->whereHas('category', function ($query) {
            $query->where('active', 1);
        })->select('id', 'name', 'price', 'description', 'category_id')->paginate(10);

        return response()->json($products);
    }

    public function categories(): \Illuminate\Http\JsonResponse
    {
        $categories = Category::where('active', 1)->select('id', 'name')->paginate(10);
        return response()->json($categories);
    }

    public function myOrders(): \Illuminate\Http\JsonResponse
    {
        $orders = Order::with('order_items')
            ->where('user_id', auth()->user()->id)
            ->join('products', 'products.id', '=', 'orders.product_id')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->select('orders.id', 'orders.status', 'order_items.quantity', DB::raw('SUM(products.price * order_items.quantity) as total_price'))
            ->groupBy('orders.id', 'orders.status', 'order_items.quantity')
            ->get();
        return response()->json($orders);
    }
}
