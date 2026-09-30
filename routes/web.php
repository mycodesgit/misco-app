<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/dashboard',[MonitoringDashboardController::class,'index'])->name('dashboard.index');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::get('/dashboard/monitoring',[MonitoringDashboardController::class,'index'])->name('monitoring-dashboard.index');
});
