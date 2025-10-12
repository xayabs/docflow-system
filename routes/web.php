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

Route::get('/', function () {
    return view('welcome');
});

// ກຸ່ມຂອງ Route ທັງໝົດທີ່ຕ້ອງ Login ກ່ອນຈຶ່ງຈະເຂົ້າໄດ້
//Route::middleware(['auth', 'verified'])->group(function () {
Route::prefix('api')->middleware(['auth', 'verified'])->group(function () {

    // Route ສຳລັບໜ້າ Dashboard ຫຼັກ (ສຳລັບທຸກຄົນທີ່ Login)
    Route::get('/dashboard', function () {
        // ດຶງຂໍ້ມູນ Role ຂອງຜູ້ໃຊ້ທີ່ກຳລັງ Login
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
                // Fallback สำหรับ Role ที่ยังไม่มี Dashboard
                return view('dashboard');
        }
    })->name('dashboard');

    Route::middleware('role:System_Admin,Head_of_Finance,Dean,Vice_Dean')->prefix('reports')->name('reports.')->group(function () {
        Route::get('documents', [\App\Http\Controllers\ReportController::class, 'index'])->name('documents.index');
        Route::get('documents/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('documents.export');
    });
    
    // Route ສຳລັບໜ້າ "ຈັດການຜູ້ໃຊ້" (ສະເພາະ Admin)
    Route::middleware('role:System_Admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
        Route::resource('document-types', \App\Http\Controllers\Admin\DocumentTypeController::class);
    });
    
    // Route Group ສໍາຫຼັບ Staff
    Route::middleware('role:Staff')->prefix('staff')->name('staff.')->group(function () {
        Route::resource('documents', \App\Http\Controllers\DocumentController::class);

        Route::get('history/approved', [\App\Http\Controllers\DocumentController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [\App\Http\Controllers\DocumentController::class, 'rejectedHistory'])->name('history.rejected');

        Route::get('documents/{document}/print', [\App\Http\Controllers\DocumentController::class, 'print'])->name('documents.print');
        Route::patch('documents/{document}/submit', [\App\Http\Controllers\DocumentController::class, 'submitDraft'])->name('documents.submit');
    });

    Route::middleware('role:Dean_Secretary')->prefix('secretary')->name('secretary.')->group(function () {
        // Route ສຳລັບ Dashboard ຂອງເລຂາ
        Route::get('dashboard', [\App\Http\Controllers\Secretary\DashboardController::class, 'index'])->name('dashboard');
    
        // Route ສຳລັບໜ້າກວດສອບເອກະສານ
        Route::get('documents/{document}', [\App\Http\Controllers\Secretary\DashboardController::class, 'show'])->name('documents.show');
    
        // Route ສຳລັບການອະນຸມັດ (Approve)
        Route::post('documents/{document}/approve', [\App\Http\Controllers\Secretary\DashboardController::class, 'approve'])->name('documents.approve');
    
        // Route ສຳລັບການປະຕິເສດ (Reject)
        Route::post('documents/{document}/reject', [\App\Http\Controllers\Secretary\DashboardController::class, 'reject'])->name('documents.reject');

        Route::get('history/approved', [\App\Http\Controllers\Secretary\DashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [\App\Http\Controllers\Secretary\DashboardController::class, 'rejectedHistory'])->name('history.rejected');
        Route::post('documents/{document}/process', [\App\Http\Controllers\Secretary\DashboardController::class, 'process'])->name('documents.process');
    });

    // Route Group ສຳລັບ Finance Preparer
    Route::middleware('role:Finance_Preparer')->prefix('finance/preparer')->name('finance.preparer.')->group(function () {
        // Route ສຳລັບ Dashboard ຝ່າຍກະກຽມເອກະສານຈົດຈ່າຍ
        Route::get('dashboard', [\App\Http\Controllers\Finance\PreparerDashboardController::class, 'index'])->name('dashboard');

        // Route ສຳລັບໜ້າກວດສອບເອກະສານ
        Route::get('documents/{document}', [\App\Http\Controllers\Finance\PreparerDashboardController::class, 'show'])->name('documents.show');
    
        // Route ສຳລັບການອະນຸມັດ (Approve)
        Route::post('documents/{document}/approve', [\App\Http\Controllers\Finance\PreparerDashboardController::class, 'approve'])->name('documents.approve');
    
        // Route ສຳລັບການປະຕິເສດ (Reject)
        Route::post('documents/{document}/reject', [\App\Http\Controllers\Finance\PreparerDashboardController::class, 'reject'])->name('documents.reject');

        Route::get('history/approved', [\App\Http\Controllers\Finance\PreparerDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [\App\Http\Controllers\Finance\PreparerDashboardController::class, 'rejectedHistory'])->name('history.rejected');
        
        Route::post('documents/{document}/process', [\App\Http\Controllers\Finance\PreparerDashboardController::class, 'process'])->name('documents.process');
    });

    Route::middleware('role:Accountant')->prefix('accountant')->name('accountant.')->group(function () {
        // Route ສຳລັບ Dashboard ຝ່າຍບັນຊີ
        Route::get('dashboard', [\App\Http\Controllers\Accountant\AccDashboardController::class, 'index'])->name('dashboard');

        // Route ສຳລັບໜ້າກວດສອບເອກະສານ
        Route::get('documents/{document}', [\App\Http\Controllers\Accountant\AccDashboardController::class, 'show'])->name('documents.show');
    
        // Route ສຳລັບການອະນຸມັດ (Approve)
        Route::post('documents/{document}/approve', [\App\Http\Controllers\Accountant\AccDashboardController::class, 'approve'])->name('documents.approve');
    
        // Route ສຳລັບການປະຕິເສດ (Reject)
        Route::post('documents/{document}/reject', [\App\Http\Controllers\Accountant\AccDashboardController::class, 'reject'])->name('documents.reject');

        Route::get('history/approved', [\App\Http\Controllers\Accountant\AccDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [\App\Http\Controllers\Accountant\AccDashboardController::class, 'rejectedHistory'])->name('history.rejected');
        Route::post('documents/{document}/process', [\App\Http\Controllers\Accountant\AccDashboardController::class, 'process'])->name('documents.process');
    });

    Route::middleware('role:Vice_Dean')->prefix('vicedean')->name('vicedean.')->group(function () {
        // Route ສຳລັບ Dashboard ຝ່າຍບັນຊີ
        Route::get('dashboard', [\App\Http\Controllers\ViceDean\VDDashboardController::class, 'index'])->name('dashboard');

        // Route ສຳລັບໜ້າກວດສອບເອກະສານ
        Route::get('documents/{document}', [\App\Http\Controllers\ViceDean\VDDashboardController::class, 'show'])->name('documents.show');
    
        // Route ສຳລັບການອະນຸມັດ (Approve)
        Route::post('documents/{document}/approve', [\App\Http\Controllers\ViceDean\VDDashboardController::class, 'approve'])->name('documents.approve');
    
        // Route ສຳລັບການປະຕິເສດ (Reject)
        Route::post('documents/{document}/reject', [\App\Http\Controllers\ViceDean\VDDashboardController::class, 'reject'])->name('documents.reject');

        Route::get('history/approved', [\App\Http\Controllers\ViceDean\VDDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [\App\Http\Controllers\ViceDean\VDDashboardController::class, 'rejectedHistory'])->name('history.rejected');
    });

    Route::middleware('role:Head_of_Finance')->prefix('headfinance')->name('headfinance.')->group(function () {
        // Route ສຳລັບ Dashboard ຝ່າຍບັນຊີ
        Route::get('dashboard', [\App\Http\Controllers\HeadFinance\HFDashboardController::class, 'index'])->name('dashboard');

        // Route ສຳລັບໜ້າກວດສອບເອກະສານ
        Route::get('documents/{document}', [\App\Http\Controllers\HeadFinance\HFDashboardController::class, 'show'])->name('documents.show');
    
        // Route ສຳລັບການອະນຸມັດ (Approve)
        Route::post('documents/{document}/approve', [\App\Http\Controllers\HeadFinance\HFDashboardController::class, 'approve'])->name('documents.approve');
    
        // Route ສຳລັບການປະຕິເສດ (Reject)
        //Route::post('documents/{document}/reject', [\App\Http\Controllers\HeadFinance\HFDashboardController::class, 'reject'])->name('documents.reject');

        Route::get('history/approved', [\App\Http\Controllers\HeadFinance\HFDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [\App\Http\Controllers\HeadFinance\HFDashboardController::class, 'rejectedHistory'])->name('history.rejected');
        Route::post('documents/{document}/process', [\App\Http\Controllers\HeadFinance\HFDashboardController::class, 'process'])->name('documents.process');
        Route::post('documents/{document}/return', [\App\Http\Controllers\HeadFinance\HFDashboardController::class, 'returnToAccountant'])->name('documents.return');
    });

    Route::middleware('role:Dean')->prefix('dean')->name('dean.')->group(function () {
        // Route ສຳລັບ Dashboard ຄະນະບໍດີ
        Route::get('dashboard', [\App\Http\Controllers\Dean\DeanDashboardController::class, 'index'])->name('dashboard');

        // Route ສຳລັບໜ້າກວດສອບເອກະສານ
        Route::get('documents/{document}', [\App\Http\Controllers\Dean\DeanDashboardController::class, 'show'])->name('documents.show');
    
        // Route ສຳລັບການອະນຸມັດ (Approve)
        Route::post('documents/{document}/approve', [\App\Http\Controllers\Dean\DeanDashboardController::class, 'approve'])->name('documents.approve');
    
        // Route ສຳລັບການປະຕິເສດ (Reject)
        Route::post('documents/{document}/reject', [\App\Http\Controllers\Dean\DeanDashboardController::class, 'reject'])->name('documents.reject');

        Route::get('history/approved', [\App\Http\Controllers\Dean\DeanDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [\App\Http\Controllers\Dean\DeanDashboardController::class, 'rejectedHistory'])->name('history.rejected');
    });

    // Route Group ສໍາຫຼັບ Cashier
    Route::middleware('role:Cashier')->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Cashier\CashDashboardController::class, 'index'])->name('dashboard');
        Route::get('documents/{document}', [\App\Http\Controllers\Cashier\CashDashboardController::class, 'show'])->name('documents.show');
        Route::post('documents/{document}/confirm-payment', [\App\Http\Controllers\Cashier\CashDashboardController::class, 'confirmPayment'])->name('documents.confirmPayment');

        Route::get('history/approved', [\App\Http\Controllers\Cashier\CashDashboardController::class, 'approvedHistory'])->name('history.approved');
    });

    Route::middleware('role:Procurement_Staff')->prefix('procurement')->name('procurement.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('documents/{document}', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'show'])->name('documents.show');

        // ເຮົາຈະສ້າງ Route ສຳລັບ Action ຕ່າງໆຢູ່ບ່ອນນີ້
        Route::post('documents/{document}/start-process', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'startProcess'])->name('documents.startProcess');
        Route::post('documents/{document}/complete-purchase', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'completePurchase'])->name('documents.completePurchase');
        // ປ່ຽນເປັນ GET ເພື່ອຄວາມງ່າຍດາຍໃນການສ້າງລິ້ງ
        Route::get('documents/{document}/create-payment-request', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'createPaymentRequest'])->name('documents.createPaymentRequest.form');
        Route::post('documents/store-payment-request', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'storePaymentRequest'])->name('documents.storePaymentRequest');

        Route::get('history/approved', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'approvedHistory'])->name('history.approved');
        Route::get('history/rejected', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'rejectedHistory'])->name('history.rejected');

        Route::get('documents/{document}/print', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'print'])->name('documents.print');

         Route::get('documents/{document}/edit', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'edit'])->name('documents.edit');
        Route::patch('documents/{document}', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'update'])->name('documents.update');
        Route::patch('documents/{document}/submit', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'submitDraft'])->name('documents.submit');
        Route::delete('documents/{document}', [\App\Http\Controllers\Procurement\ProcDashboardController::class, 'destroy'])->name('documents.destroy');
    });

    // Route ຂອງ Profile ຍັງຄົງຢູ່ຂ້າງໃນນີ້
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==========================================================
    // ===== Route สำหรับ Notification (สำหรับผู้ใช้ทุกคนที่ Login) =====
    // ==========================================================
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}/read', [NotificationController::class, 'markAsReadAndRedirect'])->name('notifications.read');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    // ==========================================================
});

require __DIR__.'/auth.php';
