<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Models\Document;
use App\Models\User;

class DocumentReturnedForCorrection extends Notification
{
    use Queueable;

    public $document;
    public $sender; // ผู้ที่ส่งกลับ (Head of Finance)

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, User $sender)
    {
        $this->document = $document;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // 2. ເພີ່ມ WebPushChannel::class ເຂົ້າໄປຮ່ວມກັບ database ແລະ mail
        return ['database', 'mail', WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *//*
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('dashboard'); // พาไปหน้า Dashboard ทั่วไป
        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ເອກະສານຖືກປະຕິເສດ')
                    ->line('ເອກະສານກ່ຽວກັບ "' . $this->document->title . '" ທີ່ທ່ານໄດ້ອະນຸມັດ ໄດ້ຖືກສົ່ງກັບໃຫ້ມາແກ້ໄຂ ໂດຍ ' . $this->sender->displayName . '.')
                    ->line('ເຫດຜົນ: ' . $this->document->rejected_reason)
                    ->action('ໄປທີ່ລະບົບ', $url);
    }*/
    public function toMail(object $notifiable): MailMessage
    {
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ເອກະສານຖືກສົ່ງກັບມາເພື່ອປັບປຸງແກ້ໄຂ')
                    ->greeting('ສະບາຍດີ ' . $notifiable->name . ',')
                    ->line('ເອກະສານເລກທີ #' . $this->document->document_code . ' ທີ່ມີຫົວຂໍ້ວ່າ "' . $this->document->title . '" ຖືກສົ່ງກັບມາໂດຍ ' . $this->sender->name . ' ເພື່ອໃຫ້ທ່ານປັບປຸງແກ້ໄຂ.')
                    ->line('ເຫດຜົນ: ' . ($this->document->rejected_reason ?? 'ບໍ່ມີເຫດຜົນລະບຸ'))
                    ->action('ແກ້ໄຂເອກະສານ', $url)
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
            ->title('ເອກະສານຖືກສົ່ງກັບມາໃຫ້ປັບປຸງ!')
            ->icon('/images/icons/icon-192x192.png')
            ->body('ເອກະສານ "' . $this->document->title . '" ຖືກສົ່ງກັບໂດຍ ' . $this->sender->name)
            ->action('ກວດສອບ', 'open_url')
            ->data(['url' => $url]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // หา URL ที่ถูกต้องสำหรับผู้รับ (Accountant)
        $url = route('accountant.documents.show', $this->document->id);
        
        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'ເອກະສານກ່ຽວກັບ "' . $this->document->title . '" ຖືກສົ່ງກັບມາໃຫ້ແກ້ໄຂ ໂດຍ ' . $this->sender->displayName,
            'url' => $url,
        ];
    }
}
