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

class DocumentApproved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // 2. ເພີ່ມ Web_PushChannel::class ເຂົ້າໄປຮ່ວມກັບ database ແລະ mail
        return ['database', 'mail', WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ເອກະສານໄດ້ຮັບການອະນຸມັດ')
                    ->greeting('ສະບາຍດີ ' . $notifiable->name . ',')
                    ->line('ເອກະສານເລກທີ #' . $this->document->document_code . ' ທີ່ມີຫົວຂໍ້ວ່າ "' . $this->document->title . '" ໄດ້ຮັບການອະນຸມັດໂດຍ ' . $this->approver->name . ' ແລະ ສົ່ງຕໍ່ມາໃຫ້ທ່ານ.')
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

        return (new WebPushMessage)
            ->title('ເອກະສານລໍຖ້າການກວດສອບ')
            ->icon('/images/icons/icon-192x192.png')
            ->body('ເอกະສານ "' . $this->document->title . '" ຖືກສົ່ງຕໍ່ມາໃຫ້ທ່ານໂດຍ ' . $this->approver->name)
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
        return [
            //
        ];
    }
}
