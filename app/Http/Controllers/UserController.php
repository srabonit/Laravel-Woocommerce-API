<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Services\WooCommerceService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $wooCommerce;

    public function __construct(WooCommerceService $wooCommerce)
    {
        $this->wooCommerce = $wooCommerce;
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // WordPress JWT API request to get the token
        $response = Http::post('https://shopnowbd.online/wp-json/jwt-auth/v1/token', [
            'username' => $request->username,
            'password' => $request->password,
        ]);

        return response()->json($response->json(), $response->status());
    }






    //  Register / Create User (Customer)
    public function register(Request $request)
    {
        $request->validate([
            'email'      => 'required|email',
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'password'   => 'required|min:6',
        ]);

        $userData = [
            'email'      => $request->email,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'username'   => $request->email,
            'password'   => $request->password,
        ];

        try {
            $customer = $this->wooCommerce->getClient()->post('customers', $userData);
            return response()->json(['success' => true, 'message' => 'User registered successfully!', 'data' => $customer], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    //  Get All Users / Customers (Admin View)
    public function index()
    {
        try {
            $customers = $this->wooCommerce->getClient()->get('customers');
            return response()->json(['success' => true, 'data' => $customers], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Single User Profile Details
    public function show($id)
    {
        try {
            $customer = $this->wooCommerce->getClient()->get("customers/{$id}");
            return response()->json(['success' => true, 'data' => $customer], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    //  Update User Profile
    public function update(Request $request, $id)
    {
        $userData = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'billing'    => [
                'phone'     => $request->phone,
                'address_1' => $request->address,
            ],
        ];

        try {
            $updatedCustomer = $this->wooCommerce->getClient()->put("customers/{$id}", array_filter($userData));
            return response()->json(['success' => true, 'message' => 'Profile updated!', 'data' => $updatedCustomer], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // Delete User
    public function destroy($id)
    {
        try {
            $response = $this->wooCommerce->getClient()->delete("customers/{$id}", ['force' => true]);
            return response()->json(['success' => true, 'message' => 'User deleted successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}