<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Document; // Import Document Model

class DocumentSubmitted extends Notification //implements ShouldQueue
{
    use Queueable;
    protected $document;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/login'); // ຫຼືຈະສ້າງ URL ທີ່ພາໄປຍັງເອກະສານໂດຍກົງ
        return (new MailMessage)
                    ->subject('ມີເອກະສານໃໝ່ລໍການກວດສອບ')
                    ->line('ມີເອກະສານໃໝ່ "' . $this->document->title . '" ສົ່ງມາເພື່ອລໍການກວດສອບ.')
                    ->action('ໄປຫາລະບົບ', $url)
                    ->line('ຂອບໃຈທີ່ໃຊ້ບໍລິການ!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

    public function toArray(object $notifiable): array
    {
        // 1. ດືງຊື່ຂອງ Role ຂອງຜູ້ໃຊ້ທີ່ຈະໄດ້ຮັບການແຈ້ງເຕືອນ
        $roleName = $notifiable->role->name;
    
        // 2. ກໍາໜົດ Route Name ແລະ Parameters ເລີ່ມຕົ້ນ
        $routeName = 'dashboard'; // ຄ່າເລີ່ມຕົ້ນ, ປ້ອງກັນ Error
        $routeParams = [];

        // 3. ໃຊ້ switch-case ເພື່ອກໍໜົດ Route ທີ່ຖືກຕ້ອງຕາມ Role
        switch ($roleName) {
            case 'Dean_Secretary':
                $routeName = 'secretary.documents.show';
                $routeParams = ['document' => $this->document->id];
                break;
            case 'Finance_Preparer':
                $routeName = 'finance.preparer.documents.show';
                $routeParams = ['document' => $this->document->id];
                break;
            case 'Accountant':
                $routeName = 'accountant.documents.show'; 
                $routeParams = ['document' => $this->document->id];
                break;
            case 'Vice_Dean':
                $routeName = 'vicedean.documents.show';
                $routeParams = ['document' => $this->document->id];
                break;
            case 'Head_of_Finance':
                $routeName = 'headfinance.documents.show';
                $routeParams = ['document' => $this->document->id];
                break;
            case 'Dean':
                $routeName = 'dean.documents.show';
                $routeParams = ['document' => $this->document->id];
                break;
            case 'Cashier':
                $routeName = 'cashier.documents.show';
                $routeParams = ['document' => $this->document->id];
                break;
            case 'Procurement_Staff':
                $routeName = 'procurement.documents.show';
                $routeParams = ['document' => $this->document->id];
                break;
        }

        return [
            'document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'ມີເອກະສານໃໝ່ລໍຖ້າການກວດສອບຈ່າກທ່ານ',
            // 4. ສ້າງ URL ຈາກ Route Name ແລະ Parameters ທີ່ເຮົາເລືອກໄວ້
            'url' => route($routeName, $routeParams),
        ];
    }
}
