<?php

namespace App\Http\Controllers\ETO;

use DB;
use App\Helpers\SiteHelper;
use Carbon\Carbon;

class ETOFrontendAPI
{
	public $config = null;
	public $configBrowser = null;
	public $lang = null;
	public $message = array(
				'error' => array(),
				'warning' => array(),
				'success' => array()
			);
	public $success = false;
	public $siteId = 0;
	public $siteName = '';
	public $userId = 0;
	public $userAvatarPath = 0;
	public $userName = '';
	public $userSince = '';

  function __construct()
	{
		$siteId = config('site.site_id');
		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		$sql = "SELECT `id`, `name`
				FROM `{$dbPrefix}profile`
				WHERE `published`='1'
				AND `id`='". $siteId ."'
				LIMIT 1";

		$query = $db->select($sql);
		if (!empty($query[0])) {
			$query = $query[0];
		}

		if ( !empty($query) ) {
			$this->siteId = (int)$query->id;
			$this->siteName = (string)$query->name;
		}

		$this->config = self::getConfig();
		$this->lang = self::loadLang();
		$this->userId = (int)session('etoUserId', 0);

    $model = \App\Models\Customer::select('id', 'name', 'created_date', 'avatar')
        ->where('id', $this->userId )
        ->where('site_id', $this->siteId )
        ->first();

		if ( !empty($model->id) ) {
			$this->userId = (int)$model->id;
			$this->userAvatarPath = $model->getAvatarPath();
			$this->userName = (string)$model->name;
			$this->userSince = trans('driver/account.member_since') .' '. Carbon::parse($model->created_date)->diffForHumans(null, true, false, 2);
		}
  }

	public function loadLang()
	{
		return trans('frontend');
	}

	public function getLang( $id )
	{
		$showTranslation = 0;
		$translation = '';

		if ( $showTranslation ) {
			$translation .= '*';
		}

		if ( !empty($this->lang[$id]) ) {
			$translation .= $this->lang[$id];
		}
		else {
			$translation .= $id;
		}

		if ( $showTranslation ) {
			$translation .= '*';
		}

		return $translation;
	}

	public function getConfig()
	{
		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		$config = new \stdClass();
		$configBrowser = new \stdClass();

		$sql = "SELECT `key` AS `param`, `value`, `type`, `browser`
				FROM `{$dbPrefix}config`
				WHERE `site_id`='". $this->siteId ."'
				ORDER BY `key` ASC";

		$query = $db->select($sql);

		if ( !empty($query) ) {
			foreach($query as $k => $v) {

				if ( $v->param == 'language' ) {
					$v->value = app()->getLocale();
				}

				switch( $v->type ) {
					case 'int':
						$v->value = (int)$v->value;
					break;
					case 'float':
						$v->value = (float)$v->value;
					break;
					case 'string':
						$v->value = (string)$v->value;
					break;
					case 'object':
						$v->value = json_decode($v->value);
					break;
					default:
						$v->value = $v->value;
					break;
				}

				if ( $v->browser == 1 ) {
					$configBrowser->{$v->param} = $v->value;
				}

				$config->{$v->param} = $v->value;
			}
		}

		$this->configBrowser = $configBrowser;

		return $config;
	}


	public function bookingList()
	{
			$bookings = array();

			if ( empty($this->userId) ) {
					return $bookings;
			}

			$db = DB::connection();
			$dbPrefix = get_db_prefix();

			$sql = "SELECT `a`.*,
						  `b`.*,
						  `a`.`id` AS `route_id`,
						  `a`.`ref_number` AS `route_ref_number`
					FROM `{$dbPrefix}booking_route` AS `a`
					LEFT JOIN `{$dbPrefix}booking` AS `b`
					ON `a`.`booking_id`=`b`.`id`
					WHERE /*`b`.`site_id`='" . $this->siteId . "'
					AND*/ `b`.`user_id`='" . $this->userId . "'
					AND `a`.`deleted_at` IS NULL
					ORDER BY `a`.`id` DESC";

			$query = $db->select($sql);

			if ( !empty($query) ) {
					foreach($query as $key => $value) {
							$bookingRoute = \App\Models\BookingRoute::find($value->route_id);

							$booking = new \stdClass();
							$booking->id = (int)$value->route_id;
							$booking->refNumber = $bookingRoute->ref_number;
							$booking->date = SiteHelper::formatDateTime($bookingRoute->date);
							$booking->from = $bookingRoute->getFrom();
							$booking->to = $bookingRoute->getTo();
							$booking->waypoints = $bookingRoute->getVia();
							$booking->price = $bookingRoute->getTotal();
							$booking->createdDate = SiteHelper::formatDateTime($bookingRoute->created_date);
							$booking->feedbackLink = config('site.feedback_type') == 2 ? '' : $bookingRoute->getFeedbackLink();

							switch( $bookingRoute->status ) {
									case 'assigned':
									case 'auto_dispatch':
									case 'accepted':
									case 'rejected':
									case 'onroute':
									case 'arrived':
									case 'onboard':
											$bookingRoute->status = 'pending';
									break;
							}
							$booking->status = $bookingRoute->getStatus('label');

							$booking->contact_name = $bookingRoute->getContactFullName();
							$booking->contact_email = $value->contact_email;
							$booking->contact_mobile = $value->contact_mobile;

							$booking->buttonPay = 0;
							$booking->buttonInvoice = 0;
							$booking->buttonEdit = 0;
							$booking->buttonCancel = 0;
							$booking->buttonDelete = 0;

							// if ( $value->payment_method != 'cash' && $value->payment_method != 'account' && $value->payment_method != 'bacs' && $value->payment_method != 'none' && $value->payment_status != 'paid' ) {
							// 	$booking->buttonPay = 1;
							// }

							if ( in_array($value->status, array('pending','confirmed','assigned','auto_dispatch','rejected','incomplete','requested','quote')) && strtotime($value->date) - (4 * 3600) > time() ) {
									$booking->buttonEdit = 1;
							}

							if ( $this->config->booking_cancel_enable && in_array($value->status, array('pending','confirmed','assigned','auto_dispatch','rejected','incomplete','requested','quote')) && strtotime($value->date) - ($this->config->booking_cancel_time * 3600) > time() ) {
									$booking->buttonCancel = 1;
							}

							// && $value->payment_method != 'none'
							if ( $this->config->invoice_enabled ) {
									$booking->buttonInvoice = 1;
							}

							$bookings[] = $booking;
					}

					$this->success = true;
			}
			else {
					//$this->message['warning'][] = $this->getLang('bookingMsg_NoBookings');
			}

			return $bookings;
	}


	public function bookingDetails( $data )
	{
		$booking = new \stdClass();
		$booking->id = 0;

		if ( empty($this->userId) ) {
			return $booking;
		}

		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		$sql = "SELECT `a`.*,
					  `b`.*,
					  `a`.`id` AS `route_id`,
					  `a`.`ref_number` AS `route_ref_number`
				FROM `{$dbPrefix}booking_route` AS `a`
				LEFT JOIN `{$dbPrefix}booking` AS `b`
				ON `a`.`booking_id`=`b`.`id`
				WHERE /*`b`.`site_id`='" . $this->siteId . "'
				AND*/ `b`.`user_id`='" . $this->userId . "'
				AND `a`.`id`='" . $data->id . "'
				AND `a`.`deleted_at` IS NULL
				LIMIT 1";

		$query = $db->select($sql);
		if (!empty($query[0])) {
			$query = $query[0];
		}

		if ( !empty($query) ) {
			$bookingRoute = \App\Models\BookingRoute::find($query->route_id);

			// Payments
			$temp = $bookingRoute->getTotal('data', 'raw');
			$total = $temp->total;
			$paymentCharge = $temp->payment_charge;
			$pInfo = '';

			foreach($temp->payment_list as $v) {
				if ( count($temp->payment_list) > 1 ) {
					$pInfo .= $v->title .' ';
				}
				$pInfo .= $v->formatted->total .' <span style="color:#888;">( '. $v->name .' )</span> - '. $v->status_color .'<br>';
			}

			$booking->id = (int)$query->route_id;
			$booking->refNumber = $bookingRoute->ref_number;
			$booking->uniqueKey = (string)$query->unique_key;
			$booking->date = SiteHelper::formatDateTime($bookingRoute->date);
			$booking->serviceType = $bookingRoute->getServiceType();
			$booking->serviceDuration = $bookingRoute->getServiceDuration();
			$booking->from = $bookingRoute->getFrom();
			$booking->to = $bookingRoute->getTo();
			$booking->waypoints = $bookingRoute->getVia();
			$booking->contactTitle = (string)$query->contact_title;
			$booking->contactName = (string)$query->contact_name;
			$booking->contactEmail = (string)$query->contact_email;
			$booking->contactMobile = (string)$query->contact_mobile;
			$booking->department = (string)$query->department;
			$booking->leadPassenger = (!empty($query->lead_passenger_name) || !empty($query->lead_passenger_mobile) || !empty($query->lead_passenger_email) ) ? 1 : 0;
			$booking->leadPassengerTitle = (string)$query->lead_passenger_title;
			$booking->leadPassengerName = (string)$query->lead_passenger_name;
			$booking->leadPassengerEmail = (string)$query->lead_passenger_email;
			$booking->leadPassengerMobile = (string)$query->lead_passenger_mobile;
			$booking->requirements = (string)$query->requirements;
			$booking->flightNumber = (string)$query->flight_number;
			$booking->flightLandingTime = (string)$query->flight_landing_time;
			$booking->departureCity = (string)$query->departure_city;
			$booking->departureFlightNumber = (string)$query->departure_flight_number;
			$booking->departureFlightTime = (string)$query->departure_flight_time;
			$booking->departureFlightCity = (string)$query->departure_flight_city;
			$booking->waitingTime = (int)$query->waiting_time;
			$booking->meetAndGreet = !empty($query->meet_and_greet) ? $this->getLang('bookingField_Yes') : '';
			$booking->meetingPoint = (string)$query->meeting_point;
			$booking->vehicle = $bookingRoute->getVehicleList();
			$booking->passengers = (int)$query->passengers;
			$booking->luggage = (int)$query->luggage;
			$booking->handLuggage = (int)$query->hand_luggage;
			$booking->childSeats = (int)$query->child_seats;
			$booking->babySeats = (int)$query->baby_seats;
			$booking->infantSeats = (int)$query->infant_seats;
			$booking->wheelchair = (int)$query->wheelchair;
			$booking->route = $bookingRoute->getRouteName();
			$booking->extraCharges = $bookingRoute->getSummary();
			$booking->totalPrice = $bookingRoute->getTotalPrice();
			$booking->discount = ($query->discount > 0) ? $bookingRoute->getDiscount() : '';
			$booking->discountCode = $query->discount_code;
			$booking->total = SiteHelper::formatPrice($total);
			$booking->paymentCharge = ($paymentCharge > 0) ? SiteHelper::formatPrice($paymentCharge) : '';
			$booking->payments = $pInfo;
			$booking->cash = $bookingRoute->getCash();
			$booking->commission = $bookingRoute->getCommission();
			$booking->createdDate = SiteHelper::formatDateTime($bookingRoute->created_date);
			$booking->feedbackLink = config('site.feedback_type') == 2 ? '' : $bookingRoute->getFeedbackLink();

			$driver = $bookingRoute->assignedDriver();
			$vehicle = $bookingRoute->assignedVehicle();

			$booking->driverId = 0;
			$booking->driverName = '';
			$booking->driverAvatar = '';
			$booking->driverPhone = '';
			$booking->driverLicence = '';
			$booking->vehicleId = 0;
			$booking->vehicleRegistrationMark = '';
			$booking->vehicleMake = '';
			$booking->vehicleModel = '';
			$booking->vehicleColour = '';

			if (!empty($driver->id)) {
					$booking->driverId = $driver->id;
					$booking->driverName = $driver->profile->getFullName();
					if (in_array($query->status, array('pending','confirmed','assigned','auto_dispatch','rejected','incomplete','requested','quote'))) {
							$booking->driverAvatar = $driver->avatar ? asset($driver->getAvatarPath()) : '';
							$booking->driverPhone = $driver->profile->mobile_no ? $driver->profile->getTelLink('mobile_no', ['style'=>'color:#333333;']) : '';
							$booking->driverLicence = $driver->profile->pco_licence;
					}
			}

			if (!empty($vehicle->id)) {
					$booking->vehicleId = $vehicle->id;
					$booking->vehicleRegistrationMark = $vehicle->registration_mark;
					$booking->vehicleMake = $vehicle->make;
					$booking->vehicleModel = $vehicle->model;
					$booking->vehicleColour = $vehicle->colour;
			}

			switch( $bookingRoute->status ) {
				case 'assigned':
				case 'auto_dispatch':
				case 'accepted':
				case 'rejected':
				case 'onroute':
				case 'arrived':
				case 'onboard':
					$bookingRoute->status = 'pending';
				break;
			}
			$booking->status = $bookingRoute->getStatus('label');
      $booking->statusHistory = $bookingRoute->getDriverStatusesHtml('customer', 'table', $bookingRoute->driver_id);

			$booking->buttonPay = 0;
			$booking->buttonInvoice = 0;
			$booking->buttonEdit = 0;
			$booking->buttonCancel = 0;
			$booking->buttonDelete = 0;

			// if ( $query->payment_method != 'cash' && $query->payment_method != 'account' && $query->payment_method != 'bacs' && $query->payment_method != 'none' && $query->payment_status != 'paid' ) {
			// 	$booking->buttonPay = 1;
			// }

			if ( in_array($query->status, array('pending','confirmed','assigned','auto_dispatch','rejected','incomplete','requested','quote')) && strtotime($query->date) - (4 * 3600) > time() ) {
					$booking->buttonEdit = 1;
			}

			if ( $this->config->booking_cancel_enable && in_array($query->status, array('pending','confirmed','assigned','auto_dispatch','rejected','incomplete','requested','quote')) && strtotime($query->date) - ($this->config->booking_cancel_time * 3600) > time() ) {
				$booking->buttonCancel = 1;
			}

			//  && $query->payment_method != 'none'
			if ( $this->config->invoice_enabled ) {
				$booking->buttonInvoice = 1;
			}

			$this->success = true;
		}
		else {
			//$this->message['warning'][] = $this->getLang('bookingMsg_NoBooking');
		}

		return $booking;
	}


	public function bookingCancel( $data )
	{
		if ( empty($this->userId) ) {
			return false;
		}

		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		$sql = "SELECT `a`.`id`, `a`.`ref_number`, `a`.`date`, `a`.`status`, `a`.`contact_email`, `a`.`contact_name`
				FROM `{$dbPrefix}booking_route` AS `a`
				LEFT JOIN `{$dbPrefix}booking` AS `b`
				ON `a`.`booking_id`=`b`.`id`
				WHERE /*`b`.`site_id`='" . $this->siteId . "'
				AND*/ `b`.`user_id`='" . $this->userId . "'
				AND `a`.`id`='" . $data->id . "'
			  AND `a`.`deleted_at` IS NULL
				LIMIT 1";

		$query = $db->select($sql);
		if (!empty($query[0])) {
			$query = $query[0];
		}

		if ( !empty($query) ) {

			if ( in_array($query->status, array('pending','confirmed','assigned','auto_dispatch','rejected','incomplete','requested','quote')) && strtotime($query->date) - ($this->config->booking_cancel_time * 3600) > time() )
			{
				$booking = \App\Models\BookingRoute::find($data->id);
				$booking->status = 'canceled';
				$booking->modified_date = Carbon::now();
				$booking->save();

				event(new \App\Events\BookingStatusChanged($booking));

				$msg = $this->getLang('bookingMsg_CanceledSuccess');
				$msg = str_replace('{refNumber}', $query->ref_number, $msg);

				$this->message['success'][] = $msg;
				$this->success = true;
			}
			else
			{
				$msg = $this->getLang('bookingMsg_CanceledFailure');
				$msg = str_replace('{refNumber}', $query->ref_number, $msg);

				$this->message['warning'][] = $msg;
			}
		}
		else {
			$this->message['warning'][] = $this->getLang('bookingMsg_NoBooking');
		}

		return $this->success;
	}

	public function bookingInvoice( $data )
	{
		$invoice = array(
			'refNumber' => '',
			'tmpl' => ''
		);

		if ( empty($this->userId) ) {
			return $invoice;
		}

		if ( !$this->config->invoice_enabled ) {
			$this->message['warning'][] = $this->getLang('bookingMsg_InvoiceDisabled');
			return $invoice;
		}

		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		$sql = "SELECT `a`.*,
					`b`.`site_id`,
					`b`.`user_id`,
					`a`.`id` AS `route_id`,
					`a`.`ref_number` AS `route_ref_number`
				FROM `{$dbPrefix}booking_route` AS `a`
				LEFT JOIN `{$dbPrefix}booking` AS `b`
				ON `a`.`booking_id`=`b`.`id`
				WHERE /*`b`.`site_id`='" . $this->siteId . "'
				AND*/ `b`.`user_id`='" . $this->userId . "'
				AND `a`.`id`='" . $data->id . "'
				AND `a`.`deleted_at` IS NULL
				LIMIT 1";

		$query = $db->select($sql);
		if (!empty($query[0])) {
			$query = $query[0];
		}

		if ( !empty($query) ) {
			$bookingRoute = \App\Models\BookingRoute::find($query->route_id);
			$tmpl = $bookingRoute->getInvoice();

			if ( !empty($data->download) || !empty($data->embed) ) {
					$filename = $bookingRoute->getInvoiceFilename() .'.pdf';
					$html = $tmpl;

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

					if ( !empty($data->embed) ) {
							$pdfInvoice = $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
					}
					else {
							$pdfInvoice = $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
					}
					exit;
			}

			$invoice['refNumber'] = $query->ref_number;
			$invoice['tmpl'] = $tmpl;
			$this->success = true;
		}
		else {
			$this->message['warning'][] = $this->getLang('bookingMsg_NoBooking');
		}

		return $invoice;
	}

	public function getUser()
	{
		$user = new \stdClass();
		$user->id = 0;
		$user->name = '';
		$user->email = '';
		$user->is_company = 0;
		$user->createdDate = '';
		$user->createdDateSince = '';
		$user->title = '';
		$user->firstName = '';
		$user->lastName = '';
		$user->mobileNumber = '';
		$user->telephoneNumber = '';
		$user->emergencyNumber = '';
		$user->address = '';
		$user->city = '';
		$user->postcode = '';
		$user->state = '';
		$user->country = '';
		$user->isCompany = 0;
		$user->isAccountPayment = 0;
		$user->companyName = '';
		$user->companyNumber = '';
		$user->companyTaxNumber = '';
		$user->departments = [];
		$user->avatar_path = asset_url('images','placeholders/avatar.png');

		if ( empty($this->userId) ) {
				return $user;
		}

    $model = \App\Models\Customer::select('user.id', 'user.name', 'user.email', 'user.is_company', 'user.created_date',
					'user.departments', 'user.avatar',
					'user_customer.title', 'user_customer.first_name', 'user_customer.last_name', 'user_customer.mobile_number',
					'user_customer.telephone_number', 'user_customer.emergency_number', 'user_customer.company_name', 'user_customer.company_number', 'user_customer.company_tax_number',
					'user_customer.is_account_payment',
					'user_customer.address', 'user_customer.city', 'user_customer.postcode', 'user_customer.state', 'user_customer.country')
      ->join('user_customer', 'user_customer.user_id', '=', 'user.id')
      ->where('user.id', $this->userId )
      ->where('user.site_id', $this->siteId )
      ->first();

		if ( !empty($model->id) ) {
				$user->id = $model->id;
				$user->name = $model->name;
				$user->email = $model->email;
				$user->is_company = $model->is_company;
				$user->createdDate = $model->created_date;
				$user->createdDateSince = trans('driver/account.member_since') .' '. Carbon::parse($model->created_date)->diffForHumans(null, true, false, 2);
				$user->title = $model->title;
				$user->firstName = $model->first_name;
				$user->lastName = $model->last_name;
				$user->mobileNumber = $model->mobile_number;
				$user->telephoneNumber = $model->telephone_number;
				$user->emergencyNumber = $model->emergency_number;
				$user->address = $model->address;
				$user->city = $model->city;
				$user->postcode = $model->postcode;
				$user->state = $model->state;
				$user->country = $model->country;

				$user->isCompany = $model->is_company;
				$user->companyName = $model->company_name ?: '';
				$user->companyNumber = $model->company_number ?: '';
				$user->companyTaxNumber = $model->company_tax_number ?: '';
				$user->isAccountPayment = $model->is_account_payment ? 1 : 0;
				$user->departments = $model->departments ?: [];
				$user->avatar = $model->avatar;
				$user->avatar_path = $model->getAvatarPath();

				$this->success = true;
		}
		else {
				$this->message['warning'][] = $this->getLang('userMsg_NoUser');
		}

		return $user;
	}


	public function saveUser( $data )
	{
			if ( empty($this->userId) ) {
					return false;
			}

			$db = DB::connection();
			$dbPrefix = get_db_prefix();

			$data->title = self::makeSafe($data->title);
			$data->firstName = self::makeSafe($data->firstName);
			$data->lastName = self::makeSafe($data->lastName);
			$data->address = self::makeSafe($data->address);
			$data->city = self::makeSafe($data->city);
			$data->postcode = self::makeSafe($data->postcode);
			$data->state = self::makeSafe($data->state);
			$data->country = self::makeSafe($data->country);

			$data->companyName = self::makeSafe($data->companyName);
			$data->companyNumber = self::makeSafe($data->companyNumber);
			$data->companyTaxNumber = self::makeSafe($data->companyTaxNumber);

			// if ( empty($data->title) ) {
			// 	$this->message['warning'][] = $this->getLang('userMsg_TitleRequired');
			// }

			if ( empty($data->firstName) ) {
					$this->message['warning'][] = $this->getLang('userMsg_FirstNameRequired');
			}

			if ( empty($data->lastName) ) {
					$this->message['warning'][] = $this->getLang('userMsg_LastNameRequired');
			}

			/*
			if ( empty($data->mobileNumber) ) {
				$this->message['warning'][] = $this->getLang('userMsg_MobileNumberRequired');
			}

			if ( empty($data->telephoneNumber) ) {
				$this->message['warning'][] = $this->getLang('userMsg_TelephoneNumberRequired');
			}

			if ( empty($data->emergencyNumber) ) {
				$this->message['warning'][] = $this->getLang('userMsg_EmergencyNumberRequired');
			}
			*/

			if ( empty($data->address) ) {
					$this->message['warning'][] = $this->getLang('userMsg_AddressRequired');
			}

			if ( empty($data->city) ) {
					$this->message['warning'][] = $this->getLang('userMsg_CityRequired');
			}

			if ( empty($data->postcode) ) {
					$this->message['warning'][] = $this->getLang('userMsg_PostcodeRequired');
			}

			if ( empty($data->state) ) {
					$this->message['warning'][] = $this->getLang('userMsg_CountyRequired');
			}

			if ( empty($data->country) ) {
					$this->message['warning'][] = $this->getLang('userMsg_CountryRequired');
			}

			if ( empty($data->email) ) {
					$this->message['warning'][] = $this->getLang('userMsg_EmailRequired');
			}
			else if ( !filter_var($data->email, FILTER_VALIDATE_EMAIL) ) {
					$this->message['warning'][] = $this->getLang('userMsg_EmailInvalid');
			}

			// Get current user
			$userData = \App\Models\Customer::findOrFail($this->userId);

			// Check if user with the same email already exists
			$sql = "SELECT `id`, `email`, `is_company`
					FROM `{$dbPrefix}user`
					WHERE /*`site_id`='". $this->siteId ."'
					AND*/ `email`='". $data->email ."'
					AND `type`='1'
					LIMIT 1";

			$query = $db->select($sql);
			if (!empty($query[0])) {
					$query = $query[0];
			}

			if ( !empty($userData->id) && $userData->is_company == 1 ) {
					if ( empty($data->companyName) ) {
							$this->message['warning'][] = $this->getLang('userMsg_CompanyNameRequired');
					}

					// if ( empty($data->companyNumber) ) {
					// 	$this->message['warning'][] = $this->getLang('userMsg_CompanyNumberRequired');
					// }
					//
					// if ( empty($data->companyTaxNumber) ) {
					// 	$this->message['warning'][] = $this->getLang('userMsg_CompanyTaxNumberRequired');
					// }
			}

			if ( !empty($data->password) || !empty($data->passwordConfirmation) ) {
					if ( strlen($data->password) < $this->config->password_length_min || strlen($data->password) > $this->config->password_length_max ) {
							$msg = $this->getLang('userMsg_PasswordLength');
							$msg = str_replace('{passwordLengthMin}', $this->config->password_length_min, $msg);
							$msg = str_replace('{passwordLengthMax}', $this->config->password_length_max, $msg);

							$this->message['warning'][] = $msg;
					}

					if ( empty($data->passwordConfirmation) ) {
							$this->message['warning'][] = $this->getLang('userMsg_ConfirmPasswordRequired');
					}
					else if ( $data->passwordConfirmation != $data->password ) {
							$this->message['warning'][] = $this->getLang('userMsg_ConfirmPasswordNotEqual');
					}
					else if ( $data->password == $data->email ) {
							$this->message['warning'][] = $this->getLang('userMsg_PasswordSameAsEmail');
					}
			}

			if ( empty($this->message['warning']) ) {

					if ( !empty($query) && $query->id != $this->userId ) {
							$this->message['warning'][] = $this->getLang('userMsg_EmailTaken');
					}
					else {
			        $filename = null;
			        $userData = \App\Models\Customer::where('id', $this->userId)->first();
			        $userData->name = $data->firstName .' '. $data->lastName;
			        $userData->email = $data->email;

							$departments = [];
			        if (!empty($data->departments)) {
			            foreach ($data->departments as $k => $v) {
			                if (!empty($v)) {
			                    $departments[] = $v;
			                }
			            }
			        }
			        $userData->departments = $departments;

			        if ( !empty($data->password) ) {
			            $userData->password = md5($data->password);
			        }

                    $uploaded = true;

			        if (!empty($_FILES['avatar']['name']) && request()->hasFile('avatar')) {
			            $file = request()->file('avatar');

                        if (in_array($file->getClientOriginalExtension(), ['jpg','jpeg','gif','png'])) {
                            $filename = \App\Helpers\SiteHelper::generateFilename('customer') . '.' . $file->getClientOriginalExtension();
                            $img = \Image::make($file);

                            if ($img->width() > config('site.image_dimensions.avatar.width')) {
                                $img->resize(config('site.image_dimensions.avatar.width'), null, function ($constraint) {
                                    $constraint->aspectRatio();
                                    $constraint->upsize();
                                });
                            }

                            if ($img->height() > config('site.image_dimensions.avatar.height')) {
                                $img->resize(null, config('site.image_dimensions.avatar.height'), function ($constraint) {
                                    $constraint->aspectRatio();
                                    $constraint->upsize();
                                });
                            }

                            $img->save(asset_path('uploads', 'avatars/' . $filename));
                            $uploaded = true;
                        } else {
                            $uploaded = false;
                        }
			        }

			        if ($filename || (int)$data->avatar_delete === 1 && \Storage::disk('avatars')->exists($userData->avatar)) {
			            \Storage::disk('avatars')->delete($userData->avatar);
			            $userData->avatar = $filename;
			        }

							if (empty($userData->avatar) && (int)$data->avatar_delete === 1) {
									$userData->avatar = null;
							}

			        $userData->save();

							$row = new \stdClass();
							$row->user_id = $this->userId;
							$row->title = $data->title;
							$row->first_name = $data->firstName;
							$row->last_name = $data->lastName;
							$row->mobile_number = $data->mobileNumber;
							$row->telephone_number = $data->telephoneNumber;
							$row->emergency_number = $data->emergencyNumber;
							$row->address = $data->address;
							$row->city = $data->city;
							$row->postcode = $data->postcode;
							$row->state = $data->state;
							$row->country = $data->country;

							if ( !empty($userData->id) && $userData->is_company == 1 ) {
								$row->company_name = $data->companyName;
								$row->company_number = $data->companyNumber;
								$row->company_tax_number = $data->companyTaxNumber;
							}
							else {
								$row->company_name = null;
								$row->company_number = null;
								$row->company_tax_number = null;
							}

							$sql = "SELECT `a`.`id`
									FROM `{$dbPrefix}user` AS `a`
									LEFT JOIN `{$dbPrefix}user_customer` AS `b`
									ON (`a`.`id`=`b`.`user_id`)
									WHERE /*`a`.`site_id`='". $this->siteId ."'
									AND*/ `b`.`user_id`='". $this->userId ."'
									LIMIT 1";

							$query = $db->select($sql);
							if (!empty($query[0])) {
								$query = $query[0];
							}

							if ( !empty($query) ) {
									DB::table('user_customer')->where('user_id', $row->user_id)->update((array)$row);
							}
							else {
									$row->id = null;
									DB::table('user_customer')->insert((array)$row);
							}

							$this->message['success'][] = $this->getLang('userMsg_ProfileUpdateSuccess');
							$this->success = true;
					}
			}

			if ($uploaded === false) {
                $this->success = false;
                $this->message = trans('frontend.js.userMsg_AvatarExtension');
            }

			return $this->success;
	}


	public function register( $data )
	{
		if ( $this->config->register_enable == 0 ) {
			$this->message['warning'][] = $this->getLang('userMsg_RegisterNotAvailable');
			return $this->success;
		}

		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		$data->firstName = self::makeSafe($data->firstName);
		$data->lastName = self::makeSafe($data->lastName);

		if ( empty($data->firstName) ) {
			$this->message['warning'][] = $this->getLang('userMsg_FirstNameRequired');
		}

		if ( empty($data->lastName) ) {
			$this->message['warning'][] = $this->getLang('userMsg_LastNameRequired');
		}

		if ( empty($data->email) ) {
			$this->message['warning'][] = $this->getLang('userMsg_EmailRequired');
		}
		else if ( !filter_var($data->email, FILTER_VALIDATE_EMAIL) ) {
			$this->message['warning'][] = $this->getLang('userMsg_EmailInvalid');
		}

		if ( $data->profileType == 'company' )
		{
			if ( empty($data->companyName) ) {
				$this->message['warning'][] = $this->getLang('userMsg_CompanyNameRequired');
			}

			// if ( empty($data->companyNumber) ) {
			// 	$this->message['warning'][] = $this->getLang('userMsg_CompanyNumberRequired');
			// }

			// if ( empty($data->companyTaxNumber) ) {
			// 	$this->message['warning'][] = $this->getLang('userMsg_CompanyTaxNumberRequired');
			// }
		}

		if ( empty($data->password) ) {
			$this->message['warning'][] = $this->getLang('userMsg_PasswordRequired');
		}
		else if ( strlen($data->password) < $this->config->password_length_min || strlen($data->password) > $this->config->password_length_max ) {
			$msg = $this->getLang('userMsg_PasswordLength');
			$msg = str_replace('{passwordLengthMin}', $this->config->password_length_min, $msg);
			$msg = str_replace('{passwordLengthMax}', $this->config->password_length_max, $msg);

			$this->message['warning'][] = $msg;
		}
		else if ( $data->password == $data->email ) {
			$this->message['warning'][] = $this->getLang('userMsg_PasswordSameAsEmail');
		}

		if ( empty($data->passwordConfirmation) ) {
			$this->message['warning'][] = $this->getLang('userMsg_ConfirmPasswordRequired');
		}
		else if ( $data->passwordConfirmation != $data->password ) {
			$this->message['warning'][] = $this->getLang('userMsg_ConfirmPasswordNotEqual');
		}

		if ( empty($data->terms) && config('site.terms_enable') ) {
			$this->message['warning'][] = $this->getLang('userMsg_TermsAndConditionsRequired');
		}

		if ( empty($this->message['warning']) ) {
			$sql = "SELECT `id`
					FROM `{$dbPrefix}user`
					WHERE /*`site_id`='". $this->siteId ."'
					AND*/ `email`='". $data->email ."'
					AND `type`='1'
					LIMIT 1";

			$query = $db->select($sql);
			if (!empty($query[0])) {
				$query = $query[0];
			}

			if ( !empty($query) ) {
				$this->message['warning'][] = $this->getLang('userMsg_EmailTaken');
			}
			else {
				if ( $this->config->register_activation_enable == 1 ) {
					$activated = 0;
				}
				else {
					$activated = 1;
				}

				$row = new \stdClass();
				$row->id = null;
				$row->site_id = $this->siteId;
				$row->name = $data->firstName .' '. $data->lastName;
				$row->email = $data->email;
				$row->password = md5($data->password);
				$row->type = 1;
				$row->ip = $_SERVER['REMOTE_ADDR'];
				$row->token_activation = md5(date('Y-m-d H:i:s') . rand(50000, 1000000));
				$row->created_date = date('Y-m-d H:i:s');
				$row->activated = $activated;
				$row->token_password = '';
				$row->description = '';

				if ( $data->profileType == 'company' ) {
					$row->is_company = 1;
				}
				else {
					$row->is_company = 0;
				}

				$row->last_visit_date = date('Y-m-d H:i:s');
				$row->published = 1;

				$query = DB::table('user')->insertGetId((array)$row);
				$row->id = $query;

				if ( !empty($query) ) {
					$row2 = new \stdClass();
					$row2->id = null;
					$row2->user_id = $row->id;
					$row2->first_name = $data->firstName;
					$row2->last_name = $data->lastName;
					$row2->mobile_number = '';
					$row2->telephone_number = '';
					$row2->emergency_number = '';
					$row2->company_name = '';

					if ( $data->profileType == 'company' ) {
						$row2->company_name = $data->companyName;
						$row2->company_number = $data->companyNumber;
						$row2->company_tax_number = $data->companyTaxNumber;
						$row2->is_account_payment = config('site.booking_allow_account_payment') ? 1 : 0;
					}
					else {
						$row2->company_name = null;
						$row2->company_number = null;
						$row2->company_tax_number = null;
						$row2->is_account_payment = 0;
					}

					$row2->address = '';
					$row2->city = '';
					$row2->postcode = '';
					$row2->state = '';
					$row2->country = '';

					DB::table('user_customer')->insert((array)$row2);


					// Common email variables
					$qProfileConfig = \App\Models\Config::getBySiteId($row->site_id)->loadLocale()->mapData()->getData();
					$pConfig = (array)$qProfileConfig;

					$eCompany = (object)[
						'name' => $pConfig['company_name'],
						'phone' => $pConfig['company_telephone'],
						'email' => $pConfig['company_email'],
						'address' => SiteHelper::nl2br2($pConfig['company_address']),
						'url_home' => $pConfig['url_home'],
						'url_feedback' => $pConfig['url_feedback'],
						'url_contact' => $pConfig['url_contact'],
						'url_booking' => $pConfig['url_booking'],
						'url_customer' => $pConfig['url_customer'],
					];

					$eSettings = (object)[
						'booking_summary_enable' => $pConfig['booking_summary_enable'],
					];

					$sender = [
						$eCompany->email,
						$eCompany->name
					];

					$recipient = [
						$row->email,
						$row->name
					];

					if ( $this->config->register_activation_enable == 1 )
					{
						$subject = trans('emails.customer_account_activation.subject');

						try {
								$sent = \Mail::send([
												'html' => 'emails.customer_account_activation',
												// 'text' => 'emails.customer_account_activation_plain'
										], [
												'subject' => $subject,
												'additionalMessage' => '',
												'company' => $eCompany,
												'settings' => $eSettings,
												'customerName' => $row->name,
												'token' => $row->token_activation,
												'link' => url('/customer') .'#register/activation/'. $row->token_activation
												// 'link' => $data->baseURL .'#register/activation/'. $row->token_activation
										],
										function ($message) use ($sender, $recipient, $subject) {
												$message->from($sender[0], $sender[1])
																->to($recipient[0], $recipient[1])
																->subject($subject);
										}
								);
						}
						catch (\Exception $e) {
						    $sent = false;
						}

						$resendLink = url('/customer') .'#register/resend/'. $row->email;
						// $resendLink = $data->baseURL .'#register/resend/'. $row->email;

						$msg = $this->getLang('userMsg_Resend');
						$msg = str_replace('{userEmail}', $row->email, $msg);
						$msg = str_replace('{resendLink}', $resendLink, $msg);

						$this->message['success'][] = $msg;
 					}
					else
					{
						$subject = trans('emails.customer_account_welcome.subject');

						try {
								$sent = \Mail::send([
												'html' => 'emails.customer_account_welcome',
												// 'text' => 'emails.customer_account_welcome_plain'
										], [
												'subject' => $subject,
												'additionalMessage' => '',
												'company' => $eCompany,
												'settings' => $eSettings,
												'customerName' => $row->name,
												'link' => url('/customer') .'#login'
												// 'link' => $data->baseURL .'#login'
										],
										function ($message) use ($sender, $recipient, $subject) {
												$message->from($sender[0], $sender[1])
																->to($recipient[0], $recipient[1])
																->subject($subject);
										}
								);
						}
						catch (\Exception $e) {
						    $sent = false;
						}

						$this->message['success'][] = $this->getLang('userMsg_RegisterSuccess');
					}

					$this->success = true;
				}
				else {
					$this->message['warning'][] = $this->getLang('userMsg_RegisterFailure');
				}
			}
		}

		return $this->success;
	}


	public function registerActivation( $data )
	{
		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		if ( empty($data->token) ) {
			$this->message['warning'][] = $this->getLang('userMsg_TokenRequired');
		}

		if ( empty($this->message['warning']) ) {
			$sql = "SELECT `id`, `email`, `name`, `activated`
					FROM `{$dbPrefix}user`
					WHERE /*`site_id`='". $this->siteId ."'
					AND*/ `token_activation`='". $data->token ."'
					LIMIT 1";

			$query = $db->select($sql);
			if (!empty($query[0])) {
				$query = $query[0];
			}

			if ( !empty($query) ) {
				if ( $query->activated == 1 ) {
					$this->message['warning'][] = $this->getLang('userMsg_ActivationDone');
				}
				else {
					$row = new \stdClass();
					$row->id = $query->id;
					$row->site_id = $this->siteId;
					$row->activated = 1;

					DB::table('user')->where('id', $row->id)->update((array)$row);


					// Common email variables
					$qProfileConfig = \App\Models\Config::getBySiteId($row->site_id)->loadLocale()->mapData()->getData();
					$pConfig = (array)$qProfileConfig;

					$eCompany = (object)[
						'name' => $pConfig['company_name'],
						'phone' => $pConfig['company_telephone'],
						'email' => $pConfig['company_email'],
						'address' => SiteHelper::nl2br2($pConfig['company_address']),
						'url_home' => $pConfig['url_home'],
						'url_feedback' => $pConfig['url_feedback'],
						'url_contact' => $pConfig['url_contact'],
						'url_booking' => $pConfig['url_booking'],
						'url_customer' => $pConfig['url_customer'],
					];

					$eSettings = (object)[
						'booking_summary_enable' => $pConfig['booking_summary_enable'],
					];

					$sender = [
						$eCompany->email,
						$eCompany->name
					];

					$recipient = [
						$query->email,
						$query->name
					];

					$subject = trans('emails.customer_account_welcome.subject');

					try {
							$sent = \Mail::send([
									'html' => 'emails.customer_account_welcome',
									// 'text' => 'emails.customer_account_welcome_plain'
								], [
									'subject' => $subject,
									'additionalMessage' => '',
									'company' => $eCompany,
									'settings' => $eSettings,
									'customerName' => $query->name,
									'link' => url('/customer') .'#login'
									// 'link' => $data->baseURL .'#login'
								],
								function ($message) use ($sender, $recipient, $subject) {
									$message->from($sender[0], $sender[1])
											->to($recipient[0], $recipient[1])
											->subject($subject);
								}
							);
					}
					catch (\Exception $e) {
					    $sent = false;
					}

					$this->message['success'][] = $this->getLang('userMsg_ActivationSuccess');
				}

				$this->success = true;
			}
			else {
				$this->message['warning'][] = $this->getLang('userMsg_TokenInvalid');
			}
		}

		return $this->success;
	}


	public function registerResend( $data )
	{
		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		if ( empty($data->email) ) {
			$this->message['warning'][] = $this->getLang('userMsg_EmailRequired');
		}

		if ( empty($this->message['warning']) ) {
			$sql = "SELECT `id`, `email`, `name`, `token_activation`, `activated`, `site_id`
					FROM `{$dbPrefix}user`
					WHERE /*`site_id`='". $this->siteId ."'
					AND*/ `email`='". $data->email ."'
					LIMIT 1";

			$query = $db->select($sql);
			if (!empty($query[0])) {
				$query = $query[0];
			}

			if ( !empty($query) ) {
				if ( $query->activated == 1 ) {
					$this->message['warning'][] = $this->getLang('userMsg_ActivationDone');
				}
				else
				{
					// Common email variables
					$qProfileConfig = \App\Models\Config::getBySiteId($query->site_id)->loadLocale()->mapData()->getData();
					$pConfig = (array)$qProfileConfig;

					$eCompany = (object)[
						'name' => $pConfig['company_name'],
						'phone' => $pConfig['company_telephone'],
						'email' => $pConfig['company_email'],
						'address' => SiteHelper::nl2br2($pConfig['company_address']),
						'url_home' => $pConfig['url_home'],
						'url_feedback' => $pConfig['url_feedback'],
						'url_contact' => $pConfig['url_contact'],
						'url_booking' => $pConfig['url_booking'],
						'url_customer' => $pConfig['url_customer'],
					];

					$eSettings = (object)[
						'booking_summary_enable' => $pConfig['booking_summary_enable'],
					];

					$sender = [
						$eCompany->email,
						$eCompany->name
					];

					$recipient = [
						$query->email,
						$query->name
					];

					if ( $this->config->register_activation_enable == 1 )
					{
						$subject = trans('emails.customer_account_activation.subject');

						try {
								$sent = \Mail::send([
										'html' => 'emails.customer_account_activation',
										// 'text' => 'emails.customer_account_activation_plain'
									], [
										'subject' => $subject,
										'additionalMessage' => '',
										'company' => $eCompany,
										'settings' => $eSettings,
										'customerName' => $query->name,
										'token' => $query->token_activation,
										'link' => url('/customer') .'#register/activation/'. $query->token_activation
										// 'link' => $data->baseURL .'#register/activation/'. $query->token_activation
									],
									function ($message) use ($sender, $recipient, $subject) {
										$message->from($sender[0], $sender[1])
												->to($recipient[0], $recipient[1])
												->subject($subject);
									}
								);
						}
						catch (\Exception $e) {
						    $sent = false;
						}

						$resendLink = url('/customer') .'#register/resend/'. $query->email;
						// $resendLink = $data->baseURL .'#register/resend/'. $query->email;

						$msg = $this->getLang('userMsg_Resend');
						$msg = str_replace('{userEmail}', $row->email, $msg);
						$msg = str_replace('{resendLink}', $resendLink, $msg);

						$this->message['success'][] = $msg;
					}
					else
					{
						$subject = trans('emails.customer_account_welcome.subject');

						try {
								$sent = \Mail::send([
								        'html' => 'emails.customer_account_welcome',
								        // 'text' => 'emails.customer_account_welcome_plain'
								    ], [
								        'subject' => $subject,
								        'additionalMessage' => '',
								        'company' => $eCompany,
								        'settings' => $eSettings,
								        'customerName' => $query->name,
												'link' => url('/customer') .'#login'
								        // 'link' => $data->baseURL .'#login'
								    ],
								    function ($message) use ($sender, $recipient, $subject) {
								        $message->from($sender[0], $sender[1])
								                ->to($recipient[0], $recipient[1])
								                ->subject($subject);
								    }
								);
						}
						catch (\Exception $e) {
						    $sent = false;
						}

						$this->message['success'][] = $this->getLang('userMsg_RegisterSuccess');
					}
				}

				$this->success = true;
			}
			else {
				$this->message['warning'][] = $this->getLang('userMsg_NoUser');
			}
		}

		return $this->success;
	}


	public function login( $data )
	{
		if ( $this->config->login_enable == 0 ) {
			$this->message['warning'][] = $this->getLang('userMsg_LoginNotAvailable');
			return $this->success;
		}

		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		if ( empty($data->email) ) {
			$this->message['warning'][] = $this->getLang('userMsg_EmailRequired');
		}
		else if ( !filter_var($data->email, FILTER_VALIDATE_EMAIL) ) {
			$this->message['warning'][] = $this->getLang('userMsg_EmailInvalid');
		}

		if ( empty($data->password) ) {
			$this->message['warning'][] = $this->getLang('userMsg_PasswordRequired');
		}

		if ( empty($this->message['warning']) ) {

			$sql = "SELECT `id`, `activated`, `published`
					FROM `{$dbPrefix}user`
					WHERE /*`site_id`='". $this->siteId ."'
					AND*/ `email`='". $data->email ."'
					AND `password`='". md5($data->password) ."'
					AND `type`='1'
					LIMIT 1";

			$query = $db->select($sql);
			if (!empty($query[0])) {
				$query = $query[0];
			}

			if ( !empty($query) ) {
				$userId = (int)$query->id;

				if ( $query->activated == 0 ) {
					$this->message['warning'][] = $this->getLang('userMsg_ActivationUnfinished');
				}

				if ( $query->published == 0 ) {
					$this->message['warning'][] = $this->getLang('userMsg_Blocked');
				}

				if ( $query->activated == 1 && $query->published == 1 ) {
					session(['etoUserId' => $userId]);

					$row = new \stdClass();
					$row->id = $userId;
					$row->site_id = $this->siteId;
					$row->last_visit_date = date('Y-m-d H:i:s');

					DB::table('user')->where('id', $row->id)->update((array)$row);

					$this->message['success'][] = $this->getLang('userMsg_LoginSuccess');
					$this->success = true;
				}
			}
			else {
				$this->message['warning'][] = $this->getLang('userMsg_LoginFailure');
			}
		}

		return $this->success;
	}


	public function password( $data )
	{
		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		if ( empty($data->email) ) {
			$this->message['warning'][] = $this->getLang('userMsg_EmailRequired');
		}
		else if ( !filter_var($data->email, FILTER_VALIDATE_EMAIL) ) {
			$this->message['warning'][] = $this->getLang('userMsg_EmailInvalid');
		}

		if ( empty($this->message['warning']) ) {

			$sql = "SELECT `id`, `name`, `email`, `site_id`
					FROM `{$dbPrefix}user`
					WHERE /*`site_id`='". $this->siteId ."'
					AND*/ `email`='". $data->email ."'
					LIMIT 1";

			$query = $db->select($sql);
			if (!empty($query[0])) {
				$query = $query[0];
			}

			if ( !empty($query) )
			{
				$row = new \stdClass();
				$row->id = $query->id;
				$row->site_id = $this->siteId;
				$row->token_password = md5(date('Y-m-d H:i:s') . rand(50000, 1000000));

				DB::table('user')->where('id', $row->id)->update((array)$row);


				// Common email variables
				$qProfileConfig = \App\Models\Config::getBySiteId($query->site_id)->loadLocale()->mapData()->getData();
				$pConfig = (array)$qProfileConfig;

				$eCompany = (object)[
					'name' => $pConfig['company_name'],
					'phone' => $pConfig['company_telephone'],
					'email' => $pConfig['company_email'],
					'address' => SiteHelper::nl2br2($pConfig['company_address']),
					'url_home' => $pConfig['url_home'],
					'url_feedback' => $pConfig['url_feedback'],
					'url_contact' => $pConfig['url_contact'],
					'url_booking' => $pConfig['url_booking'],
					'url_customer' => $pConfig['url_customer'],
				];

				$eSettings = (object)[
					'booking_summary_enable' => $pConfig['booking_summary_enable'],
				];

				$sender = [
					$eCompany->email,
					$eCompany->name
				];

				$recipient = [
					$query->email,
					$query->name
				];

				$subject = trans('emails.customer_account_password.subject');

				try {
						$sent = \Mail::send([
								'html' => 'emails.customer_account_password',
								// 'text' => 'emails.customer_account_password_plain'
							], [
								'subject' => $subject,
								'additionalMessage' => '',
								'company' => $eCompany,
								'settings' => $eSettings,
								'customerName' => $query->name,
								'token' => $row->token_password,
								'link' => url('/customer') .'#password/new/'. $row->token_password,
								// 'link' => $data->baseURL .'#password/new/'. $row->token_password,
							],
							function ($message) use ($sender, $recipient, $subject) {
								$message->from($sender[0], $sender[1])
									->to($recipient[0], $recipient[1])
									->subject($subject);
							}
						);
				}
				catch (\Exception $e) {
				    $sent = false;
				}

				$this->message['success'][] = $this->getLang('userMsg_PasswordReset');
				$this->success = true;
			}
			else {
				$this->message['warning'][] = $this->getLang('userMsg_NoUser');
			}
		}

		return $this->success;
	}


	public function passwordNew( $data )
	{
		$db = DB::connection();
		$dbPrefix = get_db_prefix();

		if ( empty($data->token) ) {
			$this->message['warning'][] = $this->getLang('userMsg_TokenRequired');
		}

		if ( empty($data->password) ) {
			$this->message['warning'][] = $this->getLang('userMsg_PasswordRequired');
		}
		else if ( strlen($data->password) < $this->config->password_length_min || strlen($data->password) > $this->config->password_length_max ) {
			$msg = $this->getLang('userMsg_PasswordLength');
			$msg = str_replace('{passwordLengthMin}', $this->config->password_length_min, $msg);
			$msg = str_replace('{passwordLengthMax}', $this->config->password_length_max, $msg);

			$this->message['warning'][] = $msg;
		}

		if ( empty($data->passwordConfirmation) ) {
			$this->message['warning'][] = $this->getLang('userMsg_ConfirmPasswordRequired');
		}
		else if ( $data->passwordConfirmation != $data->password ) {
			$this->message['warning'][] = $this->getLang('userMsg_ConfirmPasswordNotEqual');
		}

		if ( empty($this->message['warning']) ) {
			$sql = "SELECT `id`, `email`
					FROM `{$dbPrefix}user`
					WHERE /*`site_id`='". $this->siteId ."'
					AND*/ `token_password`='". $data->token ."'
					LIMIT 1";

			$query = $db->select($sql);
			if (!empty($query[0])) {
				$query = $query[0];
			}

			if ( !empty($query) ) {
				if ( $data->password == $query->email ) {
					$this->message['warning'][] = $this->getLang('userMsg_PasswordSameAsEmail');
				}
				else {
					$row = new \stdClass();
					$row->id = $query->id;
					$row->site_id = $this->siteId;
					$row->token_password = '';
					$row->password = md5($data->password);

					DB::table('user')->where('id', $row->id)->update((array)$row);

					$this->message['success'][] = $this->getLang('userMsg_PasswordUpdateSuccess');
					$this->success = true;
				}
			}
			else {
				$this->message['warning'][] = $this->getLang('userMsg_TokenInvalid');
			}
		}

		return $this->success;
	}


	public function logout()
	{
		session(['etoUserId' => 0]);

		if ( session('etoUserId') <= 0 ) {
			$this->message['success'][] = $this->getLang('userMsg_LogoutSuccess');
			$this->success = true;
		}
		else {
			$this->message['warning'][] = $this->getLang('userMsg_LogoutFailure');
		}

		return $this->success;
	}


	public function stripHtmlTags( $text )
	{
		$text = preg_replace(array(
			// Remove invisible content
			'@<head[^>]*?>.*?</head>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu'
		), array(
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			''
		), $text);

		return strip_tags($text);
	}

	public function makeSafe( $text )
	{
		$text = self::stripHtmlTags($text);
		$bad = array("=", "<", ">", "/", "\"", "`", "~", "'", "$", "%", "#");

		$text = str_replace($bad, '', $text);
		return $text;
	}
}


// Global vars
global $data, $gConfig, $gLanguage, $siteId, $userId;


// Settings
$data = array(
	'message' => array(),
	'success' => false
);


// Init ETO
$etoAPI = new ETOFrontendAPI();

if ( $etoAPI->siteId > 0 ) {
		$siteId = $etoAPI->siteId;
		$siteName = $etoAPI->siteName;
		$userId = $etoAPI->userId;
}
else {
		$siteId = 0;
		$siteName = '';
		$userId = 0;

		$data['message'][] = 'No profile was found!';
}


//$data['ETO INI config'] = $etoAPI->config;
//$data['ETO INI configBrowser'] = $etoAPI->configBrowser;


$db = DB::connection();
$dbPrefix = get_db_prefix();


// Config
$gConfig = array();

$sql = "SELECT `key`, `value`
				FROM `{$dbPrefix}config`
				WHERE `site_id`='".$siteId."'
				ORDER BY `key` ASC";

$resultsConfig = $db->select($sql);

if ( !empty($resultsConfig) ) {
		foreach($resultsConfig as $key => $value) {
				$gConfig[$value->key] = $value->value;
		}
}
else {
		$data['message'][] = 'No global config was found!';
}


// Language
$gLanguage = array();
$resultsLanguage = array();

$gConfig['language'] = app()->getLocale();

$resultsLanguage = trans('frontend.old');

if ( !empty($resultsLanguage) ) {
		$gLanguage = $resultsLanguage;
}
else {
		$data['message'][] = 'No language was found!';
}

// Panels
if ( !empty($etoPost['task']) ) {
		$task = (string)$etoPost['task'];
}
else {
		$task = '';
}

if ( !empty($etoPost['action']) ) {
		$action = (string)$etoPost['action'];
}
else {
		$action = '';
}

switch( $task ) {
		case 'init':

				include __DIR__ .'/FrontendInit.php';

		break;
		case 'booking':

				include __DIR__ .'/FrontendBooking.php';

		break;
		case 'user':

				include __DIR__ .'/FrontendUser.php';

		break;
		case 'locations':

				include __DIR__ .'/FrontendLocations.php';

		break;
		case 'scheduled_locations':

				include __DIR__ .'/FrontendScheduledLocations.php';

		break;
		case 'scheduled_availability':

				include __DIR__ .'/FrontendScheduledAvailability.php';

		break;
		case 'initV1':

				include __DIR__ .'/FrontendInitV1.php';

		break;
		case 'submit':

				include __DIR__ .'/FrontendSubmit.php';

		break;
		case 'finish':

				include __DIR__ .'/FrontendFinish.php';

		break;
		case 'notify':

			include __DIR__ .'/FrontendNotify.php';

		break;
		case 'quote':

			global $quoteType;
			$quoteType = 'frontend';
			include __DIR__ .'/FrontendQuote.php';

		break;
}

$response = $data;
