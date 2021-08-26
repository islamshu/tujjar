<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Message;

class ReplyNofication extends Notification
{
 use Queueable;
        private $message;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Message $message )
    {
               $this->message = $message;

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
        // dd($this->message->id);
        return [
            'id'=>$this->message->id,
            'title_en'=>'Your message has been replied',
            'title_ar'=>'تم الرد على رسالتك',
            'route_api'=>route('api.message_id',$this->message->id)
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


