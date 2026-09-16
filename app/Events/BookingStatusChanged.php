<?php

namespace App\Events;

use App\Models\BookingRoute;
use Illuminate\Queue\SerializesModels;

class BookingStatusChanged
{
    use SerializesModels;

    public $booking;
    public $notifications;
    public $editMode;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(BookingRoute $booking, $notifications = [], $editMode = false)
    {
        $this->booking = $booking;
        $this->notifications = $notifications;
        $this->editMode = $editMode;
    }
}
