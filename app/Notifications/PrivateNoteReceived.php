<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
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
        return ['database']; // ส่งแค่ในระบบ, ไม่ต้องส่งอีเมล
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id);

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ທ່ານໄດ້ຮັບໂໜດໃໝ່')
                    ->line('ທ່ານໄດ້ຮັບໂໜດໃໝ່ຈາກ ' . $this->sender->displayName . ' ກ່ຽວກັບເອກະສານ "' . $this->document->title . '".')
                    ->line('ຂໍ້ຄວາມ: ' . $this->note)
                    ->action('ໄປທີ່ເອກະສານ', $url);
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
