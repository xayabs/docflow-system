<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Document;
use App\Models\User;

class DocumentForwarded extends Notification implements ShouldQueue
{
    use Queueable;

    public $document;
    public $forwarder;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Document $document ເອກະສານທີ່ຖືກສົ່ງຕໍ່
     * @param \App\Models\User $forwarder ຜູ້ທີ່ສົ່ງຕໍ່ເອກະສານ
     * @return void
     */
    public function __construct(Document $document, User $forwarder)
    {
        $this->document = $document;
        $this->forwarder = $forwarder;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
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
        // ເຮົາຕ້ອງສ້າງ URL ແບບ Dynamic ຢູ່ທີ່ນີ້ເຊັ່ນກັນ
        $url = $this->generateUrl($notifiable);

        return (new MailMessage)
                    ->subject('ແຈ້ງເຕືອນ: ມີເອກະສານສົ່ງຕໍ່ມາໃຫ້ກວດສອບ')
                    ->greeting('ສະບາຍດີ ' . $notifiable->name . ',')
                    ->line('ເອກະສານເລກທີ #' . $this->document->id . ' ("' . $this->document->title . '") ໄດ້ຖືກສົ່ງຕໍ່ມາຈາກ ' . $this->forwarder->name . ' ເພື່ອໃຫ້ທ່ານດຳເນີນການກວດສອບ.')
                    ->action('ໄປທີ່ລະບົບເພື່ອດຳເນີນການ', $url)
                    ->line('ຂອບໃຈທີ່ໃຊ້ບໍລິການ!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'ເອກະສານ "' . $this->document->title . '" ຖືກສົ່ງຕໍ່ມາໃຫ້ທ່ານກວດສອບ.',
            'url' => $this->generateUrl($notifiable),
        ];
    }

    /**
     * Generate the appropriate URL based on the notifiable user's role.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function generateUrl(object $notifiable): string
    {
        $roleName = $notifiable->role->name;
        $routeName = 'dashboard'; // ຄ່າເລີ່ມຕົ້ນ
        $routeParams = ['document' => $this->document->id];

        switch ($roleName) {
            case 'Dean_Secretary':
                $routeName = 'secretary.documents.show';
                break;
            case 'Finance_Preparer':
                $routeName = 'finance.preparer.documents.show';
                break;
            case 'Accountant':
                $routeName = 'accountant.documents.show';
                break;

            // ===== ສ່ວນຂອງ Workflow ການຈັດຊື້ =====
            case 'Dean': // ນີ້ອາດຈະເປັນທັງການອະນຸມັດຈັດຊື້ ຫຼື ອະນຸມັດຈ່າຍ
                $routeName = 'dean.documents.show';
                break;
            case 'Procurement_Staff':
                $routeName = 'procurement.documents.show';
                break;
            
            // ===== ສ່ວນທີ່ເຫຼືອຂອງ Workflow ການເງິນ =====
            case 'Vice_Dean':
                $routeName = 'vicedean.documents.show';
                break;
            case 'Head_of_Finance':
                $routeName = 'headfinance.documents.show';
                break;
            case 'Cashier':
                $routeName = 'cashier.documents.show';
                break;
        }
        
        // ໃຫ້ແນ່ໃຈວ່າ Route ມີຢູ່ຈິງກ່ອນທີ່ຈະສ້າງ URL
        return \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName, $routeParams) : '#';
    }
}