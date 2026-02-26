<?php

use App\Http\Controllers\Account\NotificationController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Board\GroupController as BoardGroupController;
use App\Http\Controllers\Board\TaskController as BoardTaskController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\Client\ClientCompanyController;
use App\Http\Controllers\Client\ClientUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Doc\DocController;
use App\Http\Controllers\DropdownValuesController;
use App\Http\Controllers\Invoice\InvoiceTasksController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Monday\MondayIntegrationController;
use App\Http\Controllers\MyWork\ActivityController;
use App\Http\Controllers\MyWork\MyWorkTaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectWorkspaceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Settings\LabelController;
use App\Http\Controllers\Settings\OwnerCompanyController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\TaskPriorityController;
use App\Http\Controllers\Task\AttachmentController;
use App\Http\Controllers\Task\CommentController;
use App\Http\Controllers\Task\TimeLogController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard');

Route::group(['middleware' => ['auth:sanctum']], function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class)->except(['show']);

    Route::group(['prefix' => 'projects', 'as' => 'projects.'], function () {
        // PROJECT
        Route::post('{projectId}/restore', [ProjectController::class, 'restore'])->name('restore');
        Route::put('{project}/favorite/toggle', [ProjectController::class, 'favoriteToggle'])->name('favorite.toggle');
        Route::post('{project}/user-access', [ProjectController::class, 'userAccess'])->name('user_access');

        // BOARDS
        Route::post('{project}/boards', [BoardController::class, 'store'])->name('boards.store');
        Route::get('{project}/boards/{board}', [BoardController::class, 'show'])->name('boards.show')->scopeBindings();
        Route::get('{project}/boards/{board}/tasks/{task}/open', [BoardController::class, 'show'])->name('boards.tasks.open')->scopeBindings();
        Route::put('{project}/boards/{board}', [BoardController::class, 'update'])->name('boards.update')->scopeBindings();
        Route::delete('{project}/boards/{board}', [BoardController::class, 'destroy'])->name('boards.destroy')->scopeBindings();

        // MONDAY INTEGRATIONS
        Route::get('{project}/boards/{board}/integrations/monday', [MondayIntegrationController::class, 'show'])
            ->name('boards.integrations.monday.show')->scopeBindings();
        Route::post('{project}/boards/{board}/integrations/monday', [MondayIntegrationController::class, 'store'])
            ->name('boards.integrations.monday.store')->scopeBindings();
        Route::delete('{project}/boards/{board}/integrations/monday', [MondayIntegrationController::class, 'destroy'])
            ->name('boards.integrations.monday.destroy')->scopeBindings();

        // TASK GROUPS (scoped to boards)
        Route::post('{project}/boards/{board}/task-groups', [BoardGroupController::class, 'store'])->name('boards.task-groups.store')->scopeBindings();
        Route::put('{project}/boards/{board}/task-groups/{taskGroup}', [BoardGroupController::class, 'update'])->name('boards.task-groups.update')->scopeBindings();
        Route::delete('{project}/boards/{board}/task-groups/{taskGroup}', [BoardGroupController::class, 'destroy'])->name('boards.task-groups.destroy')->scopeBindings();
        Route::post('{project}/boards/{board}/task-groups/{taskGroupId}/restore', [BoardGroupController::class, 'restore'])->name('boards.task-groups.restore')->scopeBindings();
        Route::post('{project}/boards/{board}/task-groups/reorder', [BoardGroupController::class, 'reorder'])->name('boards.task-groups.reorder')->scopeBindings();

        // TASKS (board-aware store)
        Route::post('{project}/boards/{board}/tasks', [BoardTaskController::class, 'store'])->name('boards.tasks.store')->scopeBindings();

        // TASKS (project-scoped, stays for update/delete/complete/reorder/move)
        Route::put('{project}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update')->scopeBindings();
        Route::delete('{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy')->scopeBindings();
        Route::post('{project}/tasks/{task}/restore', [TaskController::class, 'restore'])->name('tasks.restore')->scopeBindings();
        Route::post('{project}/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete')->scopeBindings();
        Route::post('{project}/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
        Route::post('{project}/tasks/move', [TaskController::class, 'move'])->name('tasks.move');

        // Backward compatibility: old /projects/{project}/tasks → redirect to default board
        Route::get('{project}/tasks', [TaskController::class, 'index'])->name('tasks');
        Route::get('{project}/tasks/{task}/open', [TaskController::class, 'index'])->name('tasks.open')->scopeBindings();

        // ATTACHMENTS
        Route::group(['prefix' => '{project}/tasks/{task}', 'as' => 'tasks.'], function () {
            Route::post('attachments/upload', [AttachmentController::class, 'store'])->name('attachments.upload');
            Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
        })->scopeBindings();

        // TIME LOGS
        Route::group(['prefix' => '{project}/tasks/{task}', 'as' => 'tasks.'], function () {
            Route::post('time-log', [TimeLogController::class, 'store'])->name('time-logs.store');
            Route::delete('time-log/{timeLog}', [TimeLogController::class, 'destroy'])->name('time-logs.destroy');
            Route::post('time-log/timer/start', [TimeLogController::class, 'startTimer'])->name('time-logs.timer.start');
            Route::post('time-log/{timeLog}/timer/stop', [TimeLogController::class, 'stopTimer'])->name('time-logs.timer.stop');
        })->scopeBindings();

        // COMMENTS
        Route::group(['prefix' => '{project}/tasks/{task}', 'as' => 'tasks.'], function () {
            Route::get('comment', [CommentController::class, 'index'])->name('comments');
            Route::post('comment', [CommentController::class, 'store'])->name('comments.store');
        })->scopeBindings();

        // DOCS
        Route::post('{project}/docs', [DocController::class, 'store'])->name('docs.store');
        Route::get('{project}/docs/{doc}', [DocController::class, 'show'])->name('docs.show')->scopeBindings();
        Route::put('{project}/docs/{doc}', [DocController::class, 'update'])->name('docs.update')->scopeBindings();
        Route::delete('{project}/docs/{doc}', [DocController::class, 'destroy'])->name('docs.destroy')->scopeBindings();

        // PROJECT SHOW (workspace redirect, must be AFTER all other project sub-routes)
        Route::get('{project}', [ProjectWorkspaceController::class, 'show'])->name('show');
    });

    // My Work
    Route::group(['prefix' => 'my-work', 'as' => 'my-work.'], function () {
        Route::get('tasks', [MyWorkTaskController::class, 'index'])->name('tasks.index');
        Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
    });

    // Clients
    Route::group(['prefix' => 'clients', 'as' => 'clients.'], function () {
        Route::resource('users', ClientUserController::class)->except(['show']);
        Route::post('users/{userId}/restore', [ClientUserController::class, 'restore'])->name('users.restore');

        Route::resource('companies', ClientCompanyController::class)->except(['show']);
        Route::post('companies/{companyId}/restore', [ClientCompanyController::class, 'restore'])->name('companies.restore');
    });

    // Users
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('users/{userId}/restore', [UserController::class, 'restore'])->name('users.restore');

    // Invoices
    Route::resource('invoices', InvoiceController::class)->except(['show']);
    Route::group(['prefix' => 'invoices', 'as' => 'invoices.'], function () {
        Route::get('tasks', [InvoiceTasksController::class, 'index'])->name('tasks');
        Route::put('{invoice}/status', [InvoiceController::class, 'setStatus'])->name('status');
        Route::post('{invoice}/restore', [InvoiceController::class, 'restore'])->name('restore');
        Route::get('{invoice}/download', [InvoiceController::class, 'download'])->name('download');
        Route::get('{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('pdf');
    });

    // Reports
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::get('logged-time/sum', [ReportController::class, 'loggedTimeSum'])->name('logged-time.sum');
        Route::get('logged-time/daily', [ReportController::class, 'dailyLoggedTime'])->name('logged-time.daily');
        Route::get('fixed-price/sum', [ReportController::class, 'fixedPriceSum'])->name('fixed-price.sum');
    });

    // Settings
    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::get('company', [OwnerCompanyController::class, 'edit'])->name('company.edit');
        Route::put('company', [OwnerCompanyController::class, 'update'])->name('company.update');

        Route::resource('roles', RoleController::class)->except(['show']);
        Route::post('roles/{roleId}/restore', [RoleController::class, 'restore'])->name('roles.restore');

        Route::resource('labels', LabelController::class)->except(['show']);
        Route::post('labels/{labelId}/restore', [LabelController::class, 'restore'])->name('labels.restore');

        Route::resource('task-priorities', TaskPriorityController::class)->except(['show']);
        Route::post('task-priorities/{priorityId}/restore', [TaskPriorityController::class, 'restore'])->name('task-priorities.restore');
    });

    // Account
    Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::put('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::put('notifications/read/all', [NotificationController::class, 'readAll'])->name('notifications.read.all');

    Route::get('dropdown/values', DropdownValuesController::class)->name('dropdown.values');
});
