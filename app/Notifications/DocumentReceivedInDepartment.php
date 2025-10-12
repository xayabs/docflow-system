<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
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
        return ['database']; // ส่งแค่ในระบบก็พอ
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
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
