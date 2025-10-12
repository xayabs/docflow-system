<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Document; // Import Document Model


class DocumentPaid extends Notification
{
    use Queueable;
    public $document;
    protected $customMessage;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Document $document
     * @param string|null $customMessage
     * @return void
     */
    public function __construct(Document $document, $customMessage = null)
    {
        $this->document = $document;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        // ກຳນົດວ່າຈະສົ່ງການແຈ້ງເຕືອນຜ່ານຊ່ອງທາງໃດແດ່
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = getShowUrlForRole($notifiable->role->name, $this->document->id); // ใช้วิธีนี้จะดีกว่า

        $message = $this->customMessage ?? 'ເອກະສານເລກທີ #' . $this->document->id . ' ທີ່ມີຫົວຂໍ້ວ່າ "' . $this->document->title . '" ໄດ້ຮັບການຈ່າຍເງິນຮຽບຮ້ອຍແລ້ວ.';

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ເອກະສານໄດ້ຮັບການຈ່າຍເງິນແລ້ວ')
                    ->greeting('ສະບາຍດີ ' . $notifiable->name . ',')
                    ->line($message)
                    ->action('ເບິ່ງລາຍລະອຽດເອກະສານ', $url)
                    ->line('ຂອບໃຈທີ່ໃຊ້ບໍລິການ!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {   /*
        // $notifiable คือผู้รับ (Requester)
        $userRole = $notifiable->role->name;
        $url = '#'; // ค่าเริ่มต้น

        // เราต้องตรวจสอบว่า Requester เป็น Staff หรือ Procurement Staff
        if ($userRole === 'Staff') {
            // ถ้าเป็น Staff, URL ควรจะชี้ไปที่เอกสาร "จัดซื้อ" ต้นฉบับ (ถ้ามี)
            // หรือเอกสารของตัวเอง
            $targetDocumentId = $this->document->parent_document_id ?? $this->document->id;
            $url = route('staff.documents.show', $targetDocumentId);

        } elseif ($userRole === 'Procurement_Staff') {
            // ถ้าเป็น Procurement Staff, URL ควรจะชี้ไปที่ "เอกสารขอถอนเงิน" ที่ตัวเองสร้าง
            $url = route('procurement.documents.show', $this->document->id);
        }

        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'ເອກະສານກ່ຽວກັບ "' . $this->document->title . '" ໄດ້ຮັບການຈ່າຍເງິນແລ້ວ',
            'url' => $url, // <-- ใช้ URL ที่ถูกต้อง
        ];*/

        // $notifiable คือผู้รับ (Requester)
        $userRole = $notifiable->role->name;
        $url = getShowUrlForRole($userRole, $this->document->id); // <-- ใช้ Helper function

        // กำหนดข้อความตามเงื่อนไข
        $message = $this->customMessage ?? 'ເອກະສານຂອງທ່ານເລື່ອງ "' . $this->document->title . '" ໄດ້ຮັບການຈ່າຍເງິນແລ້ວ.';

        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => $message, // <-- 2. แก้ไขที่นี่ (เหลือบรรทัดเดียว)
            'url' => $url,
        ];
    }
}
