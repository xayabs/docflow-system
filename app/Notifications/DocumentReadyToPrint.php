<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Models\Document;

class DocumentReadyToPrint extends Notification
{
    use Queueable;

    public $document; // ເພີ່ມຕົວແປນີ້

    public function __construct($document) // ຮັບຄ່າ $document
    {
        $this->document = $document;
    }

    public function via(object $notifiable): array
    {
        // 2. ເພີ່ມ WebPushChannel::class ເຂົ້າໄປຮ່ວມກັບ database ແລະ mail
        return ['database', 'mail', WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ເອກະສານພ້ອມສຳລັບພິມແລ້ວ')
                    ->greeting('ສະບາຍດີ ' . $notifiable->name . ',')
                    ->line('ເອກະສານເລກທີ #' . $this->document->document_code . ' ທີ່ມີຫົວຂໍ້ວ່າ "' . $this->document->title . '" ໄດ້ຮັບການອະນຸມັດ ແລະ ພ້ອມສຳລັບການພິມແລ້ວ.')
                    ->action('ພິມເອກະສານ', $url)
                    ->line('ຂອບໃຈທີ່ໃຊ້ບໍລິການ!');
    }

    /**
     * Get the Web Push representation of the notification.
     */
    public function toWebPush($notifiable, $notification)
    {
        // 3. ສ້າງໂຄງສ້າງ ແລະ ຂໍ້ຄວາມສຳລັບ Push Notification ທີ່ຈະເດັ້ງຂຶ້ນໜ້າຈໍມືຖື
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);

        return (new WebPushMessage)
            ->title('ເອກະສານພ້ອມສຳລັບພິມແລ້ວ!')
            ->icon('/images/icons/icon-192x192.png')
            ->body('ເອກະສານ "' . $this->document->title . '" ໄດ້ຮັບການອະນຸມັດແລ້ວ.')
            ->action('ກວດສອບ', 'open_url')
            ->data(['url' => $url]);
    }

    /*
    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id, // ດຽວນີ້ $this->document ຈະມີຄ່າແລ້ວ
            'title' => $this->document->title,
            'message' => 'ເອກະສານ "' . $this->document->title . '" ໄດ້ອະນຸມັດໃຫ້ດຳເນີນການໄດ້ແລ້ວ, ຈົ່ງພິມເອກະສານດັ່ງກ່າວສົ່ງໃຫ້ເລຂາຄະນະວິຊາ.',
            'url' => route('staff.documents.show', $this->document->id),
        ];
    }*/
    // ໃນ app/Notifications/DocumentReadyToPrint.php

    public function toArray(object $notifiable): array
    {
        // 1. ກວດສອບ Role ຂອງຜູ້ຮັບການແຈ້ງເຕືອນ ($notifiable)
        $userRole = $notifiable->role ? $notifiable->role->name : '';
        
        // 2. ສ້າງ URL ໃຫ້ຕົງກັບ Role ຂອງຜູ້ຮັບ
        if ($userRole === 'Procurement_Staff') {
            // ຖ້າຜູ້ສະເໜີແມ່ນ ຝ່າຍຈັດຊື້ ໃຫ້ຊີ້ໄປໜ້າຂອງຝ່າຍຈັດຊື້
            $url = route('procurement.documents.show', $this->document->id);
        } else {
            // ຖ້າແມ່ນ Staff ທຳມະດາ ໃຫ້ຊີ້ໄປໜ້າຂອງ Staff
            $url = route('staff.documents.show', $this->document->id);
        }

        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'ເອກະສານ "' . $this->document->title . '" ໄດ້ອະນຸມັດໃຫ້ດຳເນີນການໄດ້ແລ້ວ, ຈົ່ງພິມເອກະສານດັ່ງກ່າວສົ່ງໃຫ້ເລຂາຄະນະວິຊາ.',
            'url' => $url, // <-- ໃຊ້ URL ແບບ Dynamic ທີ່ແຍກຕາມ Role
        ];
    }
}
