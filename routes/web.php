<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketRequestController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategorySubController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\AccomplishmentReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRolesController;
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
        Route::get('/request/view/all/fetch/pending',[TicketRequestController::class,'showpending'])->name('tickets.showpending');
        Route::get('/request/view/all/fetch/progress',[TicketRequestController::class,'showprogress'])->name('tickets.showprogress');
        Route::get('/request/view/all/fetch/resolved',[TicketRequestController::class,'showresolved'])->name('tickets.showresolved');
        Route::get('/request/view/all/fetch/closed',[TicketRequestController::class,'showclosed'])->name('tickets.showclosed');
        Route::get('/request/view/details',[TicketRequestController::class,'store'])->name('tickets.store');
    });

    Route::prefix('/daily-task')->group(function () {
        Route::get('/view', [DailyTaskController::class, 'index'])->name('daily-task.index');
        Route::get('/get-subcategories/{categoryId}', [DailyTaskController::class, 'getSubcategories'])->name('daily-task.getSubcategories');
        Route::post('/create', [DailyTaskController::class, 'create'])->name('daily-task.create');
        Route::get('/show', [DailyTaskController::class, 'show'])->name('daily-task.show');
        Route::post('/update', [DailyTaskController::class, 'update'])->name('daily-task.update');
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

        Route::get('/office/list', [OfficeController::class, 'index'])->name('office.index');
        Route::get('/office/list/fetch', [OfficeController::class, 'show'])->name('office.show');
        Route::post('/office/list/add', [OfficeController::class, 'create'])->name('office.create');
        Route::post('/office/list/update', [OfficeController::class, 'update'])->name('office.update');
    });

    Route::prefix('/reports')->group(function () {
        Route::get('/accomplishment/generate', [AccomplishmentReportController::class, 'index'])->name('accomplishment-report.index');
        Route::get('/accomplishment/preview', [AccomplishmentReportController::class, 'previewPdf'])->name('accomplishment.preview');
    });

    Route::prefix('/users')->group(function () {
        Route::get('/list/view/all',[UserController::class,'index'])->name('user.index');
        Route::post('/list/view/add',[UserController::class,'create'])->name('user.create');
        Route::get('/list/view/fetch',[UserController::class,'show'])->name('user.show');
        Route::post('/list/view/update', [UserController::class, 'update'])->name('user.update');
        Route::post('/list/updatePass', [UserController::class, 'userUpdatePassword'])->name('userUpdatePassword');
        Route::post('list/updateStatusnow', [UserController::class, 'userUpdateStatus'])->name('userUpdateStatus');
    });

    Route::prefix('/roles')->group(function () {
        Route::get('/user/view/all',[UserRolesController::class,'index'])->name('roles.index');
        Route::post('/user/view/add',[UserRolesController::class,'create'])->name('roles.create');
        Route::get('/user/view/fetch',[UserRolesController::class,'show'])->name('roles.show');
        Route::post('/user/view/update', [UserRolesController::class, 'update'])->name('roles.update');
    });

    Route::get('/dashboard/monitoring',[MonitoringDashboardController::class,'index'])->name('monitoring-dashboard.index');
});
