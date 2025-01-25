<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnums;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
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
            'product_id' => 'required|exists:products,id',
            'gross_amount' => 'required|numeric',
        ]);

        $user = Auth::user();
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
                'order_id' => $order_id,
            ]);

            $product = Product::find($request->product_id);
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


    public function paymentCallback(Request $request)
    {
        $order_id = $request->input('order_id');
        $payment_status = $request->input('payment_status'); // Depending on the payment gateway

        // Find the order by ID
        $order = Order::where('order_id', $order_id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Update the order status based on the payment status
        if ($payment_status === 'success') {
            $order->status = 'completed'; // Mark the order as completed
        } else {
            $order->status = 'failed'; // Mark the order as failed
        }

        $order->save();

        return response()->json(['status' => 'success', 'order' => $order]);
    }


    public function getOrders()
    {
        $orders = Order::with('user', 'product')->get();
        return response()->json([
            'orders' => $orders,
        ]);
    }
}
