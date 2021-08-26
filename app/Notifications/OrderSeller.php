<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Order;
class OrderSeller extends Notification
{
 use Queueable;
        private $order;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order )
    {
               $this->order = $order;

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
            'id'=>$this->order->id,
            'title_ar'=>'هناك طلبية جديدة',
            'title_en'=>'There is a new order',
            'route_api'=>route('api.ordersDetails',$this->order->id),
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


