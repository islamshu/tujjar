<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    const ORDER = 'App\Notifications\OrderSeller';
    const STATUS = 'App\Notifications\V3\OrderStatus';
    const OFFER = 'App\Notifications\V3\FlashdealNofication';
    const PRODUCT = 'product';
    const MESSAGE = 'App\Notifications\V3\MessageNofication';
    const REPLY = 'App\Notifications\V3\ReplyNofication';
}
