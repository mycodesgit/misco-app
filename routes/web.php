<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SupportTicketRequestController;
use App\Http\Controllers\RequesterTicketRequestController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategorySubController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\AccomplishmentReportController;
use App\Http\Controllers\TicketStatusReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRolesController;
use App\Http\Controllers\LogoutController;

use App\Http\Controllers\MonitoringDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ClientFeedbackController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\WifiVoucherController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectKanbanController;
use App\Http\Controllers\TicketArchiveController;
use App\Http\Controllers\CalendarController;
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
    Route::post('/login',[LoginController::class,'postLogin'])->middleware('throttle:login')->name('postLogin');
});

Route::group(['middleware'=>['login_auth']],function(){
    Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard.index');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::prefix('/tickets')->group(function () {
        // "All Tickets" of the user's desk — desk staff only
        Route::middleware('feature:desk_staff')->group(function () {
            Route::get('/support/view/all',[SupportTicketRequestController::class,'index'])->name('tickets.index');
            Route::get('/support/view/all/fetch/pending',[SupportTicketRequestController::class,'showsupportpending'])->name('tickets.showsupportpending');
            Route::get('/support/view/all/fetch/progress',[SupportTicketRequestController::class,'showprogress'])->name('tickets.showprogress');
            Route::get('/support/view/all/fetch/resolved',[SupportTicketRequestController::class,'showresolved'])->name('tickets.showresolved');
            Route::get('/support/view/all/fetch/closed',[SupportTicketRequestController::class,'showclosed'])->name('tickets.showclosed');
            Route::get('/support/view/all/fetch/mine',[SupportTicketRequestController::class,'showmine'])->name('tickets.showmine');
        });

        // Archive: finished tickets from past years — every role
        Route::get('/archive', [TicketArchiveController::class, 'index'])->name('tickets.archive');
        Route::get('/archive/fetch', [TicketArchiveController::class, 'show'])->name('tickets.archive.show');
        Route::get('/support/view/details',[SupportTicketRequestController::class,'store'])->name('tickets.store');
        Route::post('/ticket-requests/{id}/status', [SupportTicketRequestController::class, 'updateStatus'])->name('tickets.update-status');

        Route::post('/ticket/chat/send', [SupportTicketRequestController::class, 'sendMessage'])->name('ticket.chat.send');
        Route::get('/ticket/chat/fetch/{ticketId}', [SupportTicketRequestController::class, 'fetchMessages'])->name('ticket.chat.fetch');

        Route::get('/requester/view/all',[RequesterTicketRequestController::class,'index'])->name('ticketsrequester.index');
        Route::get('/requester/tickets/categories/{supportType}', [RequesterTicketRequestController::class, 'getCat'])->name('tickets.categories');
        Route::get('/requester/tickets/subcategories/{category}', [RequesterTicketRequestController::class, 'getSubcat'])->name('tickets.subcategories');
        Route::get('/requester/tickets/assigned-personnel/{category}', [RequesterTicketRequestController::class, 'getassignedPersonnel'])->name('tickets.assignedPersonnel');
        Route::post('/requester/tickets/submit/ticket', [RequesterTicketRequestController::class, 'create'])->name('ticketsrequester.create');
        Route::get('/requester/tickets/edit/{id}', [RequesterTicketRequestController::class, 'edit'])->name('ticketsrequester.edit');
        Route::post('/requester/tickets/update', [RequesterTicketRequestController::class, 'update'])->name('ticketsrequester.update');
        Route::post('/requester/tickets/reopen', [RequesterTicketRequestController::class, 'reopen'])->name('ticketsrequester.reopen');
        Route::get('/requester/view/all/fetch/pending',[RequesterTicketRequestController::class,'showreqpending'])->name('tickets.showreqpending');
        Route::get('/requester/view/all/fetch/progress',[RequesterTicketRequestController::class,'showreqprogress'])->name('tickets.showreqprogress');
        Route::get('/requester/view/all/fetch/resolved',[RequesterTicketRequestController::class,'showreqresolved'])->name('tickets.showreqresolved');
        Route::get('/requester/view/all/fetch/closed',[RequesterTicketRequestController::class,'showreqclosed'])->name('tickets.showreqclosed');
        Route::get('/requester/tickets/feedback/{ticketId}', [RequesterTicketRequestController::class, 'getFeedback'])->name('ticketsrequester.feedback.show');
        Route::post('/requester/tickets/submit/feedback', [RequesterTicketRequestController::class, 'submitFeedback'])->name('ticketsrequester.feedback.submit');

    });

    Route::prefix('/daily-task')->middleware('feature:daily_task')->group(function () {
        Route::get('/view', [DailyTaskController::class, 'index'])->name('daily-task.index');
        Route::get('/get-subcategories/{categoryId}', [DailyTaskController::class, 'getSubcategories'])->name('daily-task.getSubcategories');
        Route::post('/create', [DailyTaskController::class, 'create'])->name('daily-task.create');
        Route::get('/show', [DailyTaskController::class, 'show'])->name('daily-task.show');
        Route::post('/update', [DailyTaskController::class, 'update'])->name('daily-task.update');
    });

    Route::prefix('/manage')->group(function () {
        // Desk staff manage their own office's categories (scoped in the controllers)
        Route::middleware('feature:category')->group(function () {
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

        // Offices and Audit Trail — Administrators only
        Route::middleware('feature:admin')->group(function () {
            Route::get('/office/list', [OfficeController::class, 'index'])->name('office.index');
            Route::get('/office/list/fetch', [OfficeController::class, 'show'])->name('office.show');
            Route::post('/office/list/add', [OfficeController::class, 'create'])->name('office.create');
            Route::post('/office/list/update', [OfficeController::class, 'update'])->name('office.update');

            Route::get('/audittrail/view', [AuditTrailController::class, 'index'])->name('audittrail.index');
            Route::get('/audittrail/fetch', [AuditTrailController::class, 'show'])->name('audittrail.show');
        });
    });

    Route::prefix('/reports')->group(function () {
        Route::middleware('feature:daily_task')->group(function () {
            Route::get('/accomplishment/generate', [AccomplishmentReportController::class, 'index'])->name('accomplishment-report.index');
            Route::get('/accomplishment/preview', [AccomplishmentReportController::class, 'previewPdf'])->name('accomplishment.preview');
        });
        Route::middleware('feature:desk_staff')->group(function () {
            Route::get('/ticketstatus/view', [TicketStatusReportController::class, 'index'])->name('ticketstatus-report.index');
            Route::get('/ticketstatus/preview', [TicketStatusReportController::class, 'previewPdf'])->name('ticketstatus-report.preview');
            Route::get('/clientfeedback/view', [ClientFeedbackController::class, 'index'])->name('clientfeedback.index');
            Route::get('/clientfeedback/fetch', [ClientFeedbackController::class, 'show'])->name('clientfeedback.show');
            Route::get('/clientfeedback/users', [ClientFeedbackController::class, 'users'])->name('clientfeedback.users');
        });
    });

    // Personal account page — outside /users/* so Requester role can access it
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::post('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
    Route::post('/account/contact', [AccountController::class, 'updateContact'])->name('account.contact');

    Route::prefix('/projects')->middleware('feature:work_progress')->group(function () {
        Route::get('/tracker', [ProjectController::class, 'index'])->name('project.index');
        Route::get('/fetch', [ProjectController::class, 'show'])->name('project.show');
        Route::get('/members/{officeId}', [ProjectController::class, 'membersByOffice'])->name('project.members');
        Route::post('/create', [ProjectController::class, 'store'])->name('project.create');
        Route::post('/update', [ProjectController::class, 'update'])->name('project.update');
        Route::delete('/delete/{id}', [ProjectController::class, 'destroy'])->name('project.delete');

        Route::get('/kanban/{projectId}', [ProjectKanbanController::class, 'board'])->name('project.kanban.board');
        Route::post('/kanban', [ProjectKanbanController::class, 'store'])->name('project.kanban.store');
        Route::post('/kanban/update', [ProjectKanbanController::class, 'update'])->name('project.kanban.update');
        Route::post('/kanban/reorder', [ProjectKanbanController::class, 'reorder'])->name('project.kanban.reorder');
        Route::delete('/kanban/{id}', [ProjectKanbanController::class, 'destroy'])->name('project.kanban.delete');
    });

    // Office Calendar (embedded Google Workspace calendar) — office 36 only
    Route::middleware('feature:calendar')->group(function () {
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::post('/calendar/settings', [CalendarController::class, 'update'])->name('calendar.update');
    });

    // User management and roles — Administrators only
    Route::prefix('/users')->middleware('feature:admin')->group(function () {
        Route::get('/list/view/all/supports',[UserController::class,'index'])->name('user.index');
        Route::get('/list/view/all/requester',[UserController::class,'requesterindex'])->name('requester.index');
        Route::post('/list/view/add',[UserController::class,'create'])->name('user.create');
        Route::get('/list/view/fetch/support',[UserController::class,'show'])->name('user.show');
        Route::get('/list/view/fetch/requester',[UserController::class,'requestershow'])->name('requester.show');
        Route::post('/list/view/update', [UserController::class, 'update'])->name('user.update');
        Route::post('/list/updatePass', [UserController::class, 'userUpdatePassword'])->name('userUpdatePassword');
        Route::post('list/updateStatusnow', [UserController::class, 'userUpdateStatus'])->name('userUpdateStatus');
        Route::post('list/updateTaskassignment', [UserController::class, 'userAssignTaskUpdate'])->name('userAssignTaskUpdate');
    });

    Route::prefix('/roles')->middleware('feature:admin')->group(function () {
        Route::get('/user/view/all',[UserRolesController::class,'index'])->name('roles.index');
        Route::post('/user/view/add',[UserRolesController::class,'create'])->name('roles.create');
        Route::get('/user/view/fetch',[UserRolesController::class,'show'])->name('roles.show');
        Route::post('/user/view/update', [UserRolesController::class, 'update'])->name('roles.update');
    });

    // TV monitoring dashboard — desk staff only
    Route::middleware('feature:desk_staff')->group(function () {
        Route::get('/monitor',[MonitoringDashboardController::class,'index'])->name('monitoring.index');
        Route::get('/dashboard/monitoring',[MonitoringDashboardController::class,'index'])->name('monitoring-dashboard.index');
        Route::get('/dashboard/monitoring/helpdesk',[MonitoringDashboardController::class,'helpdesk'])->name('monitoring.helpdesk');
        Route::get('/dashboard/monitoring/personnel',[MonitoringDashboardController::class,'personnel'])->name('monitoring.personnel');
        Route::get('/dashboard/monitoring/queue',[MonitoringDashboardController::class,'queue'])->name('monitoring.queue');
        Route::get('/dashboard/monitoring/evaluation',[MonitoringDashboardController::class,'evaluation'])->name('monitoring.evaluation');
        Route::get('/dashboard/monitoring/wifi',[MonitoringDashboardController::class,'wifi'])->name('monitoring.wifi');
    });

    Route::prefix('/station')->group(function () {
        Route::get('/', [StationController::class, 'show'])->name('station.show');
        Route::post('/update', [StationController::class, 'update'])->name('station.update');
    });

    Route::prefix('/wifi')->middleware('feature:wifi_voucher')->group(function () {
        Route::get('/', [WifiVoucherController::class, 'index'])->name('wifi.index');
        Route::get('/fetch', [WifiVoucherController::class, 'show'])->name('wifi.show');
        Route::post('/upload', [WifiVoucherController::class, 'upload'])->name('wifi.upload');
        Route::post('/status', [WifiVoucherController::class, 'updateStatus'])->name('wifi.status');
        Route::post('/delete/{id}', [WifiVoucherController::class, 'delete'])->name('wifi.delete');
    });

    Route::prefix('/notifications')->group(function () {
        Route::get('/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
        Route::post('/read/{id}', [NotificationController::class, 'read'])->name('notifications.read');
        Route::post('/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
        Route::post('/read-ticket', [NotificationController::class, 'readTicket'])->name('notifications.readTicket');
    });
});
