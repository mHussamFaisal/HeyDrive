<?php

namespace App\Events;

use App\Models\BookingRoute;
use Illuminate\Queue\SerializesModels;

class BookingCreated
{
    use SerializesModels;

    public $booking;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(BookingRoute $booking)
    {
        $this->booking = $booking;
    }
}
