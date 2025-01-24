<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubCategoriesController;
use App\Http\Controllers\OrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Transaction
Route::post('/orders', [OrdersController::class, 'createOrders']);
Route::get('/orders', [OrdersController::class, 'getOrders']);

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::post('/store/product', [ProductController::class, 'upload']);
});

//Product
Route::get('/get/product', [ProductController::class, 'getProduct']);
Route::get('/get/product/{id}', [ProductController::class, 'getProductById']);
Route::get('/get/productExcel/{id}', [ProductController::class, 'getExcelUrl']);
Route::get('/get-excel-data/{id}', [ProductController::class, 'getExcelData']);

//Category
Route::get('/get/categories', [CategoriesController::class, 'getCategories']);
Route::post('/store/categories', [CategoriesController::class, 'setCategories']);
Route::put('/edit/categories/{id}', [CategoriesController::class, 'updateCategories']);
Route::delete('/delete/categories/{id}', [CategoriesController::class, 'deleteCategories']);

//SubCategory
Route::get('/subcategories', [SubCategoriesController::class, 'getSubCategories']);
Route::post('/store/subcategories', [SubCategoriesController::class, 'setSubCategories']);
Route::put('/edit/subcategories/{id}', [SubCategoriesController::class, 'updateSubCategories']);
Route::delete('/delete/subcategories/{id}', [SubCategoriesController::class, 'deleteSubCategories']);



//Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-google', [AuthController::class, 'loginGoogle']);
Route::get('/get/user',[AuthController::class, 'getUser']);
Route::put('/user/{id}/edit', [AuthController::class, 'editUser']);

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
