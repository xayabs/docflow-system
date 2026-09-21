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

class DocumentReceivedInDepartment extends Notification
{
    use Queueable;

    public $document;
    public $sender;

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
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }*/
    public function toMail(object $notifiable): MailMessage
    {
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ມີເອກະສານຈັດຊື້ສ້ອມແປງໃໝ່ເຂົ້າມາ')
                    ->greeting('ສະບາຍດີ ' . $notifiable->name . ',')
                    ->line('ເອກະສານເລກທີ #' . $this->document->document_code . ' ທີ່ມີຫົວຂໍ້ວ່າ "' . $this->document->title . '" ຖືກສົ່ງເຂົ້າມາຫາທ່ານໂດຍ ' . $this->sender->name . '.')
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
            ->title('ມີເອກະສານຈັດຊື້ສ້ອມແປງໃໝ່ເຂົ້າມາ!')
            ->icon('/images/icons/icon-192x192.png')
            ->body('ເອກະສານ "' . $this->document->title . '" ຖືກສົ່ງເຂົ້າມາໂດຍ ' . $this->sender->name)
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
        // $notifiable คือ Staff ในแผนก
        $url = route('staff.documents.show', $this->document->id);
        
        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            // !!! ข้อความใหม่ที่ถูกต้อง !!!
            'message' => 'ມີເອກະສານໃໝ່ ("' . $this->document->title . '") ເຂົ້າມາຫາຝ່າຍຈັດຊື້/ສ້ອມແປງ',
            'url' => $url,
        ];
    }
}
