<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketRequestController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategorySubController;
use App\Http\Controllers\LogoutController;

use App\Http\Controllers\MonitoringDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['middleware'=>['guest']],function(){
    Route::get('/', function () {
        return view('auth.login');
    });

    Route::get('/login',[LoginController::class,'getLogin'])->name('getLogin');
    Route::post('/login',[LoginController::class,'postLogin'])->name('postLogin');
});

Route::group(['middleware'=>['login_auth']],function(){
    Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard.index');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::prefix('/tickets')->group(function () {
        Route::get('/request/view/all',[TicketRequestController::class,'index'])->name('tickets.index');
        Route::get('/request/view/details',[TicketRequestController::class,'store'])->name('tickets.store');
    });

    Route::prefix('/manage')->group(function () {
        Route::get('/category/all',[CategoryController::class,'index'])->name('category.index');
        Route::get('/category/view/fetch',[CategoryController::class,'show'])->name('category.show');
        Route::post('/category/view/add',[CategoryController::class,'create'])->name('category.create');
        Route::post('/category/view/update',[CategoryController::class,'update'])->name('category.update');
        Route::post('/category/view/delete/{id}', [CategoryController::class, 'delete'])->name('category.delete');

        Route::get('/sub/category/view/fetch',[CategorySubController::class,'show'])->name('subcategory.show');
        Route::post('/sub/category/view/add',[CategorySubController::class,'create'])->name('subcategory.create');
        Route::post('/sub/category/view/update',[CategorySubController::class,'update'])->name('subcategory.update');
        Route::post('/sub/category/view/delete/{id}', [CategorySubController::class, 'delete'])->name('subcategory.delete');
    });

    Route::get('/dashboard/monitoring',[MonitoringDashboardController::class,'index'])->name('monitoring-dashboard.index');
});
