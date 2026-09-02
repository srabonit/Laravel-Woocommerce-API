<?php

namespace App\Http\Controllers;

use App\Services\WooCommerceService;
use Illuminate\Http\Request;

class WooCommerceController extends Controller
{
    protected $wooCommerce;

    // onely one Constructor 
    public function __construct(WooCommerceService $wooCommerceService)
    {
        $this->wooCommerce = $wooCommerceService->getClient();
    }

    //get all product list
public function getProducts(Request $request)
{
    try {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 30);
        $search = $request->query('search'); // search keword

        $params = [
            'page'     => $page,
            'per_page' => $perPage,
            'status'   => 'publish'
        ];

        //product search from user
        if (!empty($search)) {
            $params['search'] = $search;
        }

        $client = $this->wooCommerce;
        $products = $client->get('products', $params);

        $headers = $client->http->getResponse()->getHeaders();
        $totalProducts = isset($headers['x-wp-total']) ? (int) $headers['x-wp-total'] : count($products);
        $totalPages = isset($headers['x-wp-totalpages']) ? (int) $headers['x-wp-totalpages'] : 1;

        return response()->json([
            'status'       => 'success',
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $totalProducts,
            'total_pages'  => $totalPages,
            'data'         => $products
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}


    // get single product details
    public function getProduct($id)
    {
        try {
            $product = $this->wooCommerce->get("products/{$id}");
            return response()->json($product, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    // create new order
    public function createOrder(Request $request)
    {
        $data = [
            'payment_method' => $request->payment_method ?? 'bacs',
            'payment_method_title' => $request->payment_method_title ?? 'Direct Bank Transfer',
            'set_paid' => false,
            'billing' => $request->billing,
            'line_items' => $request->line_items,
        ];

        try {
            $order = $this->wooCommerce->post('orders', $data);
            return response()->json($order, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

// get all categories with pagination
public function getCategories(Request $request)
{
    try {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 12); // প্রতি পেজে ১২টি ক্যাটাগরি

        $client = $this->wooCommerce;

        $categories = $client->get('products/categories', [
            'page'       => $page,
            'per_page'   => $perPage,
            'hide_empty' => false // blank categories show
        ]);

        // from WooCommerce Header  Total Categories & Total Pages
        $headers = $client->http->getResponse()->getHeaders();
        $totalCategories = isset($headers['x-wp-total']) ? (int) $headers['x-wp-total'] : count($categories);
        $totalPages = isset($headers['x-wp-totalpages']) ? (int) $headers['x-wp-totalpages'] : 1;

        return response()->json([
            'status'       => 'success',
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $totalCategories,
            'total_pages'  => $totalPages,
            'data'         => $categories
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

// get featured products (Paginated / Limited)
public function getFeaturedProducts(Request $request)
{
    try {
        $perPage = (int) $request->query('per_page', 15);

        // WooCommerce- main end point 'products', 'featured' => true 
        $products = $this->wooCommerce->get('products', [
            'featured' => true,
            'per_page' => $perPage,
            'status'   => 'publish'
        ]);

        return response()->json([
            'status' => 'success',
            'total'  => count($products),
            'data'   => $products
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
// get specific category products (Paginated)
public function getProductsByCategory(Request $request, $id)
{
    try {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 30);

        $client = $this->wooCommerce;

        $products = $client->get('products', [
            'category' => $id,
            'page'     => $page,
            'per_page' => $perPage,
            'status'   => 'publish'
        ]);

        $headers = $client->http->getResponse()->getHeaders();
        $totalProducts = isset($headers['x-wp-total']) ? (int) $headers['x-wp-total'] : count($products);
        $totalPages = isset($headers['x-wp-totalpages']) ? (int) $headers['x-wp-totalpages'] : 1;

        return response()->json([
            'status'       => 'success',
            'category_id'  => (int) $id,
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $totalProducts,
            'total_pages'  => $totalPages,
            'data'         => $products
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
}