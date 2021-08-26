<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    const ORDER = 'App\Notifications\OrderSeller';
    const STATUS = 'App\Notifications\OrderStatus';
    const OFFER = 'App\Notifications\FlashdealNofication';
    const PRODUCT = 'product';
    const MESSAGE = 'App\Notifications\MessageNofication';
    const REPLY = 'App\Notifications\ReplyNofication';
}
