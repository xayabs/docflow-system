<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Document;

class DocumentWasRejectedToApprover extends Notification
{
    use Queueable;

    public $document;
    public $rejector;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, User $rejector)
    {
        $this->document = $document;
        $this->rejector = $rejector;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // ส่งทั้งสองช่องทาง
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('dashboard'); // พาไปหน้า Dashboard ทั่วไป
        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ເອກະສານຖືກປະຕິເສດ')
                    ->line('ເອກະສານກ່ຽວກັບ "' . $this->document->title . '" ທີ່ທ່ານໄດ້ອະນຸມັດ ໄດ້ຖືກສົ່ງກັບ ໂດຍ ' . $this->rejector->displayName . '.')
                    ->line('ເຫດຜົນ: ' . $this->document->rejected_reason)
                    ->action('ໄປທີ່ລະບົບ', $url);
    }

    /**
     * Get the array representation of the notification.
     **/
    public function toArray(object $notifiable): array
    {
        // หา URL ที่เหมาะสมสำหรับผู้รับ
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);
        
        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            // !!! ข้อความใหม่ที่ถูกต้อง !!!
            'message' => 'ເອກະສານທີ່ທ່ານເຄີຍອະນຸມັດໄດ້ຖືກສົ່ງກັບ ໂດຍ ' . $this->rejector->displayName,
            'url' => $url,
        ];
    }
}
