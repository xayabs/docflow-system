<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // ใน NotificationController.php

    /**
    * ສະແດງໜ້າການແຈ້ງເຕືອນທັງໝົດ.
    */
    public function index()
    {
        // ດືງການແຈ້ງເຕືອນທັງໝົດຂອງຜູ້ໃຊ້, ແບ່ງໜ້າ, 15 ລາຍການຕໍ່ໜ້າ
        $notifications = Auth::user()
                         ->notifications()
                         ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
    * ເຮັດເຄືອງໝາຍການແຈ້ງເຕືອນທັງໝົດວ່າອ່ານແລ້ວ.
    */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'ການແຈ້ງເຕືອນັງໝົດຖືກເຮັດເຄື່ອງໝາຍວ່າອ່ານແລ້ວ.');
    }

    public function markAsReadAndRedirect($notificationId)
    {
        // 1. ຄົ້ນຫາ Notification ຜູ້ໃຊ້ທີ່ Login ຢູ່
        $notification = Auth::user()
                            ->notifications()
                            ->where('id', $notificationId)
                            ->first();

        if ($notification) {
            // 2. ເຮັດເຄື່ອງໝາຍວ່າອ່ານແລ້ວ
            $notification->markAsRead();

            // 3. ດືງ URL ຈາກຂໍ້ມູນ data ແລ້ວ Redirect ໄປ
            // ເຮົາບັນທຶກ URL ໄວ້ໃນ key 'url' ຕອນສ້າງ Notification
            return redirect($notification->data['url']);
        }

        // ຖ້າຫາບໍ່ພົບ ຫຼື ບໍ່ແມ່ນຈຂອງຕົວເອງ, ໃຫ້ກັບໄປຫາ Dashboard
        return redirect()->route('dashboard');
    }
}
