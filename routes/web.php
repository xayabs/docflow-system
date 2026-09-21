<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Secretary\DashboardController;
use App\Http\Controllers\Finance\PreparerDashboardController;
use App\Http\Controllers\Accountant\AccDashboardController;
use App\Http\Controllers\ViceDean\VDDashboardController;
use App\Http\Controllers\HeadFinance\HFDashboardController;
use App\Http\Controllers\Dean\DeanDashboardController;
use App\Http\Controllers\Cashier\CashDashboardController;
use App\Http\Controllers\Procurement\ProcDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController; 
use App\Http\Controllers\PushSubscriptionController;

Route::get('/', function () {
    return view('welcome');
});

// ກຸ່ມຂອງ Route ທັງໝົດທີ່ຕ້ອງ Login ກ່ອນຈຶ່ງຈະເຂົ້າໄດ້ (ບໍ່ໃສ່ prefix api)
Route::middleware(['auth', 'verified'])->group(function () {

    // Route ສຳລັບໜ້າ Dashboard ຫຼັກ
    Route::get('/dashboard', function () {
        $userRole = auth()->user()->role->name;
        switch ($userRole) {
            case 'System_Admin':
                return redirect()->route('admin.users.index');
            case 'Staff':
                return redirect()->route('staff.documents.index');
            case 'Dean_Secretary':
                return redirect()->route('secretary.dashboard');
            case 'Finance_Preparer':
                return redirect()->route('finance.preparer.dashboard');
            case 'Accountant':
                return redirect()->route('accountant.dashboard');
            case 'Vice_Dean':
                return redirect()->route('vicedean.dashboard');
            case 'Head_of_Finance':
                return redirect()->route('headfinance.dashboard');
            case 'Dean':
                return redirect()->route('dean.dashboard');
            case 'Cashier':
                return redirect()->route('cashier.dashboard');
            case 'Procurement_Staff':
                return redirect()->route('procurement.dashboard');
            default:
                return view('dashboard');
        }
    })->name('dashboard');

    // ລາຍງານ (Reports)
    Route::middleware('role:System_Admin,Head_of_Finance,Dean,Vice_Dean')->prefix('reports')->name('reports.')->group(function () {
        Route::get('documents', [ReportController::class, 'index'])->name('documents.index');
        Route::get('documents/export', [ReportController::class, 'export'])->name('documents.export');
    });
    
    // ຈັດການຂໍ້ມູນລະບົບ (Admin)
    Route::middleware('role:System_Admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
        Route::resource('document-types', \App\Http\Controllers\Admin\DocumentTypeController::class);
    });
    
    // ພະນັກງານທົ່ວໄປ (Staff)
    Route::middleware('role:Staff')->prefix('staff')->name('staff.')->group(function () {
        Route::resource('documents', \App\Http\Controllers\DocumentController::class);
        Route::get('history/approved', [\App\Http\Controllers\DocumentController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [\App\Http\Controllers\DocumentController::class, 'rejectedHistory'])->name('history.rejected');
        Route::get('documents/{document}/print', [\App\Http\Controllers\DocumentController::class, 'print'])->name('documents.print');
        Route::patch('documents/{document}/submit', [\App\Http\Controllers\DocumentController::class, 'submitDraft'])->name('documents.submit');
    });

    // ເລຂາຄະນະ (Dean_Secretary)
    Route::middleware('role:Dean_Secretary')->prefix('secretary')->name('secretary.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('documents/{document}', [DashboardController::class, 'show'])->name('documents.show');
        Route::post('documents/{document}/process', [DashboardController::class, 'process'])->name('documents.process');
        Route::get('history/approved', [DashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [DashboardController::class, 'rejectedHistory'])->name('history.rejected');
        Route::get('history/approved/export/{type}', [DashboardController::class, 'exportApprovedHistory'])->name('history.approved.export');
    });

    // ຝ່າຍກະກຽມເອກະສານ (Finance_Preparer)
    Route::middleware('role:Finance_Preparer')->prefix('finance/preparer')->name('finance.preparer.')->group(function () {
        Route::get('dashboard', [PreparerDashboardController::class, 'index'])->name('dashboard');
        Route::get('documents/{document}', [PreparerDashboardController::class, 'show'])->name('documents.show');
        Route::post('documents/{document}/process', [PreparerDashboardController::class, 'process'])->name('documents.process');
        Route::get('history/approved', [PreparerDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [PreparerDashboardController::class, 'rejectedHistory'])->name('history.rejected');
    });

    // ນາຍບັນຊີ (Accountant)
    Route::middleware('role:Accountant')->prefix('accountant')->name('accountant.')->group(function () {
        Route::get('dashboard', [AccDashboardController::class, 'index'])->name('dashboard');
        Route::get('documents/{document}', [AccDashboardController::class, 'show'])->name('documents.show');
        Route::post('documents/{document}/process', [AccDashboardController::class, 'process'])->name('documents.process');
        Route::get('history/approved', [AccDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [AccDashboardController::class, 'rejectedHistory'])->name('history.rejected');
    });

    // ຮອງຄະນະບໍດີ (Vice_Dean)
    Route::middleware('role:Vice_Dean')->prefix('vicedean')->name('vicedean.')->group(function () {
        Route::get('dashboard', [VDDashboardController::class, 'index'])->name('dashboard');
        Route::get('documents/{document}', [VDDashboardController::class, 'show'])->name('documents.show');
        Route::post('documents/{document}/approve', [VDDashboardController::class, 'approve'])->name('documents.approve');
        Route::post('documents/{document}/reject', [VDDashboardController::class, 'reject'])->name('documents.reject');
        Route::get('history/approved', [VDDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [VDDashboardController::class, 'rejectedHistory'])->name('history.rejected');
        Route::get('all-documents', [VDDashboardController::class, 'allDocuments'])->name('documents.all');
    });

    // ຫົວໜ້າພະແນກການເງິນ (Head_of_Finance)
    Route::middleware('role:Head_of_Finance')->prefix('headfinance')->name('headfinance.')->group(function () {
        Route::get('dashboard', [HFDashboardController::class, 'index'])->name('dashboard');
        Route::get('documents/{document}', [HFDashboardController::class, 'show'])->name('documents.show');
        Route::post('documents/{document}/process', [HFDashboardController::class, 'process'])->name('documents.process');
        Route::get('history/approved', [HFDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [HFDashboardController::class, 'rejectedHistory'])->name('history.rejected');
        Route::get('all-documents', [HFDashboardController::class, 'allDocuments'])->name('documents.all');
    });

    // ຄະນະບໍດີ (Dean)
    Route::middleware('role:Dean')->prefix('dean')->name('dean.')->group(function () {
        Route::get('dashboard', [DeanDashboardController::class, 'index'])->name('dashboard');
        Route::get('documents/{document}', [DeanDashboardController::class, 'show'])->name('documents.show');
        Route::post('documents/{document}/approve', [DeanDashboardController::class, 'approve'])->name('documents.approve');
        Route::post('documents/{document}/reject', [DeanDashboardController::class, 'reject'])->name('documents.reject');
        Route::get('history/approved', [DeanDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [DeanDashboardController::class, 'rejectedHistory'])->name('history.rejected');
        Route::get('all-documents', [DeanDashboardController::class, 'allDocuments'])->name('documents.all');
    });

    // ຄັງເງິນສົດ (Cashier)
    Route::middleware('role:Cashier')->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('dashboard', [CashDashboardController::class, 'index'])->name('dashboard');
        Route::get('documents/{document}', [CashDashboardController::class, 'show'])->name('documents.show');
        Route::post('documents/{document}/process', [CashDashboardController::class, 'process'])->name('documents.process');
        Route::get('history/approved', [CashDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/withdrawal-slips', [CashDashboardController::class, 'withdrawalSlipsHistory'])->name('history.withdrawalSlips');
    });

    // ຝ່າຍຈັດຊື້/ສ້ອມແປງ (Procurement_Staff)
    Route::middleware('role:Procurement_Staff')->prefix('procurement')->name('procurement.')->group(function () {
        Route::get('dashboard', [ProcDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('documents/{document}', [ProcDashboardController::class, 'show'])->name('documents.show');
        Route::post('documents/{document}/start-process', [ProcDashboardController::class, 'startProcess'])->name('documents.startProcess');
        Route::post('documents/{document}/complete-purchase', [ProcDashboardController::class, 'completePurchase'])->name('documents.completePurchase');
        Route::get('documents/{document}/create-payment-request', [ProcDashboardController::class, 'createPaymentRequest'])->name('documents.createPaymentRequest.form');
        Route::post('documents/store-payment-request', [ProcDashboardController::class, 'storePaymentRequest'])->name('documents.storePaymentRequest');
        Route::get('history/approved', [ProcDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [ProcDashboardController::class, 'rejectedHistory'])->name('history.rejected');
        Route::get('documents/{document}/print', [ProcDashboardController::class, 'print'])->name('documents.print');
        Route::get('documents/{document}/edit', [ProcDashboardController::class, 'edit'])->name('documents.edit');
        Route::patch('documents/{document}', [ProcDashboardController::class, 'update'])->name('documents.update');
        Route::patch('documents/{document}/submit', [ProcDashboardController::class, 'submitDraft'])->name('documents.submit');
        Route::delete('documents/{document}', [ProcDashboardController::class, 'destroy'])->name('documents.destroy');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}/read', [NotificationController::class, 'markAsReadAndRedirect'])->name('notifications.read');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // Web Push Subscriptions
    Route::post('/push-subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
});

require __DIR__.'/auth.php';