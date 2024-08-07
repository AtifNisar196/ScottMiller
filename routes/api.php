<?php

use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\CoupenCodeController;
use App\Http\Controllers\Api\Admin\CouponCodeController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\ShippingController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Common\ProfileController;
use App\Http\Controllers\Api\Frontend\BlogController as FrontendBlogController;
use App\Http\Controllers\Api\Frontend\CartController;
use App\Http\Controllers\Api\Frontend\CouponController;
use App\Http\Controllers\Api\Frontend\EmailSubscriberController;
use App\Http\Controllers\Api\Frontend\FavouriteController;
use App\Http\Controllers\Api\Frontend\LiberaryController;
use App\Http\Controllers\Api\Frontend\OrderController;
use App\Http\Controllers\Api\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Api\Frontend\ProductReviewController;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\Frontend\ContactController;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('getLoggedIn', [AuthController::class, 'me']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('profile/update', [ProfileController::class, 'update']);
    Route::post('profile/reset-password', [ProfileController::class, 'resetPassword']);
    Route::post('profile/sendOTP', [ProfileController::class, 'sendOTP']);
    Route::post('profile/checkOtp', [ProfileController::class, 'checkOtp']);
    Route::post('profile/changePassword', [ProfileController::class, 'changePassword']);
});

//admin
Route::group(['middleware' => ['api'], 'prefix' => 'admin/products'], function () {
    Route::get('getAll', [ProductController::class, 'getAll']);
    Route::post('store', [ProductController::class, 'store']);
    Route::post('update', [ProductController::class, 'update']);
    Route::get('delete/{id}', [ProductController::class, 'delete']);
    Route::get('getById/{id}', [ProductController::class, 'getById']);
    Route::get('changeStatus', [ProductController::class, 'changeStatus']);
});

Route::group(['middleware' => ['api', 'checkAuth'], 'prefix' => 'admin/users'], function () {
    Route::get('getAll', [UserController::class, 'getAll']);
    Route::get('getById', [UserController::class, 'getById']);
});

Route::group(['middleware' => ['api', 'checkAuth'], 'prefix' => 'admin/orders'], function () {
    Route::get('getAll', [AdminOrderController::class, 'getAll']);
    Route::put('change/status/{id}', [AdminOrderController::class, 'changeStatus']);
});

Route::group(['middleware' => ['api', 'checkAuth'], 'prefix' => 'admin/blog'], function () {
    Route::get('getAll', [BlogController::class, 'getAll']);
    Route::post('store', [BlogController::class, 'store']);
    Route::post('update', [BlogController::class, 'update']);
    Route::get('delete/{id}', [BlogController::class, 'delete']);
});

Route::group(['middleware' => ['api', 'checkAuth'], 'prefix' => 'admin/coupons'], function () {
    Route::get('getAll', [CouponCodeController::class, 'getAll']);
    Route::get('toggleStatus/{id}', [CouponCodeController::class, 'toggleStatus']);
    Route::post('store', [CouponCodeController::class, 'store']);
    Route::post('update', [CouponCodeController::class, 'update']);
});


Route::group(['middleware' => ['api', 'checkAuth'], 'prefix' => 'admin/categories'], function () {
    Route::get('getAll', [CategoryController::class, 'getAll']);
    Route::post('store', [CategoryController::class, 'store']);
});


//frontend
Route::group(['middleware' => 'api', 'prefix' => 'frontend/products'], function () {
    Route::get('getAll/{page?}', [FrontendProductController::class, 'getAll']);
    Route::get('getById', [FrontendProductController::class, 'getById']);

    Route::get('get-favourites', [FavouriteController::class, 'getAll'])->middleware('checkAuth');
    Route::post('add-favourite', [FavouriteController::class, 'store'])->middleware('checkAuth');

    Route::get('review/getAll', [ProductReviewController::class, 'getAll']);
    Route::post('review/store', [ProductReviewController::class, 'store'])->middleware('checkAuth');
});


Route::group(['middleware' => ['api', 'checkAuth'], 'prefix' => 'frontend/cart'], function () {

    Route::get('getAll', [CartController::class, 'getAll']);
    Route::post('store', [CartController::class, 'store']);
    Route::post('updateItemQuantity', [CartController::class, 'updateItemQuantity']);
    Route::post('deleteItem', [CartController::class, 'deleteItem']);
});


Route::group(['middleware' => ['api', 'checkAuth'], 'prefix' => 'shippings'], function () {

    Route::get('getAll', [ShippingController::class, 'getAll']);
    Route::post('store', [ShippingController::class, 'store']);
});

Route::group(['prefix' => 'frontend/order'], function () {
    Route::get('getAll', [OrderController::class, 'getAll']);
    Route::post('store', [OrderController::class, 'store']);
});


Route::group(['prefix' => 'frontend/contact'], function () {
    Route::get('getAll', [ContactController::class, 'getAll']);
    Route::post('store', [ContactController::class, 'store']);
});

Route::group(['prefix' => 'frontend/email-subscribers'], function () {
    Route::get('getAll', [EmailSubscriberController::class, 'getAll']);
    Route::post('store', [EmailSubscriberController::class, 'store']);
});

Route::group(['prefix' => 'frontend/blog/'], function () {
    Route::get('/{page?}', [FrontendBlogController::class, 'getAll']);
    Route::get('/getById/{id}', [FrontendBlogController::class, 'getById']);
});

Route::group(['prefix' => 'frontend/coupons/'], function () {
    Route::post('/getByCode', [CouponController::class, 'getByCode']);
});

Route::group(['prefix' => 'frontend/liberary'], function () {
    Route::get('/getAll', [LiberaryController::class, 'getAll']);
    Route::get('/show/{id}', [LiberaryController::class, 'getById']);
    Route::post('/bookmark/page', [LiberaryController::class, 'bookmark_page']);
});

//server
// Route::get('migrate-fresh', [ArtisanController::class, 'migrate_db']);
// Route::get('migrate', [ArtisanController::class, 'migrate']);
