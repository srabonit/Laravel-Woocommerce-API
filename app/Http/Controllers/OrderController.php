<?php

namespace App\Http\Controllers;

use App\Services\WooCommerceService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $wooCommerce;

    public function __construct(WooCommerceService $wooCommerce)
    {
        $this->wooCommerce = $wooCommerce;
    }

    public function store(Request $request)
    {
        // ১. Validation
        $request->validate([
            'first_name' => 'required|string',
            'phone'      => 'required|string',
            'address'    => 'required|string',
            'items'      => 'required|array',
        ]);

        // ২. WooCommerce Order Structure 
        $orderData = [
            'payment_method' => 'cod',
            'payment_method_title' => 'Cash on Delivery',
            'status' => 'processing', // give order status processing  , Woo will send email to customer
            'billing' => [
                'first_name' => $request->first_name,
                'email' => $request->email,    // email set
                'address_1' => $request->address,
                'phone' => $request->phone,
            ],
            'shipping' => [
                'first_name' => $request->first_name,
                'address_1'  => $request->address,
            ],
            'line_items' => array_map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ];
            }, $request->items),
        ];

    try {
        $order = $this->wooCommerce->getClient()->post('orders', $orderData);
        $orderId = is_array($order) ? $order['id'] : $order->id;

        return response()->json([
            'success'  => true,
            'message'  => 'Order created successfully!',
            'order_id' => $orderId,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
    }
}