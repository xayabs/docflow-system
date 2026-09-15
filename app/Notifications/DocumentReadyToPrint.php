<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentReadyToPrint extends Notification
{
    use Queueable;

    public $document; // ເພີ່ມຕົວແປນີ້

    public function __construct($document) // ຮັບຄ່າ $document
    {
        $this->document = $document;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // ປ່ຽນເປັນ database (ຖ້າບໍ່ມີການຕັ້ງຄ່າ Mail Server ໃຫ້ເອົາ 'mail' ອອກກ່ອນ)
    }
    /*
    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id, // ດຽວນີ້ $this->document ຈະມີຄ່າແລ້ວ
            'title' => $this->document->title,
            'message' => 'ເອກະສານ "' . $this->document->title . '" ໄດ້ອະນຸມັດໃຫ້ດຳເນີນການໄດ້ແລ້ວ, ຈົ່ງພິມເອກະສານດັ່ງກ່າວສົ່ງໃຫ້ເລຂາຄະນະວິຊາ.',
            'url' => route('staff.documents.show', $this->document->id),
        ];
    }*/
    // ໃນ app/Notifications/DocumentReadyToPrint.php

    public function toArray(object $notifiable): array
    {
        // 1. ກວດສອບ Role ຂອງຜູ້ຮັບການແຈ້ງເຕືອນ ($notifiable)
        $userRole = $notifiable->role ? $notifiable->role->name : '';
        
        // 2. ສ້າງ URL ໃຫ້ຕົງກັບ Role ຂອງຜູ້ຮັບ
        if ($userRole === 'Procurement_Staff') {
            // ຖ້າຜູ້ສະເໜີແມ່ນ ຝ່າຍຈັດຊື້ ໃຫ້ຊີ້ໄປໜ້າຂອງຝ່າຍຈັດຊື້
            $url = route('procurement.documents.show', $this->document->id);
        } else {
            // ຖ້າແມ່ນ Staff ທຳມະດາ ໃຫ້ຊີ້ໄປໜ້າຂອງ Staff
            $url = route('staff.documents.show', $this->document->id);
        }

        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'ເອກະສານ "' . $this->document->title . '" ໄດ້ອະນຸມັດໃຫ້ດຳເນີນການໄດ້ແລ້ວ, ຈົ່ງພິມເອກະສານດັ່ງກ່າວສົ່ງໃຫ້ເລຂາຄະນະວິຊາ.',
            'url' => $url, // <-- ໃຊ້ URL ແບບ Dynamic ທີ່ແຍກຕາມ Role
        ];
    }
}
