<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Document; // Import Document Model
use App\Models\User;     // Import User Model


class DocumentRejected extends Notification
{
    use Queueable;

    protected $document;
    protected $rejector;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, User $rejector, $customMessage = null)
    {
        $this->document = $document;
        $this->rejector = $rejector;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // ສົ່ງຜ່ານຖານຂໍ້ມູນ ແລະ ອີເມວ
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('staff.documents.edit', $this->document->id);

        return (new MailMessage)
                    ->error() // ໃຊ້ .error() ເພື່ອໃຫ້ອີເມວເປັນສີແດງ
                    ->subject('ແຈ້ງການ: ເອກະສານຂອງທ່ານຖືກສົ່ງຄືນ')
                    ->line('ເອກະສານ "' . $this->document->title . '" ໄດ້ຖືກສົ່ງກັບຄືນ ໂດຍ ' . $this->rejector->displayName . '.')
                    ->line('ເຫດຜົນ: ' . $this->document->rejected_reason)
                    ->action('ກວດສອບ ແລະ ແກ້ໄຂເອກະສານ', $url)
                    ->line('ກະລຸນາເຂົ້າສູ່ລະບົບເພື່ອດຳເນີນການແກ້ໄຂ.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    /*
    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'ເອກະສານຂອງທ່ານຖືກປະຕິເສດໂດຍ ' . $this->rejector->name . '. ກະລຸນາກວດສອບ.',
            'url' => route('staff.documents.edit', $this->document->id) // ຊີ້ໄປໜ້າແກ້ໄຂ
        ];
    }*/

    public function toArray(object $notifiable): array
    {
        $userRole = $notifiable->role->name;
        $url = '#';

        if ($userRole === 'Staff') {
            $url = route('staff.documents.edit', $this->document->id);
        } elseif ($userRole === 'Procurement_Staff') {
            // !!! ชี้ไปที่ Route ของ Procurement !!!
            $url = route('procurement.documents.edit', $this->document->id);
        }
        
        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'ເອກະສານຂອງທ່ານ ກ່ຽວກັບ "' . $this->document->title . '" ໄດ້ຖືກສົ່ງກັບຄືນ ໂດຍ ' . $this->rejector->displayName,
            'url' => $url, // <-- ใช้ URL ที่เราเพิ่งสร้างขึ้น
        ];
    }
}
