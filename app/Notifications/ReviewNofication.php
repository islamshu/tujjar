<?php

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Review;
class ReviewNofication extends Notification
{
use Queueable;
        private $review;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Review $review )
    {
               $this->review = $review;

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
    'id'=>$this->review->id,
    'title_ar'=>'تم تقيم منتجك بواسطة '.$this->review->user->name,
    'title_en'=>'Your product has been rated by'.$this->review->user->name,
    'route_api'=>route('products.show', $this->review->product_id)

    
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
