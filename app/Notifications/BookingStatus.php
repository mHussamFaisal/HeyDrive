<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Channels\MailChannel;
use App\Channels\SmsChannel;
use App\Channels\ExpoPushChannel;
use App\Models\BookingRoute;
use App\Helpers\SiteHelper;

class BookingStatus extends Notification
{
    use Queueable;

    protected $status;
    protected $booking;
    protected $note;

    public function __construct($status = '', BookingRoute $booking, $note = '')
    {
        if (config('site.customer_show_only_lead_passenger') && !empty($booking->lead_passenger_name)) {
            $booking->contact_title = $booking->lead_passenger_title;
            $booking->contact_name = $booking->lead_passenger_name;
            if ($booking->lead_passenger_email) {
                $booking->contact_email = $booking->lead_passenger_email;
            }
            if ($booking->lead_passenger_mobile) {
                $booking->contact_mobile = $booking->lead_passenger_mobile;
            }

            $booking->lead_passenger_title = '';
            $booking->lead_passenger_name = '';
            $booking->lead_passenger_email = '';
            $booking->lead_passenger_mobile = '';
        }

        $this->status = $status;
        $this->booking = $booking;
        $this->note = $note;
    }

    public function via($notifiable)
    {
        $channels = [];
        $enabled = [];

        $notifications = (array)config('site.notifications');
        if ( isset($notifications['booking_'. $this->status]) ) {
            $notification = (array)$notifications['booking_'. $this->status];
            if ( isset($notifiable->role) && isset($notification[$notifiable->role]) ) {
                $enabled = (array)$notification[$notifiable->role];
            }
        }

        if ( !empty($notifiable->override_channels) ) {
            $enabled = (array)$notifiable->override_channels;
        }

        if ( $this->status == 'invoice' ) {
            $enabled[] = 'email';
        }

        if ( in_array('email', $enabled) && !empty($notifiable->email) ) {
            $channels[] = MailChannel::class; // 'mail'
        }

        if ( in_array('sms', $enabled) && !empty($notifiable->profile->mobile_no) ) {
            $channels[] = SmsChannel::class;
        }

        if ( in_array('push', $enabled) && !empty($notifiable->push_token) ) {
            $channels[] = ExpoPushChannel::class;
        }

        if ( in_array('db', $enabled) && !empty($notifiable->id) ) {
            $channels[] = 'database';
        }

        // dd($this->status, $channels, $enabled, $notifiable, $notifications, $this);

        return $channels;
    }

    public function toMail($notifiable)
    {
        $driver = $this->booking->assignedDriver();
        $vehicle = $this->booking->assignedVehicle();
        $message = new MailMessage;
        $url = '';

        if (config('site.notification_test_email')) {
            $notifiable->email = config('site.notification_test_email');
            $message->line('<div style="font-size:14px; color:#ff5722;">'. SiteHelper::nl2br2(trans('notifications.test_email_msg')) .'</div>');
        }

        $message->from(config('site.company_email'), config('site.company_name'));
        $message->to($notifiable->email, $notifiable->name);

        switch ($notifiable->role) {
            case 'admin':
                $url = route('admin.bookings.show', $this->booking->id);

                if (in_array($this->status, [
                    'pending', 'quote', 'requested', 'completed', 'canceled', 'confirmed', 'incomplete'
                ]) && !empty($this->booking->contact_email)) {
                    $message->from(config('site.company_email'), $this->booking->contact_name);
                    $message->replyTo($this->booking->contact_email, $this->booking->contact_name);
                }

                if (in_array($this->status, [
                    'assigned', 'auto_dispatch', 'accepted', 'rejected', 'onroute', 'arrived', 'onboard', 'unfinished'
                ]) && !empty($driver->id)) {
                    $message->from(config('site.company_email'), $driver->getName(true));
                    $message->replyTo($driver->email, $driver->getName(true));
                }
            break;
            case 'driver':
                $url = route('driver.jobs.show', $this->booking->id);
            break;
            case 'customer':
                if (!empty($this->booking->booking->user_id)) {
                    $url = route('customer.index') .'#booking/details/'. $this->booking->id;
                }

                if ($this->status == 'completed' && $this->booking->getFeedbackLink()) {
                    $url = $this->booking->getFeedbackLink();
                }

                if ($this->status == 'completed' && $notifiable->role == 'customer' && config('site.feedback_type') == 2) {
                    $url = '';
                }
            break;
        }

        if ($this->status == 'invoice') {
            $url = '';
        }

        $trans = SiteHelper::notifictionTrans('notifications.booking_'. $this->status .'.subject', $notifiable->role, 'email');

        $message->subject(trans($trans, [
            'ref_number' => $this->booking->getRefNumber(),
            'company_name' => config('site.company_name'),
        ]));

        if ($notifiable->name && !in_array($this->status, [
            'pending', 'confirmed', 'requested',
        ])) {
            $trans = SiteHelper::notifictionTrans('notifications.booking_'. $this->status .'.greeting', $notifiable->role, 'email');

            if (!\Lang::has($trans)) {
                $trans = 'notifications.greeting.general';
            }

            $message->greeting(trans($trans, [
                'name' => $notifiable->name,
            ]));
        }

        if ($this->note) {
            $message->line(view('notifications.note', [
                'note' => SiteHelper::nl2br2($this->note),
            ])->render());
        }

        $trans = SiteHelper::notifictionTrans('notifications.booking_'. $this->status .'.message', $notifiable->role, 'email');

        $lines = explode("\r\n\r\n", trans($trans, [
            'ref_number' => $this->booking->getRefNumber(),
            'driver_name' => $driver->getName(false),
            'driver_mobile_no' => $driver->profile->mobile_no,
            'driver_pco_licence' => $driver->profile->pco_licence,
            'vehicle_make' => $vehicle->make,
            'vehicle_model' => $vehicle->model,
            'vehicle_color' => $vehicle->colour,
            'vehicle_registration_mark' => $vehicle->registration_mark,
            'company_name' => config('site.company_name'),
            'request_time' => '<b>'. config('site.booking_request_time') .'h</b>',
            'action_url' => $url,
        ]));

        foreach ($lines as $k => $v) {
        		$lines[$k] = SiteHelper::nl2br2($v);
        }

        switch ($this->status) {
            case 'pending':
            case 'requested':
            case 'confirmed':

                $lines[] = view('notifications.booking_pending_details', [
                    'booking' => $this->booking,
                ])->render();

            break;
            case 'quote':

                $lines[] = view('notifications.booking_quote_details', [
                    'booking' => $this->booking,
                ])->render();

            break;
            case 'assigned':
            case 'auto_dispatch':

                if ($notifiable->role == 'customer') {
                    $lines[] = view('notifications.booking_onroute_details', [
                        'booking' => $this->booking,
                        'driver' => $driver,
                        'vehicle' => $vehicle,
                    ])->render();
                }
                else {
                    if (config('site.driver_attach_booking_details_to_email')) {
                        $lines[] = view('notifications.booking_assigned_details', [
                            'booking' => $this->booking,
                        ])->render();
                    }

                    if ( config('site.booking_meeting_board_enabled') &&
                        config('site.booking_meeting_board_attach') &&
                        $notifiable->role == 'driver' ) {

                        $filename = trans('driver/jobs.subtitle.meeting_board') .'.pdf';
                        $html = $this->booking->getMeetingBoard('pdf');

                        $mpdf = new \Mpdf\Mpdf([
                            'mode' => '',
                            'format' => 'A4',
                            'default_font_size' => 0,
                            'default_font' => '',
                            'margin_left' => 0,
                            'margin_right' => 0,
                            'margin_top' => 0,
                            'margin_bottom' => 0,
                            'margin_header' => 0,
                            'margin_footer' => 0,
                            'orientation' => 'L',
                        ]);
                        $mpdf->WriteHTML($html);

                        $file = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

                        $message->attachData($file, $filename, [
                            'mime' => 'application/pdf',
                        ]);
                    }
                }

            break;
            case 'accepted':

                if ($notifiable->role == 'customer') {
                    $lines[] = view('notifications.booking_onroute_details', [
                        'booking' => $this->booking,
                        'driver' => $driver,
                        'vehicle' => $vehicle,
                    ])->render();
                }

            break;
            case 'onroute':

                if (config('site.customer_attach_booking_details_access_link') && $notifiable->role == 'customer') {
                    $params = \App\Models\BookingParam::where('key', 'access_uuid')->where('booking_id', $this->booking->id)->first();
                    if (!empty($params->value)) {
                        $lines[] = '<a href="'. route('booking.details', $params->value) .'" style="color:#157ed2">'. trans('booking.tracking.access_link') .'</a>';
                    }
                }

            break;
            case 'arrived':

                $lines[] = view('notifications.booking_onroute_details', [
                    'booking' => $this->booking,
                    'driver' => $driver,
                    'vehicle' => $vehicle,
                ])->render();

            break;
            case 'canceled':
            case 'unfinished':

              if ($this->booking->status_notes) {
                  $lines[] = view('notifications.note', [
                      'note' => trans('notifications.reason') .': '. SiteHelper::nl2br2($this->booking->status_notes),
                  ])->render();
              }

              if ($notifiable->role == 'driver') {
                  $lines[] = view('notifications.booking_assigned_details', [
                      'booking' => $this->booking,
                  ])->render();
              }
              else {
                  $lines[] = view('notifications.booking_pending_details', [
                      'booking' => $this->booking,
                  ])->render();
              }

            break;
            case 'invoice':

                $invoice = $this->booking->getInvoice();
                preg_match("~<body.*?>(.*?)<\/body>~is", $invoice, $match);
                if ( !empty($match[0]) ) {
                    $invoice = preg_replace('/(<body.*?>)(.*?)(<\/body>)/s', '$2', $match[0]);
                }
                $lines[] = $invoice;

            break;
        }

        foreach ($lines as $line) {
            $message->line($line);
        }

        if ($url) {
            $trans = SiteHelper::notifictionTrans('notifications.booking_'. $this->status .'.link_view', $notifiable->role, 'email');

            if (!\Lang::has($trans)) {
                $trans = 'notifications.link_view';
            }

            $message->action(trans($trans), $url);
        }

        $footerKey = SiteHelper::notifictionTrans('notifications.booking_'. $this->status .'.footer', $notifiable->role, 'email');
        if (\Lang::has($footerKey)) {
            $footerMsg = trans($footerKey);
            if (!empty($footerMsg)) {
                $message->line(SiteHelper::nl2br2($footerMsg));
            }
        }

        if (in_array($this->status, ['pending', 'requested', 'confirmed'])) {
            $info = '';

            if (config('site.notification_booking_pending_info')) {
                $info = SiteHelper::translate(config('site.notification_booking_pending_info'));
            }
            elseif (trans('notifications.booking_pending.info')) {
                $info = trans('notifications.booking_pending.info');
            }

            if ($info) {
                $info = explode("\r\n\r\n", $info);
                foreach ($info as $k => $v) {
                    $message->line(SiteHelper::nl2br2($v));
                }
            }
        }

        if (in_array($this->status, ['pending', 'requested', 'invoice'])) {
            $send = 1;

            if (in_array($this->status, ['pending', 'requested']) && $notifiable->role != 'customer') {
                $send = 0;
            }

            if ( config('site.invoice_enabled') && $send ) {
                $filename = $this->booking->getInvoiceFilename() .'.pdf';
        				$html = $this->booking->getInvoice();

        				$mpdf = new \Mpdf\Mpdf([
        						'mode' => '',
        						'format' => 'A4',
        						'default_font_size' => 0,
        						'default_font' => '',
        						'margin_left' => 0,
        						'margin_right' => 0,
        						'margin_top' => 0,
        						'margin_bottom' => 0,
        						'margin_header' => 0,
        						'margin_footer' => 0,
        						'orientation' => 'P',
        				]);
                $mpdf->WriteHTML($html);

                $file = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

                $message->attachData($file, $filename, [
                    'mime' => 'application/pdf',
                ]);
            }

            if ( config('site.terms_type') && config('site.terms_text') && config('site.terms_email') && $send ) {
                $terms = config('site.terms_text');
                $terms = SiteHelper::translate($terms);
                $terms = ltrim($terms);
                $terms = SiteHelper::nl2br2($terms);

                // $terms = view('booking.terms', [
                //     'html' => $terms,
                // ])->render();

                $filename = trans('booking.page.terms.page_title') .'.pdf';
                $html = $terms;

                $mpdf = new \Mpdf\Mpdf([
                    'mode' => '',
                    'format' => 'A4',
                    'default_font_size' => 0,
                    'default_font' => '',
                    'margin_left' => 5,
                    'margin_right' => 5,
                    'margin_top' => 5,
                    'margin_bottom' => 5,
                    'margin_header' => 0,
                    'margin_footer' => 0,
                    'orientation' => 'P',
                ]);
                $mpdf->WriteHTML($html);

                $file = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

                $message->attachData($file, $filename, [
                    'mime' => 'application/pdf',
                ]);
            }
        }

        if (config('site.booking_attach_ical') && in_array($this->status, ['pending', 'confirmed'])) {
            $message->attachData($this->booking->getIcal(), trans('booking.ical_journey_filename') .'.ics', [
                'mime' => 'text/calendar',
            ]);
        }

        // \Log::debug((array)$message);
        // dd($message);

        return $message;
    }

    public function toSMS($notifiable)
    {
        if (config('site.notification_test_phone')) {
            $notifiable->profile->mobile_no = config('site.notification_test_phone');
        }

        $driver = $this->booking->assignedDriver();
        $vehicle = $this->booking->assignedVehicle();
        $url = '';

        switch ($notifiable->role) {
            case 'admin':
                $url = route('admin.bookings.show', $this->booking->id);
            break;
            case 'driver':
                $url = route('driver.jobs.show', $this->booking->id);
            break;
            case 'customer':
                if (!empty($this->booking->booking->user_id)) {
                    $url = route('customer.index') .'#booking/details/'. $this->booking->id;
                }

                if ($this->status == 'completed' && $this->booking->getFeedbackLink()) {
                    $url = $this->booking->getFeedbackLink();
                }

                if ($this->status == 'completed' && $notifiable->role == 'customer' && config('site.feedback_type') == 2) {
                    $url = '';
                }
            break;
        }

        $trans = SiteHelper::notifictionTrans('notifications.booking_'. $this->status .'.message', $notifiable->role, 'sms');

        $vehicle_details = $vehicle->make;
        $vehicle_details .= ($vehicle_details ? ' ' : '') . $vehicle->model;
        $vehicle_details .= ($vehicle_details ? ' ' : '') . $vehicle->colour;
        $vehicle_details .= ($vehicle_details ? ' ' : '') . $vehicle->registration_mark;

        $message = trans($trans, [
            'ref_number' => $this->booking->getRefNumber(),
            'booking_from' => $this->booking->getFrom('no_html'),
            'booking_to' => $this->booking->getTo('no_html'),
            'booking_date' => SiteHelper::formatDateTime($this->booking->date),
            'driver_name' => $driver->getName(false),
            'driver_mobile_no' => $driver->profile->mobile_no,
            'driver_pco_licence' => $driver->profile->pco_licence,
            'vehicle_registration_mark' => $vehicle->registration_mark,
            'vehicle_make' => $vehicle->make,
            'vehicle_model' => $vehicle->model,
            'vehicle_color' => $vehicle->colour,
            'vehicle_details' => trim($vehicle_details),
            'company_name' => config('site.company_name'),
            'request_time' => config('site.booking_request_time') .'h',
            'action_url' => $url,
        ]);

        if (config('site.driver_attach_booking_details_to_sms') && $notifiable->role == 'driver' && in_array($this->status, ['assigned','auto_dispatch'])) {
            $message = view('notifications.booking_assigned_details_sms', [
                'booking' => $this->booking,
                'driver' => $driver,
                'vehicle' => $vehicle,
            ])->render();
        }

        if (config('site.customer_attach_booking_details_to_sms') && $notifiable->role == 'customer' && $this->status == 'pending') {
            $message = view('notifications.booking_pending_details_sms', [
                'booking' => $this->booking,
                'driver' => $driver,
                'vehicle' => $vehicle,
            ])->render();
        }

        if (config('site.customer_attach_booking_details_access_link') && $notifiable->role == 'customer' && $this->status == 'onroute') {
            $params = \App\Models\BookingParam::where('key', 'access_uuid')->where('booking_id', $this->booking->id)->first();
            if (!empty($params->value)) {
                $message .= ' '. trans('booking.tracking.access_link') .': '. route('booking.details', $params->value);
            }
        }

        $message = str_replace('<separator>', ';', $message);
        $message = str_replace('&amp;', '&', $message);
        $message = strip_tags($message);
        $message = preg_replace('/\s+/', ' ', $message);
        $message = trim($message);

        if (config('site.notification_test_phone')) {
            $message = trans('notifications.test_phone_msg') .' '. $message;
        }

        // \Log::debug((array)$message);
        // dd($message);

        return [
            'numbers' => [$notifiable->profile->mobile_no],
            'message' => $message,
        ];
    }

    public function toExpoPush($notifiable)
    {
        $driver = $this->booking->assignedDriver();
        $vehicle = $this->booking->assignedVehicle();
        $url = '';

        switch ($notifiable->role) {
            case 'admin':
                $url = route('admin.bookings.show', $this->booking->id);
            break;
            case 'driver':
                $url = route('driver.jobs.show', $this->booking->id);
            break;
            case 'customer':
                if (!empty($this->booking->booking->user_id)) {
                    $url = route('customer.index') .'#booking/details/'. $this->booking->id;
                }

                if ($this->status == 'completed' && $this->booking->getFeedbackLink()) {
                    $url = $this->booking->getFeedbackLink();
                }

                if ($this->status == 'completed' && $notifiable->role == 'customer' && config('site.feedback_type') == 2) {
                    $url = '';
                }
            break;
        }

        $trans = SiteHelper::notifictionTrans('notifications.booking_'. $this->status .'.message', $notifiable->role, 'push_or_sms');

        $title = trans($trans, [
            'ref_number' => $this->booking->getRefNumber(),
            'booking_from' => $this->booking->getFrom('no_html'),
            'booking_to' => $this->booking->getTo('no_html'),
            'booking_date' => SiteHelper::formatDateTime($this->booking->date),
            'driver_name' => $driver->getName(false),
            'driver_mobile_no' => $driver->profile->mobile_no,
            'driver_pco_licence' => $driver->profile->pco_licence,
            'vehicle_registration_mark' => $vehicle->registration_mark,
            'vehicle_make' => $vehicle->make,
            'vehicle_model' => $vehicle->model,
            'vehicle_color' => $vehicle->colour,
            'company_name' => config('site.company_name'),
            'request_time' => config('site.booking_request_time') .'h',
            'action_url' => $url,
        ]);

        $trans = SiteHelper::notifictionTrans('notifications.booking_'. $this->status .'.link_view', $notifiable->role, 'push_or_sms');

        if (!\Lang::has($trans)) {
            $trans = 'notifications.link_view';
        }

        $body = $title;
        // $title = trim(config('site.company_name') .' '. trans($trans) .' >');
        $title = trim(config('site.company_name'));

        $body = str_replace('<separator>', ';', $body);
        $body = str_replace('&amp;', '&', $body);
        $body = strip_tags($body);
        $body = preg_replace('/\s+/', ' ', $body);
        $body = trim($body);

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'url' => $url,
                'id' => $this->booking->id,
            ]
        ];
    }

    public function toDatabase($notifiable)
    {
        return [
            'ref_number' => $this->booking->getRefNumber(),
            'id' => $this->booking->id,
        ];
    }
}
