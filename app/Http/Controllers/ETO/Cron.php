<?php

namespace App\Http\Controllers\ETO;

use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\SiteHelper;

// wget -q -O /dev/null "https://www.domain.com/eto/cron?security_key=xxx&job_reminder=1"
// wget -q -O /dev/null "https://www.domain.com/eto/cron?security_key=xxx&job_reminder=1" > /dev/null 2>&1
// Cron 0	*	*	*	* (every 1 hour)
if ( empty($etoPost['security_key']) || empty(config('eto_cron.secret_key')) || trim($etoPost['security_key']) !== trim(config('eto_cron.secret_key')) ) {
		die('Restricted access');
}

class ETOCronAPI
{
	public $success = false;
	public $message = array(
		'success' => array(),
		'info' => array(),
		'warning' => array(),
		'danger' => array()
	);
	public $config = null;

	public $subscription;

  function __construct()
	{
			$request = request();
			$this->subscription = $request->system->subscription;
			$this->getSiteConfig();
  }

  public function getSiteConfig()
	{
			$this->success = true;
			$this->config = \App\Models\Config::where('site_id', $this->siteId)->toObject();

			$this->config->job_reminder_enabled = 0;
			$this->config->job_reminder_type = 0;
			$this->config->job_reminder_minutes = config('eto_cron.job_reminder.minutes');
			$this->config->job_reminder_allowed_times = config('eto_cron.job_reminder.allowed_times');

			$this->config->mail_job_reminder_subject = 'Job reminder';
			$this->config->mail_job_reminder_body = 'You have <span style="color:#FF7200;font-weight:bold;">{amount}</span> upcoming jobs.<br />{list}<br /><br />';

			$this->config->document_expiry_reminder_enabled = 0;
			$this->config->document_expiry_reminder_days = 7;
			$this->config->document_expiry_reminder_driver_enabled = 1;
			$this->config->document_expiry_reminder_admin_enabled = 1;

			$this->config->mail_document_expiry_reminder_driver_subject = 'Document expiry reminder';
			$this->config->mail_document_expiry_reminder_driver_body = 'Some of your documents are about to expire.<br />{list}<br />{companyName} - {siteLink}<br /><br />';
			$this->config->mail_document_expiry_reminder_admin_subject = 'Document expiry reminder';
			$this->config->mail_document_expiry_reminder_admin_body = 'Some of the driver\'s documents are about to expire.<br />{list}<br />{companyName} - {siteLink}<br /><br />';
  }

	public function jobReminder()
	{
			$sender = array(
					$this->config->company_email,
					$this->config->company_name,
			);

			$start = date('Y-m-d H:i:s');
			$end = date('Y-m-d H:i:s', time() + ($this->config->job_reminder_minutes * 60));

			$bookings = \App\Models\BookingRoute::whereBetween('date', [$start, $end])
				->where('job_reminder', '<', $this->config->job_reminder_allowed_times)
				->where('driver_id', '>', 0)
				->whereIn('status', [
						'pending',
						'requested',
						'confirmed',
						'assigned',
						'auto_dispatch',
						'accepted',
				])
				->orderBy('date', 'ASC')
				->orderBy('ref_number', 'ASC')
				->get();

			// \Log::info(request()->fullUrl() .' | '. request('security_key') .' | '. settings_system('eto_cron.secret_key'));
			// \Log::info('Cron reminders: '. $bookings->count());
			// dd($bookings, $start, $end);

			if ($bookings) {
					$i = 0;
					$list = [];

					foreach($bookings as $booking) {
							$i++;

							if ((int)$booking->driver_id > 0 && !isset($list[$booking->driver_id]['driver'])) {
									$driver = $booking->assignedDriver();

									if (!$driver) {
											continue;
									}

									$list[$booking->driver_id]['driver'] = array(
											$driver->email,
											$driver->name
									);
							}

							if ($this->config->job_reminder_type == 0) {
									$booking->job_reminder = $booking->job_reminder + 1;
									$booking->save();
							}

							// Job '. $i .' -
							$list[$booking->driver_id]['body'][] = '<div style="color:#808080;font-size:12px;margin-bottom:10px;"><a style="color:#008000;" href="' . url('/driver/jobs/' . $booking->id) . '">'. $booking->getRefNumber() .'</a> | DATE: <span style="color:#000000;">'. SiteHelper::formatDateTime($booking->date) .'</span> | FROM: <span style="color:#000000;">'. $booking->getFrom() .'</span> | TO: <span style="color:#000000;">'. $booking->getTo() .'</span></div>';
					}

					foreach($list as $driverId => $data) {
							$subject = $this->config->mail_job_reminder_subject;
							$body = $this->config->mail_job_reminder_body;

							$replace = array(
									'amount' => count($data['body']),
									'list' => implode('', $data['body'])
							);

							foreach($replace as $key => $value) {
									$subject = str_replace('{'. $key .'}', $value, $subject);
									$body = str_replace('{'. $key .'}', $value, $body);
							}

							ob_start();
							eval('?> '. $body .' <?php ');
							$body = ob_get_clean();
							ob_end_clean();

							$body = SiteHelper::nl2br2($body);

							try {
									\Mail::to($data['driver'][0], $data['driver'][1])->send(new \App\Mail\CronReminder($sender, $subject, $body));
									$sent = true;
							}
							catch (\Exception $e) {
									$sent = false;
							}

							if ( $sent !== true ) {
									$this->message['warning'][] = 'Error sending email to '. $data['driver'][0] . '(' . $data['driver'][1] . ')';
							}
							else {
									$this->success = true;
							}
					}

					// dd($list);
			}

			return $this->success;
	}

	// private function sendJobReminder($sender,$data,$subject,$body) {
  //   	return (new MailMessage)->from($sender[0], $sender[1])
	// 		->to($data['driver'][0], $data['driver'][1])
	// 		->subject($subject)
	// 		->greeting('Hello!')
	// 		->line($body)
	// 		->action('View all jobs', url('/driver/jobs'));
	// }

	public function documentExpiryReminder()
	{
		$db = \DB::connection();
		$dbPrefix = get_db_prefix();

		$sql = "SELECT `a`.*, `b`.*, `a`.`id` AS `main_user_id`
						FROM `{$dbPrefix}user` AS `a`
						LEFT JOIN `{$dbPrefix}user_driver` AS `b`
						ON `a`.`id`=`b`.`user_id`
						WHERE `a`.`type`='2'
						AND `a`.`published`='1'
						AND `a`.`site_id`='". $this->siteId ."'
						ORDER BY `a`.`name` ASC";
		$query = $db->select($sql);

		if ( !empty($query) ) {
			$list = '';
			$featureTime = time() + 60 * 60 * 24 * $this->config->document_expiry_reminder_days;

			foreach($query as $k => $v) {

				$expired = '';

				// Date only
				$check = array(
					'custom_field_10' => 'PCO Licence',
					'custom_field_11' => 'Driving Licence',
					'custom_field_12' => 'PHV Licence'
				);

				foreach($check as $k2 => $v2) {
					$temp = $v->{$k2};
					if ( $temp != '0000-00-00') {
						$formattedDate = SiteHelper::formatDateTime($temp, 'date');
						if ( strtotime($temp) <= $featureTime ) {
							if ( !empty($expired) ) {
								$expired .= ', ';
							}
							$expired .= $v2 .': <span style="color:red;">'. $formattedDate .'</span>';
						}
					}
				}

				// Date and Time
				$check = array(
					'custom_field_13' => 'MOT',
					'custom_field_14' => 'Insurance'
				);

				foreach($check as $k2 => $v2) {
					$temp = $v->{$k2};
					if ( $temp != '0000-00-00 00:00:00') {
						$formattedDate = SiteHelper::formatDateTime($temp);
						if ( strtotime($temp) <= $featureTime ) {
							if ( !empty($expired) ) {
								$expired .= ', ';
							}
							$expired .= $v2 .': <span style="color:red;">'. $formattedDate .'</span>';
						}
					}
				}

				if ( !empty($expired) ) {
					$list .= '<p>'. $v->name .' - '. $expired .'</p>';
				}

				// Driver
				if ( !empty($expired) && !empty($v->email) && $this->config->document_expiry_reminder_driver_enabled > 0 ) {
					$sender = array(
						$this->config->company_email,
						$this->config->company_name
					);

					$recipient = array(
						$v->email,
						$v->name
					);

					$subject = $this->config->mail_document_expiry_reminder_driver_subject;
					$body = $this->config->mail_document_expiry_reminder_driver_body;

					$replace = array(
						'companyName' => $this->config->company_name,
						'list' => '<p>'. $expired .'</p>',
						'siteLink' => $this->config->url_home
					);

					foreach($replace as $key => $value) {
						$subject = str_replace('{'. $key .'}', $value, $subject);
						$body = str_replace('{'. $key .'}', $value, $body);
					}

					ob_start();
					eval('?> '. $body .' <?php ');
					$body = ob_get_clean();
					ob_end_clean();

					$body = SiteHelper::nl2br2($body);

					try {
							$sent = \Mail::send(
								['html' => 'emails.blank-html', 'text' => 'emails.blank-text'],
								['body' => $body],
								function ($message) use ($subject, $sender, $recipient) {
									$message->from($sender[0], $sender[1])
													->to($recipient[0], $recipient[1])
													->subject($subject);
								}
							);
					}
					catch (\Exception $e) {
					    $sent = false;
					}

					if ( $sent !== true ) {
						// $this->message['warning'][] = 'Error sending email: '. $send->message;
					}
					else {
						// $this->success = true;
					}

					$this->success = true;
				}
			}

			// Admin
			if ( !empty($list) && $this->config->document_expiry_reminder_admin_enabled > 0 ) {

				$sender = array(
					$this->config->company_email,
					$this->config->company_name
				);

				$recipient = array(
					$this->config->company_email,
					$this->config->company_name
				);

				$subject = $this->config->mail_document_expiry_reminder_admin_subject;
				$body = $this->config->mail_document_expiry_reminder_admin_body;

				$replace = array(
					'companyName' => $this->config->company_name,
					'list' => $list,
					'siteLink' => $this->config->url_home
				);

				foreach($replace as $key => $value) {
					$subject = str_replace('{'. $key .'}', $value, $subject);
					$body = str_replace('{'. $key .'}', $value, $body);
				}

				ob_start();
				eval('?> '. $body .' <?php ');
				$body = ob_get_clean();
				ob_end_clean();

				$body = SiteHelper::nl2br2($body);

				try {
						$sent = \Mail::send(
							['html' => 'emails.blank-html', 'text' => 'emails.blank-text'],
							['body' => $body],
							function ($message) use ($subject, $sender, $recipient) {
								$message->from($sender[0], $sender[1])
												->to($recipient[0], $recipient[1])
												->subject($subject);
							}
						);
				}
				catch (\Exception $e) {
				    $sent = false;
				}

				if ( $send !== true ) {
					// $this->message['warning'][] = 'Error sending email: '. $send->message;
				}
				else {
					// $this->success = true;
				}

				$this->success = true;

				//$this->message['warning'][] = $rows;
			}
		}

		return $this->success;
	}
}

// Output
$data = array(
		'message' => array(),
		'success' => false
);

$etoAPI = new ETOCronAPI();

// Job reminder
if ( isset($etoPost['job_reminder']) ) {
		$etoAPI->config->job_reminder_enabled = (int)$etoPost['job_reminder'];
}

if ( $etoAPI->config->job_reminder_enabled == 1 ) {
		$etoAPI->jobReminder();
}

if (isset($etoPost['autodispatch']) && config('eto_dispatch.enable_autodispatch')) {
		\App\Http\Controllers\DispatchDriverController::check();
}

// Driver expiry
//if ( isset($etoPost['documentExpiryReminder']) ) {
//	$etoAPI->config->document_expiry_reminder_enabled = (int)$etoPost['documentExpiryReminder'];
//}
//if ( $etoAPI->config->document_expiry_reminder_enabled == 1 ) {
//	$etoAPI->documentExpiryReminder();
//}

$data['success'] = $etoAPI->success;
$data['message'] = $etoAPI->message;

$response = $data;
