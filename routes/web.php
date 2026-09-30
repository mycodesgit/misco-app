<?php

use Illuminate\Support\Facades\Route;

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
        return view('pages.monitor');
    });

    Route::get('/dashboard/monitoring',[MonitoringDashboardController::class,'index'])->name('monitoring-dashboard.index');
});
