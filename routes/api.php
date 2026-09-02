<?php

use App\Http\Controllers\WooCommerceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;

// Product routes
Route::get('/products', [WooCommerceController::class, 'getProducts']);
Route::get('/products/featured', [WooCommerceController::class, 'getFeaturedProducts']);
Route::get('/products/{id}', [WooCommerceController::class, 'getProduct']);

// Order routes
Route::post('/orders', [WooCommerceController::class, 'createOrder']);
Route::post('/create-order', [OrderController::class, 'store']);

// Category routes
Route::get('/categories', [WooCommerceController::class, 'getCategories']);
Route::get('/categories/{id}/products', [WooCommerceController::class, 'getProductsByCategory']);

// User CRUD Routes
Route::post('/users/register', [UserController::class, 'register']); // Create / Register
Route::get('/users', [UserController::class, 'index']);               // Read All
Route::get('/users/{id}', [UserController::class, 'show']);           // Read Single
Route::put('/users/{id}', [UserController::class, 'update']);         // Update Profile
Route::delete('/users/{id}', [UserController::class, 'destroy']);     // Delete User
Route::post('/login', [UserController::class, 'login']);