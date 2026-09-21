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

class PrivateNoteReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public Document $document;
    public User $sender;
    public string $note;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, User $sender, string $note)
    {
        $this->document = $document;
        $this->sender = $sender;
        $this->note = $note;
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
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ທ່ານໄດ້ຮັບໂໜດໃໝ່')
                    ->line('ທ່ານໄດ້ຮັບໂໜດໃໝ່ຈາກ ' . $this->sender->displayName . ' ກ່ຽວກັບເອກະສານ "' . $this->document->title . '".')
                    ->line('ຂໍ້ຄວາມ: ' . $this->note)
                    ->action('ໄປທີ່ເອກະສານ', $url);
    }*/
    public function toMail(object $notifiable): MailMessage
    {
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ທ່ານໄດ້ຮັບໂນດ/ຄວາມເຫັນເພີ່ມເຕີມ')
                    ->greeting('ສະບາຍດີ ' . $notifiable->name . ',')
                    ->line('ທ່ານໄດ້ຮັບຂໍ້ຄວາມເພີ່ມເຕີມກ່ຽວກັບເອກະສານເລກທີ #' . $this->document->document_code . ' ຈາກ ' . $this->sender->name . ':')
                    ->line('"' . $this->noteMessage . '"')
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
            ->title('ມີໂນດ/ຄວາມເຫັນໃໝ່ຈາກ ' . $this->sender->name)
            ->icon('/images/icons/icon-192x192.png')
            ->body('ຂໍ້ຄວາມ: ' . $this->noteMessage)
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
        // หา URL ที่ถูกต้องสำหรับผู้รับ
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);
        
        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'ທ່ານໄດ້ຮັບໂໜດໃໝ່ຈາກ ' . $this->sender->displayName . ': "' . \Illuminate\Support\Str::limit($this->note, 50) . '"',
            'url' => $url,
            'note_content' => $this->note,
        ];
    }
    /*
    public function toArray(object $notifiable): array
    {
        // หา URL ที่ถูกต้องสำหรับผู้รับ
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);
        
        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            // ข้อความใหม่ที่เกี่ยวกับโน้ตโดยเฉพาะ
            'message' => 'ທ່ານໄດ້ຮັບໂໜດໃໝ່ຈາກ ' . $this->sender->name . ' ກ່ຽວກັບເອກະສານ "' . $this->document->title . '"',
            'url' => $url,
            'note_content' => $this->note, // (Optional) แนบเนื้อหาโน้ตไปด้วย
        ];
    }*/
}
