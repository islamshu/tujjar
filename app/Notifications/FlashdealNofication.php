<?php

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\FlashDeal;

class FlashdealNofication extends Notification
{
    use Queueable;
    private $flash_deal;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FlashDeal $flash_deal)
    {
        $this->flash= $flash_deal;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
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
    'id'=>$this->flash->id,
    'title_ar'=>'تم اضافة منتجات الى الصفقات السريعة',
    'title_en'=>'Products have been added to Flash deals',
    'route_api'=>route('api_flashdeals')

    
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

