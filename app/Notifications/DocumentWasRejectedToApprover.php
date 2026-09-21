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
                    ->line('ເອກະສານກ່ຽວກັບ "' . $this->document->title . '" ທີ່ທ່ານໄດ້ອະນຸມັດ ໄດ້ຖືກສົ່ງກັບ ໂດຍ ' . $this->rejector->displayName . '.')
                    ->line('ເຫດຜົນ: ' . $this->document->rejected_reason)
                    ->action('ໄປທີ່ລະບົບ', $url);
    }*/

    public function toMail(object $notifiable): MailMessage
    {
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);
        $msg = $this->customMessage ?? 'ເອກະສານທີ່ທ່ານເຄີຍອະນຸມັດໄດ້ຖືກປະຕິເສດ ໂດຍ ' . $this->rejector->name;

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ເອກະສານຖືກສົ່ງກັບມາແກ້ໄຂ')
                    ->greeting('ສະບາຍດີ ' . $notifiable->name . ',')
                    ->line($msg)
                    ->line('ເຫດຜົນ: ' . $this->document->rejected_reason)
                    ->action('ກວດສອບເອກະສານ', $url)
                    ->line('ຂອບໃຈທີ່ໃຊ້ບໍລິການ!');
    }

    /**
     * Get the Web Push representation of the notification.
     */
    public function toWebPush($notifiable, $notification)
    {
        // 3. ສ້າງໂຄງສ້າງ ແລະ ຂໍ້ຄວາມສຳລັບ Push Notification ທີ່ຈະເດັ້ງຂຶ້ນໜ້າຈໍມືຖື
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);
        $msg = $this->customMessage ?? 'ເອກະສານທີ່ທ່ານເຄີຍອະນຸມັດຖືກປະຕິເສດໂດຍ ' . $this->rejector->name;

        return (new WebPushMessage)
            ->title('ແຈ້ງເຕືອນ: ເອກະສານຖືກສົ່ງກັບ')
            ->icon('/images/icons/icon-192x192.png')
            ->body($msg)
            ->action('ກວດສອບ', 'open_url')
            ->data(['url' => $url]);
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
