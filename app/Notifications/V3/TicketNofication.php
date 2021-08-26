<?php

namespace App\Notifications\V3;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\V3\TicketReply;

class TicketNofication extends Notification
{
use Queueable;
        private $ticket;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(TicketReply $ticket )
    {
               $this->ticket = $ticket;

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
            'id'=>$this->ticket->id,
            'title_ar'=>'تم الرد على التذكرة',
            'title_en'=>'Ticket has been answered',

            
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


