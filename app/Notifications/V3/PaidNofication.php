<?php

namespace App\Notifications\V3;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\V3\Payment;

class PaidNofication extends Notification
{
use Queueable;
        private $payment;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Payment $payment )
    {
               $this->payment = $payment;

    }

  
      public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toDatabase()
    {
        return [
            'id'=>$this->payment->id,
            'title_ar'=>'تم اتم تحويل دفعة نقدية من الإدارة الى حسابك',
            'title_en'=>'A cash payment has been transferred from the administration to your account',
            'api_route'=>route('api.seller_payments'),

            
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
