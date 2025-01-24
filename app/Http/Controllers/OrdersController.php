<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnums;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class OrdersController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.serverKey');
        Config::$clientKey = config('midtrans.clientKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');
    }

    public function createOrders(Request $request)
{
    $request->validate([
        'id' => 'required|exists:users,id',
        'product_id' => 'required|exists:products,id',
        'gross_amount' => 'required|numeric',
    ]);

    $user = User::find($request->id);
    $order_id = Str::uuid();

    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => $request->gross_amount,
        ],
        'customer_details' => [
            'first_name' => $user->name,
            'last_name' => '',
            'email' => $user->email,
            'phone' => '',
        ],
    ];

    try {
        $paymentUrl = Snap::createTransaction($params)->redirect_url;

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
        ]);

        $product = \App\Models\Product::find($request->product_id);
        $product->last_purchased_at = now();
        $product->save();

        return response()->json([
            'payment_url' => $paymentUrl,
            'order' => $order,
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function getOrders()
    {
        $orders = Order::with('user')->get();
        return response()->json([
            'orders' => $orders,
        ]);
    }


}
