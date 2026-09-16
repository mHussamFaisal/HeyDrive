<?php
use App\Helpers\SiteHelper;

$finishType = (string)$etoPost['finishType'];
$bID = (string)$etoPost['bID'];
$tID = (string)$etoPost['tID'];

// Get booking
$sql = "SELECT *
        FROM `{$dbPrefix}booking`
        WHERE `unique_key`='". $bID ."'
        LIMIT 1";

$qBooking = $db->select($sql);
if ( !empty($qBooking[0]) ) {
    $qBooking = $qBooking[0];
}

if ( !empty($qBooking) ) {

		// $tIDs = explode('|', base64_decode($tID));
		// $tList = [];

		// foreach($tIDs as $tKey => $tValue) {
		// 	if ( $tValue ) {
		// 		$transaction = \App\Models\Transaction::where('relation_type', '=', 'booking_route')
		// 			// ->where('relation_id', '=', $tValue) // Check booking id
		// 			// ->where('relation_id', '=', $qBooking->id)
		// 			->where('id', '=', $tValue)
		// 			->first();
		// 	}
		// 	else {
		// 		$transaction = new \stdClass();
		// 	}
          //
		// 	$tList[] = $transaction;
		// }
          //
		// dd($tList);
		// dd($tIDs);


    // Transaction
    if ( !empty($tID) ) {
        $transaction = \App\Models\Transaction::where('relation_type', 'booking')
            ->where('relation_id', $qBooking->id)
            ->where('unique_key', $tID)
            ->first();
    }
		else {
				$transaction = new \stdClass();
		}

    // Payment
    $sql = "SELECT *
            FROM `{$dbPrefix}payment`
            WHERE `published`='1'
            AND `id`='". $transaction->payment_id ."'
            LIMIT 1";

    $qPayment = $db->select($sql);
    if ( !empty($qPayment[0]) ) {
        $qPayment = $qPayment[0];
    }

    if ( !empty($qPayment) ) {
        if ( !empty($qPayment->params) ) {
            $qPayment->params = json_decode($qPayment->params);
        }
        $payment = $qPayment;
    }
		else {
        $payment = new \stdClass();
    }

		// Get route
    $sql = "SELECT *
            FROM `{$dbPrefix}booking_route`
            WHERE `booking_id`='". $qBooking->id ."'
            ORDER BY `ref_number` ASC";

    $qBookingRoute = $db->select($sql);

    // Get customer data
    $firstRouteID = 0;
    $contactTitle = '';
    $contactName = '';
    $contactEmail = '';
    $contactMobile = '';
    $bookingStatus = '';

    if ( !empty($qBookingRoute) ) {
        foreach($qBookingRoute as $key => $value) {
            if (	$value->route == 1 ) {
                $firstRouteID = $value->id;
                $contactTitle = $value->contact_title;
                $contactName = $value->contact_name;
                $contactEmail = $value->contact_email;
                $contactMobile = $value->contact_mobile;
                $bookingStatus = $value->status;
            }
        }
    }

		// Generate payment form
    if ( $finishType == 'payment' && !in_array($payment->method, array('cash', 'account', 'bacs', 'none')) ) {
				if ($transaction->status != 'paid') {
						// Default
						$total = $transaction->amount + $transaction->payment_charge;
						$orderDesc = $transaction->name ?: 'Booking';

						// URLs
            $bookingUrl = $gConfig['url_booking'];
            if ( strpos($bookingUrl, '?') === false ) {
                $bookingUrlPrefix = '?';
            }
						else {
                $bookingUrlPrefix = '&';
            }

            $finishUrl = url('/booking') .'?finishType=paymentThankYou&bID='. $qBooking->unique_key .'&tID='. $transaction->unique_key;
						$notifyUrl = url('/etov2') .'?apiType=frontend&task=notify&pMethod='. $payment->method .'&tID='. $transaction->unique_key;

            switch( $payment->method ) {
                case 'epdq':

										// http://domain.com/eto/etov2?apiType=frontend&task=notify&pMethod=epdq
                    $epdqPassPhrase = trim($payment->params->pass_phrase);

                    if ( $gConfig['language'] == 'pt-PT' ) {
                        $langCode = 'pt_PT';
                    }
                    elseif ( $gConfig['language'] == 'pt-BR' ) {
                        $langCode = 'pt_BR';
                    }
                    elseif ( $gConfig['language'] == 'es-ES' ) {
                        $langCode = 'es_ES';
                    }
                    else {
                        $langCode = 'en_US';
                    }

                    if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                         $langCode = trim($payment->params->language_code);
                    }

										$totalAmount = $total * 100; // Production amount

                    if ( !empty((int)$payment->params->test_mode) ) { // Test
                        $paymentUrl = 'https://mdepayments.epdq.co.uk/ncol/test/orderstandard.asp';

												if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount * 100;  // Test amount
												}
                    }
										else { // Production
                        $paymentUrl = 'https://payments.epdq.co.uk/ncol/prod/orderstandard.asp';
                    }

                    $epdqParams = array(
                        'ACCEPTURL' => $finishUrl,
                        'AMOUNT' => $totalAmount,
                        'BACKURL' => $bookingUrl,
                        'CANCELURL' => $bookingUrl,
                        'CATALOGURL' => $bookingUrl,
                        'COM' => $orderDesc .' #'. $qBooking->ref_number,
                        'CN' => $contactName,
                        'CURRENCY' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP',
                        'DECLINEURL' => $bookingUrl,
                        'EMAIL' => $contactEmail,
                        'EXCEPTIONURL' => $bookingUrl,
                        'HOMEURL' => $bookingUrl,
                        'LANGUAGE' => $langCode,
                        'OPERATION' => ($payment->params->operation_mode) ? trim($payment->params->operation_mode) : 'SAL', // RES: request for authorisation | SAL: request for sale (payment)
                        // 'ORDERID' => $qBooking->ref_number,
                        'ORDERID' => $transaction->unique_key,
                        'OWNERTELNO' => $contactMobile,
                        // 'OWNERADDRESS' => $qBooking->contact_address,
                        // 'OWNERZIP' => $qBooking->contact_postcode,
                        'PARAMVAR' => trim($payment->params->paramvar),
                        'PSPID' => trim($payment->params->pspid),
                        'TITLE' => str_replace(' ',' ',$gConfig['company_name'])
                    );

                    ksort($epdqParams);

                    $html = '';
                    if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
                        $html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
                    }
                    else {
                        $html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
                    }
                    $html .= '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';

                    $epdqShasign = '';
                    foreach($epdqParams as $key => $value) {
                        if ( !empty($value) ) {
                            $html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
                            $epdqShasign .= $key.'='.$value.$epdqPassPhrase;
                        }
                    }
                    $epdqShasign = strtoupper(sha1($epdqShasign));

                    $html .= '<input type="hidden" name="SHASIGN" value="'.$epdqShasign.'">';
                    $html .= '<input type="submit" name="submit" value="'.$gLanguage['API']['PAYMENT_BUTTON'].'" class="button btn btn-primary">';
										$html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
										$html .= '</form>';

                break;
                case 'paypal':

                    // https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/
										// https://www.paypalobjects.com/en_AU/vhelp/paypalmanager_help/credit_card_numbers.htm
										// https://developer.paypal.com/developer/accounts
										// Test card: 4111111111111111

										$totalAmount = $total; // Production amount

                    if ( !empty((int)$payment->params->test_mode) ) { // Test
                        $paymentUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

												if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount; // Test amount
												}
                    }
										else { // Production
                        $paymentUrl = 'https://www.paypal.com/cgi-bin/webscr';
                    }

                    $language = explode('-', $gConfig['language']);
                    $langCode = ($language[1]) ? $language[1] : 'GB';

                    if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                         $langCode = trim($payment->params->language_code);
                    }

                    $paymentParams = array(
                        'cmd' => '_xclick',
                        'lc' => $langCode,
                        'business' => trim($payment->params->paypal_email),
                        'return' => $finishUrl,
                        'notify_url' => $notifyUrl,
                        'cancel_return' => $bookingUrl,
                        'charset' => 'utf-8',
                        'rm' => 0, // 0-1 get, 2 post
                        'no_note' => 1,
                        'no_shipping' => 1,
                        'first_name' => '', // $contactName
                        'last_name' => '',
                        'address1' => '',
                        'address2' => '',
                        'zip' => '',
                        'city' => '',
                        'state' => '',
                        'country' => '',
                        'email' => $contactEmail,
                        'night_phone_b' => '', // $contactMobile
                        'item_name' => $orderDesc .' #'. $qBooking->ref_number,
                        'quantity' => 1,
                        'amount' => $totalAmount,
                        'currency_code' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP'
                    );

                    ksort($paymentParams);
                    $html = '';

                    if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
                        $html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
                    }
                    else {
                        $html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
                    }

                    $html .= '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';
                    foreach($paymentParams as $key => $value) {
                        if ( !empty($value) ) {
                            $html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
                        }
                    }
                    $html .= '<input type="submit" name="submit" value="'.$gLanguage['API']['PAYMENT_BUTTON'].'" class="button btn btn-primary">';
										$html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
                    $html .= '</form>';

                break;
								case 'payzone':

										// https://www.payzone.co.uk/media/1989/hostedpaymentform-integration-docs.pdf
										// https://mms.payzoneonlinepayments.com/Login.aspx

										$paymentUrl = 'https://mms.payzoneonlinepayments.com/Pages/PublicPages/PaymentForm.aspx';

										$totalAmount = $total * 100; // Production amount

										if ( !empty((int)$payment->params->test_mode) ) { // Test
												if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount * 100; // Test amount
												}
                    }

										//Function to get date/time stamp as required by the gateway
										function gatewaydatetime() {
												return date('Y-m-d H:i:s P');
										}

										//Function to generate a unique OrderID for the transaction (The OrderID can be any AlphaNumeric string - e.g. your own carts order ID if applicable
										function guid()	{
												if (function_exists('com_create_guid')) {
														return com_create_guid();
												}
                        else {
														mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
														$charid = strtoupper(md5(uniqid(rand(), true)));
														$hyphen = chr(45);// "-"
														$uuid = chr(123)// "{"
																		.substr($charid, 0, 8).$hyphen
																		.substr($charid, 8, 4).$hyphen
																		.substr($charid,12, 4).$hyphen
																		.substr($charid,16, 4).$hyphen
																		.substr($charid,20,12)
																		.chr(125);// "}"
														return $uuid;
												}
										}

										// Function to remove invalid characters / characters which will cause the gateway to error.
										function stripGWInvalidChars($strToCheck) {
												$toReplace = array("#","\\",">","<", "\"", "[", "]");
												$cleanString = str_replace($toReplace, "", $strToCheck);
												return $cleanString;
										}

										function createhash( $PreSharedKey, $MerchantID, $Password, $paymentParams ) {
												$tempParams = array(
														'PreSharedKey' => $PreSharedKey,
														'MerchantID' => $MerchantID,
														'Password' => $Password
												);
												$paymentParams = array_merge($tempParams, $paymentParams);
												$paymentParamsString = '';
												foreach($paymentParams as $key => $value) {
														$prefix =  '&';
														if ( empty($paymentParamsString) ) {
																$prefix =  '';
														}
														$paymentParamsString .= $prefix . $key .'='. $value;
												}
												return sha1($paymentParamsString);
										}

										$PreSharedKey = trim($payment->params->pre_shared_key);
										$MerchantID = trim($payment->params->merchant_id); // live or test
										$Password = trim($payment->params->password);

										$langCode = '';

										if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
												 $langCode = trim($payment->params->language_code);
										}

										$paymentParams = array(
												'Amount' => $totalAmount,
												'CurrencyCode' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : '826',
												'EchoAVSCheckResult' => 'true',
												'EchoCV2CheckResult' => 'true',
												'EchoThreeDSecureAuthenticationCheckResult' => 'true',
												'EchoCardType' => 'true',
												'OrderID' => guid(),
												'TransactionType' => ($payment->params->operation_mode) ? trim($payment->params->operation_mode) : 'SALE',
												'TransactionDateTime' => gatewaydatetime(),
												'CallbackURL' => $finishUrl,
												'OrderDescription' => $orderDesc .' '. $qBooking->ref_number,
												'CustomerName' => stripGWInvalidChars($contactName),
												'Address1' => '',
												'Address2' => '',
												'Address3' => '',
												'Address4' => '',
												'City' => '',
												'State' => '',
												'PostCode' => '',
												'CountryCode' => ($payment->params->country_code) ? trim($payment->params->country_code) : '826',
												'EmailAddress' => stripGWInvalidChars($contactEmail),
												'PhoneNumber' => stripGWInvalidChars($contactMobile),
												'EmailAddressEditable' => 'false',
												'PhoneNumberEditable' => 'false',
												'CV2Mandatory' => 'true',
												'Address1Mandatory' => 'true',
												'CityMandatory' => 'true',
												'PostCodeMandatory' => 'true',
												'StateMandatory' => 'false',
												'CountryMandatory' => 'true',
												'ResultDeliveryMethod' => 'SERVER', // POST | SERVER
												'ServerResultURL' => $notifyUrl,
												'PaymentFormDisplaysResult' => 'false',
												'ServerResultURLCookieVariables' => '',
												'ServerResultURLFormVariables' => '',
												'ServerResultURLQueryStringVariables' => ''
										);

										$tempParams = array(
												'HashDigest' => createhash($PreSharedKey, $MerchantID, $Password, $paymentParams),
												'MerchantID' => $MerchantID
										);

										$paymentParams = array_merge($tempParams, $paymentParams);
										$html = '';

										if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
												$html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
										}
										else {
												$html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
										}

										$html .= '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';
										foreach($paymentParams as $key => $value) {
												$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
										}
										$html .= '<input type="submit" name="submit" value="'.$gLanguage['API']['PAYMENT_BUTTON'].'" class="button btn btn-primary">';
										$html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
										$html .= '</form>';

								break;
                case 'cardsave':

                    // http://www.cardsave.net/Developer-Support/Integration-Methods/Redirect
                    // https://github.com/CardSave/woocommerce-gateway-cardsave/blob/master/gateway-cardsave.php

                    $paymentUrl = 'https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx';

										$totalAmount = $total * 100; // Production amount

										if ( !empty((int)$payment->params->test_mode) ) { // Test
												if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount * 100; // Test amount
												}
                    }

                    //Function to get date/time stamp as required by the gateway
                    function gatewaydatetime() {
                        return date('Y-m-d H:i:s P');
                    }

                    //Function to generate a unique OrderID for the transaction (The OrderID can be any AlphaNumeric string - e.g. your own carts order ID if applicable
                    function guid()	{
                        if (function_exists('com_create_guid')) {
                            return com_create_guid();
                        }
                        else {
                            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
                            $charid = strtoupper(md5(uniqid(rand(), true)));
                            $hyphen = chr(45);// "-"
                            $uuid = chr(123)// "{"
                                    .substr($charid, 0, 8).$hyphen
                                    .substr($charid, 8, 4).$hyphen
                                    .substr($charid,12, 4).$hyphen
                                    .substr($charid,16, 4).$hyphen
                                    .substr($charid,20,12)
                                    .chr(125);// "}"
                            return $uuid;
                        }
                    }

                    // Function to remove invalid characters / characters which will cause the gateway to error.
                    function stripGWInvalidChars($strToCheck) {
                        $toReplace = array("#","\\",">","<", "\"", "[", "]");
                        $cleanString = str_replace($toReplace, "", $strToCheck);
                        return $cleanString;
                    }

                    function createhash( $PreSharedKey, $MerchantID, $Password, $paymentParams ) {
                        $tempParams = array(
                            'PreSharedKey' => $PreSharedKey,
                            'MerchantID' => $MerchantID,
                            'Password' => $Password
                        );

                        $paymentParams = array_merge($tempParams, $paymentParams);
                        $paymentParamsString = '';
                        foreach($paymentParams as $key => $value) {
                            $prefix =  '&';
                            if ( empty($paymentParamsString) ) {
                                $prefix =  '';
                            }
                            $paymentParamsString .= $prefix . $key .'='. $value;
                        }
                        return sha1($paymentParamsString);
                    }

                    $PreSharedKey = trim($payment->params->pre_shared_key);
                    $MerchantID = trim($payment->params->merchant_id); // live or test
                    $Password = trim($payment->params->password);

                    $langCode = '';

                    if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                         $langCode = trim($payment->params->language_code);
                    }

                    $paymentParams = array(
                        'Amount' => $totalAmount,
                        'CurrencyCode' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : '826',
                        'EchoAVSCheckResult' => 'true',
                        'EchoCV2CheckResult' => 'true',
                        'EchoThreeDSecureAuthenticationCheckResult' => 'true',
                        'EchoCardType' => 'true',
                        'OrderID' => guid(),
                        'TransactionType' => ($payment->params->operation_mode) ? trim($payment->params->operation_mode) : 'SALE',
                        'TransactionDateTime' => gatewaydatetime(),
                        'CallbackURL' => $finishUrl,
                        'OrderDescription' => $orderDesc .' '. $qBooking->ref_number,
                        'CustomerName' => stripGWInvalidChars($contactName),
                        'Address1' => '',
                        'Address2' => '',
                        'Address3' => '',
                        'Address4' => '',
                        'City' => '',
                        'State' => '',
                        'PostCode' => '',
                        'CountryCode' => ($payment->params->country_code) ? trim($payment->params->country_code) : '826',
                        'EmailAddress' => stripGWInvalidChars($contactEmail),
                        'PhoneNumber' => stripGWInvalidChars($contactMobile),
                        'EmailAddressEditable' => 'false',
                        'PhoneNumberEditable' => 'false',
                        'CV2Mandatory' => 'true',
                        'Address1Mandatory' => 'true',
                        'CityMandatory' => 'true',
                        'PostCodeMandatory' => 'true',
                        'StateMandatory' => 'false',
                        'CountryMandatory' => 'true',
                        'ResultDeliveryMethod' => 'SERVER', // POST | SERVER
                        'ServerResultURL' => $notifyUrl,
                        'PaymentFormDisplaysResult' => 'false',
                        'ServerResultURLCookieVariables' => '',
                        'ServerResultURLFormVariables' => '',
                        'ServerResultURLQueryStringVariables' => ''
                    );

                    $tempParams = array(
                        'HashDigest' => createhash($PreSharedKey, $MerchantID, $Password, $paymentParams),
                        'MerchantID' => $MerchantID
                    );

                    $paymentParams = array_merge($tempParams, $paymentParams);

                    $html = '';

                    if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
                        $html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
                    }
                    else {
                        $html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
                    }

                    $html .= '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';

                    foreach($paymentParams as $key => $value) {
                        $html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
                    }

                    $html .= '<input type="submit" name="submit" value="'.$gLanguage['API']['PAYMENT_BUTTON'].'" class="button btn btn-primary">';
										$html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
                    $html .= '</form>';

                break;
                case 'worldpay':

                    // http://support.worldpay.com/support/kb/bg/pdf/rhtml.pdf
                    // http://support.worldpay.com/support/kb/bg/testandgolive/tgl5103.html

                    // Test details:
                    // Visa Card Number: 4917610000000000 or MasterCard Number: 5454545454545454
                    // Security Code: 123

										$totalAmount = $total; // Production amount

                    if ( !empty((int)$payment->params->test_mode) ) { // Test
                        $paymentUrl = 'https://secure-test.worldpay.com/wcc/purchase';
                        $testMode = 100;

												if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount; // Test amount
												}
                    }
										else { // Production
                        $paymentUrl = 'https://secure.worldpay.com/wcc/purchase';
                        $testMode = 0;
                    }

                    $langCode = $gConfig['language'];

                    if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                         $langCode = trim($payment->params->language_code);
                    }

										// Callback protocol.
										if (!empty($payment->params->callback_protocol)) {
												$callback_protocol = (int)$payment->params->callback_protocol;
												if ($callback_protocol == 1) { // http
														$notifyUrl = str_replace('https://', 'http://', $notifyUrl);
														$notifyUrl .= (strpos($notifyUrl, '?') === false ? '?' : '&') .'no_https_redirect=1';
												}
												elseif ($callback_protocol == 2) { // https
														$notifyUrl = str_replace('http://', 'https://', $notifyUrl);
												}
										}

                    $paymentParams = array(
                        'testMode' => $testMode,
                        'instId' => trim($payment->params->inst_id),
                        'cartId' => $qBooking->ref_number,
                        'amount' => $totalAmount,
                        'currency' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP',
                        'desc' => $orderDesc .' #'. $qBooking->ref_number,
                        'name' => $contactName,
                        'email' => $contactEmail,
                        // 'tel' => $contactMobile,
                        'lang' => $langCode,
                        'compName' => str_replace(' ',' ',$gConfig['company_name']),
                        'MC_finish_url' => $finishUrl,
                        'MC_notify_url' => $notifyUrl,
                        'MC_cancel_url' => $bookingUrl,
                    );

                    if ( !empty($payment->params->md5_secret) && !empty($payment->params->signature_fields) ) {
                        // instId:amount:currency:cartId
                        $signature = $payment->params->md5_secret;
                        $temp = explode(':', $payment->params->signature_fields);
                        foreach($temp as $k => $v) {
                            if ( !empty($signature) ) {
                                $signature .= ':';
                            }
                            $signature .= $paymentParams[$v];
                        }
                        $paymentParams['signature'] = md5($signature);
                    }

                    ksort($paymentParams);

                    $html = '';

                    if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
                        $html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
                    }
                    else {
                        $html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
                    }

                    $html .= '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';
                    foreach($paymentParams as $key => $value) {
                        if ( !empty($value) ) {
                            $html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
                        }
                    }
                    $html .= '<input type="submit" name="submit" value="'.$gLanguage['API']['PAYMENT_BUTTON'].'" class="button btn btn-primary">';
										$html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
                    $html .= '</form>';

                break;
                case 'redsys':
                    // http://www.redsys.es/en/index.html#descargas

                    switch ($gConfig['language']) {
                        case 'en-GB':
                            $langCode = 'en';
                        break;
                        case 'es-ES':
                            $langCode = 'es';
                        break;
                        case 'pt-PT':
                            $langCode = 'pt';
                        break;
                        case 'pt-BR':
                            $langCode = 'pt';
                        break;
                        default:
                            $langCode = 'en';
												break;
                    }

                    if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                         $langCode = trim($payment->params->language_code);
                    }

                    switch ($langCode) {
                        case 'es':
                            $langCode = '001';
                        break;
                        case 'en':
                            $langCode = '002';
                        break;
                        case 'ca':
                            $langCode = '003';
                        break;
                        case 'fr':
                            $langCode = '004';
                        break;
                        case 'de':
                            $langCode = '005';
                        break;
                        case 'nl':
                            $langCode = '006';
                        break;
                        case 'it':
                            $langCode = '007';
                        break;
                        case 'sv':
                            $langCode = '008';
                        break;
                        case 'pt':
                            $langCode = '009';
                        break;
                        case 'pl':
                            $langCode = '011';
                        break;
                        case 'gl':
                            $langCode = '012';
                        break;
                        case 'eu':
                            $langCode = '013';
                        break;
                        default:
                            $langCode = '002';
                        break;
                    }

										$totalAmount = $total * 100; // Production amount

                    if ( !empty((int)$payment->params->test_mode) ) { // Test
                        $paymentUrl = 'https://sis-t.redsys.es:25443/sis/realizarPago';

												if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount * 100; // Test amount
												}
                    }
										else { // Production
                        $paymentUrl = 'https://sis.redsys.es/sis/realizarPago';
                    }

                    $merchantID = trim($payment->params->merchant_id);
                    $terminalID = trim($payment->params->terminal_id);
                    $encryptionKey = trim($payment->params->encryption_key);
                    $signatureVersion = ($payment->params->signature_version) ? trim($payment->params->signature_version) : 'HMAC_SHA256_V1';
                    $currency = ($payment->params->currency_code) ? trim($payment->params->currency_code) : '826';
                    $transactionType = ($payment->params->operation_mode) ? trim($payment->params->operation_mode) : '0';
                    $id = time();
                    $merchantName = str_replace(' ',' ',$gConfig['company_name']);
                    $description = $orderDesc .' #'. $qBooking->ref_number;

                    include(base_path('vendor/easytaxioffice/apiRedsys.php'));

                    $miObj = new \RedsysAPI;
                    $miObj->setParameter("DS_MERCHANT_AMOUNT", $totalAmount);
                    $miObj->setParameter("DS_MERCHANT_ORDER", strval($id));
                    $miObj->setParameter("DS_MERCHANT_MERCHANTCODE", $merchantID);
                    $miObj->setParameter("DS_MERCHANT_CURRENCY", $currency);
                    $miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE", $transactionType);
                    $miObj->setParameter("DS_MERCHANT_TERMINAL", $terminalID);
                    $miObj->setParameter("DS_MERCHANT_MERCHANTURL", $notifyUrl);
                    $miObj->setParameter("DS_MERCHANT_URLOK", $finishUrl);
                    $miObj->setParameter("DS_MERCHANT_URLKO", $bookingUrl);
                    $miObj->setParameter("DS_MERCHANT_CONSUMERLANGUAGE", $langCode);
                    $miObj->setParameter("DS_MERCHANT_PRODUCTDESCRIPTION", $description);
                    $miObj->setParameter("DS_MERCHANT_CARDHOLDER", $contactName);
                    $miObj->setParameter("DS_MERCHANT_MERCHANTNAME", $merchantName);

                    $paymentParams = array(
                        'Ds_SignatureVersion' => $signatureVersion,
                        'Ds_MerchantParameters' => $miObj->createMerchantParameters(),
                        'Ds_Signature' => $miObj->createMerchantSignature($encryptionKey)
                    );

                    $html = '';
                    if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
                        $html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
                    }
                    else {
                        $html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
                    }
                    $html .= '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';
                    foreach($paymentParams as $key => $value) {
                        if ( !empty($value) ) {
                            $html .= '<input type="hidden" name="'. $key .'" value="'. $value .'">';
                        }
                    }
                    $html .= '<input type="submit" name="submit" value="'.$gLanguage['API']['PAYMENT_BUTTON'].'" class="button btn btn-primary">';
										$html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
                    $html .= '</form>';

                break;
								case 'stripe_ideal':

  									// https://stripe.com/docs/sources/ideal

										$gConfig['auto_payment_redirection'] = 0;
										$totalAmount = $total * 100; // Production amount

										if ( !empty((int)$payment->params->test_mode) ) { // Test
											$publishableKey = $payment->params->pk_test;

											if ( !empty((float)$payment->params->test_amount) ) {
												$totalAmount = (float)$payment->params->test_amount * 100; // Test amount
											}
										}
										else { // Production
											$publishableKey = $payment->params->pk_live;
										}

										$langCode = $gConfig['language'];

										if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
											$langCode = trim($payment->params->language_code);
										}

										$paymentParams = array(
											'key' => $publishableKey,
											'amount' => $totalAmount,
											// 'name' => str_replace(' ',' ',$gConfig['company_name']),
											'description' => $orderDesc .' #'. $qBooking->ref_number,
											'email' => $contactEmail,
										);

										ksort($paymentParams);

										$stripeParams = (object)$paymentParams;

										$html = '';
										if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
											$html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
										}
										else {
											$html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
										}

										$html .= '<div style="margin-top:20px; height:600px;">';
											$html .= '<form action="#" id="paymentForm" name="paymentForm">
												<div class="form-master">
													<div class="form-row clearfix">
														<label for="name">Name:</label>
														<input id="name" name="name"" class="form-control" placeholder="Enter your name" value="'. $contactName .'" required>
													</div>
													<div class="form-row clearfix">
														<label for="ideal-bank-element">iDEAL Bank:</label>
														<div id="ideal-bank-element" class="form-control" style="padding:0;">
															<div style="padding:12px 12px;">Loading...</div>
														</div>
													</div>
													<div id="error-message" role="alert"></div>
												</div>
												<div class="form-footer">
													<button type="submit" id="payNowButton" class="button btn btn-primary">'. $gLanguage['API']['PAYMENT_BUTTON'] .'</button>
													<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>
												</div>
											</form>';

											$html .= '<div id="result" style="margin-top:20px;"></div>
													<div id="modal" class="modal fade" role="dialog">
														<div class="modal-dialog">
															<div class="modal-content">
																<div class="modal-body" id="iframe-container"></div>
															</div>
														</div>
													</div>';
										$html .= '</div>';

										$html .= '<style>
										#paymentForm {
									    text-align: left
										}
										#paymentForm .form-master {
											display: inline-block;
										}
										#paymentForm .form-footer {
											display: block;
										}
										#paymentForm .form-row {
											text-align: left;
										}
										#paymentForm .form-row label {
											display: block;
			    						margin: 10px 10px 5px 0;
                      font-weight: bold !important;
                      color: #888;
										}
										#paymentForm .form-row .form-control {
											display: block;
										}
										#paymentForm #error-message {
											color: red;
			    						margin-top: 10px;
										}
										</style>';

										$html .= '<script src="https://js.stripe.com/v3/"></script>';

										$html .= "<script>
										$('#paymentForm').show();
										$('#result').hide();

										var poll;
										var timeout = 100; // 10 seconds timeout

										poll = function() {
											setTimeout(function() {
												timeout--;
												if ( typeof Stripe !== 'undefined') {
													initStripe();
												}
												else if ( timeout > 0 ) {
													poll();
												}
												else {
													displayResult('External library failed to load.');
												}
											}, 100);
										};

										poll();

										function initStripe() {
											var stripe = Stripe('". $stripeParams->key ."');
											var elements = stripe.elements();

											var style = {
											  base: {
											    padding: '10px 12px',
											    color: '#32325d',
											    fontFamily: '-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif',
											    fontSmoothing: 'antialiased',
											    fontSize: '16px',
											    '::placeholder': {
											      color: '#aab7c4'
											    },
											  },
											  invalid: {
											    color: '#fa755a',
											  }
											};

											var idealBank = elements.create('idealBank', {style: style});
											idealBank.mount('#ideal-bank-element');
											var errorMessage = document.getElementById('error-message');

											var form = document.getElementById('paymentForm');
											form.addEventListener('submit', function(event) {
											  event.preventDefault();

											  var sourceData = {
											    type: 'ideal',
											    amount: ". $stripeParams->amount .",
                          currency: 'eur',  // Only euro is supported
													statement_descriptor: '". $stripeParams->description ."',
											    owner: {
											      name: document.querySelector('input[name=\"name\"]').value,
											      email: '". $stripeParams->email ."' ? '". $stripeParams->email ."' : null,
											    },
											    redirect: {
											      return_url: '". $notifyUrl ."',
											    },
											  };

											  stripe.createSource(idealBank, sourceData).then(function(result) {
											    if (result.error) {
											      // Inform the customer that there was an error.
											      errorMessage.textContent = result.error.message;
											      errorMessage.classList.add('visible');
											    } else {
											      // Redirect the customer to the authorization URL.
											      errorMessage.classList.remove('visible');
											      stripeSourceHandler(result.source);
											    }
											  });

                        return false;
											});

											function stripeSourceHandler(source) {
											  document.location.href = source.redirect.url;
											}
										}

										function displayResult(resultText) {
											resultText = '<p class=\"alert alert-info\">'+ resultText +'</p>';
											$('#paymentForm').show();
											$('#result').html(resultText).show();
										}
										</script>";

								break;
                case 'stripe':

                    if (!empty((int)$payment->params->sca_mode)) {
                        // https://stripe.com/docs/api/checkout/sessions/object
                        // https://stripe.com/docs/testing#cards
                        // https://stripe.com/docs/payments/checkout

                        // Test details:
                        // Visa Card Number: 4242 4242 4242 4242
                        // 3D Secure authentication: 4000 0000 0000 3220
                        // Expiry date: any date in future
                        // Security Code: 123

                        $paymentUrl = $notifyUrl;
                        $totalAmount = $total * 100; // Production amount

                        if ( !empty((int)$payment->params->test_mode) ) { // Test
                            $publishableKey = $payment->params->pk_test;
    												$secretKey = $payment->params->sk_test;
                            if ( !empty((float)$payment->params->test_amount) ) {
                                $totalAmount = (float)$payment->params->test_amount * 100; // Test amount
                            }
                        }
                        else { // Production
                            $publishableKey = $payment->params->pk_live;
    												$secretKey = $payment->params->sk_live;
                        }

                        $langCode = $gConfig['language'];

                        if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                            $langCode = trim($payment->params->language_code);
                        }

                        $paymentParams = array(
                            'key' => $publishableKey,
                            'amount' => $totalAmount,
                            'name' => str_replace(' ',' ',$gConfig['company_name']),
                            'description' => $orderDesc .' #'. $qBooking->ref_number,
                            'email' => $contactEmail ?: null,
                            'currency' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP',
                            'locale' => 'auto', // $langCode
                        );

                        ksort($paymentParams);
                        $stripeParams = (object)$paymentParams;

                        $gConfig['auto_payment_redirection'] = 0;

                        $html = '';
                        if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
                            $html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
                        }
                        else {
                            $html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
                        }

                        $html .= '<div style="margin-top:20px; height:600px;">';
                            // $html .= '<form method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';
                            $html .= '<button type="submit" id="payNowButton" class="button btn btn-primary">'. $gLanguage['API']['PAYMENT_BUTTON'] .'</button>';
                            $html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
                            // $html .= '</form>';
                            $html .= '<div id="result" style="margin-top:20px;"></div>';
                        $html .= '</div>';

                        $checkoutSessionID = '';

                        try {
                            if (\Request::isSecure()) {
                                $curl = new \Stripe\HttpClient\CurlClient(array(CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1));
                                \Stripe\ApiRequestor::setHttpClient($curl);
                            }

                            \Stripe\Stripe::setApiKey($secretKey);

                            // https://stripe.com/docs/api/checkout/sessions/create
                            $session = \Stripe\Checkout\Session::create([
                                'payment_method_types' => ['card'],
                                'line_items' => [[
                                    'name' => $stripeParams->name,
                                    'description' => $stripeParams->description,
                                    'amount' => $stripeParams->amount,
                                    'currency' => $stripeParams->currency,
                                    'quantity' => 1,
                                ]],
                                'payment_intent_data' => [
                                    'description' => $stripeParams->description,
                                ],
                                // 'customer_email' => $stripeParams->email,
                                // 'client_reference_id' => $qBooking->ref_number,
                                'client_reference_id' => $transaction->unique_key,
                                'locale' => $stripeParams->locale,
                                'success_url' => $finishUrl,
                                'cancel_url' => $bookingUrl,
                            ]);

                            $checkoutSessionID = $session->id;
                            // \Log::debug($session);

                            // $updateTransaction = \App\Models\Transaction::find($transaction->id);
                            // $updateTransaction->params = json_encode([
                            //     'checkout_session_id' => $session->id,
                            //     'payment_intent_id' => $session->payment_intent,
                            // ]);
                            // $updateTransaction->save();
                        }
                        catch (\Exception $e) {
                            \Log::error('Stripe payment method session could not be started: '. $e->getMessage());
                        }

                        $html .= '<script src="https://js.stripe.com/v3/"></script>';

                        $html .= "<script>
                        $('#paymentForm').show();
                        $('#result').hide();

                        var poll;
                        var timeout = 100; // 10 seconds timeout
                        poll = function() {
                            setTimeout(function() {
                                timeout--;
                                if ( typeof Stripe !== 'undefined') {
                                    initStripe();
                                } else if ( timeout > 0 ) {
                                    poll();
                                } else {
                                    displayResult('External library failed to load.');
                                }
                            }, 100);
                        };
                        poll();

                        function initStripe() {
                            setTimeout(function(){
                                $('#payNowButton').click();
                            }, 3000);

                            if (". ($checkoutSessionID ? 'true' : 'false') .") {
                                var stripe = Stripe('". $stripeParams->key ."');

                                $('#payNowButton').on('click', function(e) {
                                    stripe.redirectToCheckout({
                                        sessionId: '". $checkoutSessionID ."'
                                    })
                                    .then(function(result) {
                                        displayResult(result.error.message);
                                    });
                                    e.preventDefault();
                                });
                            }
                            else {
                                displayResult('You must provide one of items or sessionId.');
                            }
                        }

                        function displayResult(resultText) {
                            resultText = '<p class=\"alert alert-info\">'+ resultText +'</p>';
                            $('#paymentForm').show();
                            $('#result').html(resultText).show();
                        }
                        </script>";
                    }
                    else {
          							// https://stripe.com/docs/checkout/tutorial
          							// https://stripe.com/docs/testing#cards

          							// Test details:
          							// Visa Card Number: 4242424242424242
          							// Expiry date: any date in future
          							// Security Code: 123

          							$paymentUrl = $notifyUrl;
          							$totalAmount = $total * 100; // Production amount

          							if ( !empty((int)$payment->params->test_mode) ) { // Test
            								$publishableKey = $payment->params->pk_test;
            								if ( !empty((float)$payment->params->test_amount) ) {
    									         $totalAmount = (float)$payment->params->test_amount * 100; // Test amount
            								}
          							}
          							else { // Production
      							         $publishableKey = $payment->params->pk_live;
          							}

          							$langCode = $gConfig['language'];

          							if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
  								          $langCode = trim($payment->params->language_code);
          							}

          							$paymentParams = array(
          								'key' => $publishableKey,
          								'amount' => $totalAmount,
          								'name' => str_replace(' ',' ',$gConfig['company_name']),
          								'description' => $orderDesc .' #'. $qBooking->ref_number,
          								'email' => $contactEmail,
          								'image' => '',
          								'locale' => 'auto',
          								'label' => $gLanguage['API']['PAYMENT_BUTTON'],
          								'allowRememberMe' => 'false',
          								'zipCode' => isset($payment->params->zip_code) ? $payment->params->zip_code : 'false',
          								'billingAddress' => isset($payment->params->billing_address) ? $payment->params->billing_address : 'false',
          								// 'image' => 'https://stripe.com/img/documentation/checkout/marketplace.png',
          								// 'lang' => $langCode,
          								// 'zip-code' => isset($payment->params->zip_code) ? $payment->params->zip_code : 'false',
          								'currency' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP',
          							);

          							ksort($paymentParams);
          							$stripeParams = (object)$paymentParams;

          							$html = '';
          							if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
          								$html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
          							}
                        else {
          								$html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
          							}

          							$html .= '<div style="margin-top:20px; height:600px;">';
          								$html .= '<form method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';
          									$html .= '<button type="submit" id="payNowButton" class="button btn btn-primary">'. $gLanguage['API']['PAYMENT_BUTTON'] .'</button>';
          									$html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
          								$html .= '</form>';
          								$html .= '<div id="result" style="margin-top:20px;"></div>
          										<div id="modal" class="modal fade" role="dialog">
          											<div class="modal-dialog">
          												<div class="modal-content">
          													<div class="modal-body" id="iframe-container"></div>
          												</div>
          											</div>
          										</div>';
          							$html .= '</div>';

          							// https://jsfiddle.net/ywain/5j580jxa/
          							$html .= '<script src="https://js.stripe.com/v3/"></script>';
          							$html .= '<script src="https://checkout.stripe.com/checkout.js"></script>';

          							$html .= "<script>
          							$('#paymentForm').show();
          							$('#result').hide();

          							var poll;
          							var timeout = 100; // 10 seconds timeout
          							poll = function() {
          								setTimeout(function() {
          									timeout--;
          									if ( typeof Stripe !== 'undefined' && typeof StripeCheckout !== 'undefined' ) { initStripe(); }
          									else if ( timeout > 0 ) { poll(); }
          									else { displayResult('External library failed to load.'); }
          								}, 100);
          							};
          							poll();

          							function initStripe() {
          								var stripe = Stripe('". $stripeParams->key ."');

          								var handler = StripeCheckout.configure({
          									key: '". $stripeParams->key ."',
          									image: '". $stripeParams->image ."',
          									locale: '". $stripeParams->locale ."',
          									allowRememberMe: ". $stripeParams->allowRememberMe .",
          									token: function(token) {
          										stripe.createSource({
          											type: 'card',
          											token: token.id
          										})
          										.then(function(result) {
          											stripeCardResponseHandler(result);
          										});
          										displayResult('<i class=\"ion-ios-loop fa fa-spin\" style=\"color:#000; font-size:21px; float:left; margin-right:5px;\"></i> Processing... Please wait and do not not close this window.');
          									}
          								});

          								$('#payNowButton').on('click', function(e) {
          									handler.open({
          										name: '". $stripeParams->name ."',
          										description: '". $stripeParams->description ."',
          										zipCode: ". $stripeParams->zipCode .",
          										billingAddress: ". $stripeParams->billingAddress .",
          										amount: ". $stripeParams->amount .",
          										currency: '". $stripeParams->currency ."'
          									});
          									e.preventDefault();
          								});

          								$(window).on('popstate', function() {
          									handler.close();
          								});

          								function stripeCardResponseHandler(result) {
          									// console.log('stripeCardResponseHandler', result);

          									if ( result.error ) {
          										displayResult('Unexpected card source creation error: '+ result.error.message);
          										return;
          									}

          									// 3D Secure
          									if ( ". (isset($payment->params->three_d_secure) ? $payment->params->three_d_secure : 'false') ." ) {
          										if ( result.source.card.three_d_secure == 'not_supported' ) {
          											displayResult('This card does not support 3D Secure.');
          											return;
          										}

          										stripe.createSource({
          											type: 'three_d_secure',
          											amount: ". $stripeParams->amount .",
          											currency: '". $stripeParams->currency ."',
          											three_d_secure: {
          												card: result.source.id
          											},
          											redirect: {
          												return_url: '". url('/payment-waiting') ."'
          											}
          										})
          										.then(function(result) {
          											stripe3DSecureResponseHandler(result);
          										});
          									}
          									else {
          										stripeSourceHandler(result.source);
          									}
          								}

          								function stripe3DSecureResponseHandler(result) {
          									// console.log('stripe3DSecureResponseHandler', result);

          									if ( result.error ) {
          										displayResult('Unexpected 3DS source creation error: '+ result.error.message);
          										return;
          									}

          									if ( result.source.status == 'chargeable' ) {
          										stripeSourceHandler(result.source);
          										displayResult('<i class=\"ion-ios-loop fa fa-spin\" style=\"color:#000; font-size:21px; float:left; margin-right:5px;\"></i> Processing... Please wait and do not not close this window.');
          										// displayResult('This card does not support 3D Secure authentication, but liability will be shifted to the card issuer.');
          										return;
          									}
          									else if ( result.source.status != 'pending' ) {
          										displayResult('Unexpected 3D Secure status: '+ result.source.status);
          										return;
          									}

          									$('#iframe-container').html('<iframe src=\"'+ result.source.redirect.url +'\" frameborder=\"0\" style=\"width:100%; height: 600px;\"></iframe>');

          									$('#modal').modal({
          										show: true
          									});

          									var poll2;
          									var timeout2 = 120;
          									var status = 'pending';

          									poll2 = function () {
          										setTimeout(function() {
          											stripe.retrieveSource({
          												id: result.source.id,
          												client_secret: result.source.client_secret,
          											})
          											.then(function(result) {
          												timeout2--;
          												status = result.source.status;
          												if ( status != 'pending' ) {
          													stripe3DSStatusChangedHandler(result);
          												}
          												else if ( timeout2 > 0 ) {
          													poll2();
          												}
          												else {
          													displayResult('Retrieve source connection timed out.');
          													$('#modal').modal('hide');
          												}
          												// console.log(status, timeout2, result.source);
          											});
          										}, 1000);
          									};

          									poll2();
          								}

          								function stripe3DSStatusChangedHandler(result) {
          									// console.log('stripe3DSStatusChangedHandler', result);

          									if ( result.error ) {
          										displayResult('Unexpected 3DS source status error: '+ result.error.message);
          										return;
          									}

          									if ( result.source.status == 'chargeable' ) {
          										stripeSourceHandler(result.source);
          										displayResult('<i class=\"ion-ios-loop fa fa-spin\" style=\"color:#000; font-size:21px; float:left; margin-right:5px;\"></i> Processing... Please wait and do not not close this window.');
          										// displayResult('3D Secure authentication succeeded: '+ result.source.id +'. In a real app you would send this source ID to your backend to create the charge.');
          										$('#modal').modal('hide');
          									}
          									else if ( result.source.status == 'failed' ) {
          										displayResult('3D Secure authentication failed. Please try again.');
          										$('#modal').modal('hide');
          									}
          									else if ( result.source.status != 'pending' ) {
          										displayResult('Unexpected 3D Secure status: ' + result.source.status);
          										$('#modal').modal('hide');
          									}
          								}

          								function stripeSourceHandler(source) {
          									var form = document.getElementById('paymentForm');
          									var hiddenInput = document.createElement('input');
          									hiddenInput.setAttribute('type', 'hidden');
          									hiddenInput.setAttribute('name', 'stripeSource');
          									hiddenInput.setAttribute('value', source.id);
          									form.appendChild(hiddenInput);
          									form.submit();
          								}

          								function displayResult(resultText) {
          									resultText = '<p class=\"alert alert-info\">'+ resultText +'</p>';
          									$('#paymentForm').show();
          									$('#result').html(resultText).show();
          								}
          							}
          							</script>";
                    }

								break;
                case 'wpop':

										// https://beta.developer.worldpay.com/docs/wpop
										// https://beta.developer.worldpay.com/docs/wpop/testing
                    // https://beta.developer.worldpay.com/docs/wpop/templateform-advanced-usage
                    // https://github.com/Worldpay/worldpay-lib-php

										// Test details:
										// Visa Card Number: 4444333322221111
										// Expiry date: any date in future 2025
										// Security Code: 123

										$paymentUrl = $notifyUrl;

										if ( !empty((int)$payment->params->test_mode) ) { // Test
  											$publishableKey = $payment->params->pk_test;
                        $templateCode = !empty($payment->params->template_code_test) ? $payment->params->template_code_test : "";
										}
										else { // Production
						            $publishableKey = $payment->params->pk_live;
                        $templateCode = !empty($payment->params->template_code_live) ? $payment->params->template_code_live : "";
										}

                    if (empty($publishableKey)) {
                        \Log::warning('Missing Worldpay Online Payments client key is payment config tab.');
                    }

                    // $langCode = $gConfig['language'];
                    // if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                    //     $langCode = trim($payment->params->language_code);
                    // }

										$html = '';
										if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
                        $html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
										}
										else {
                        $html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
										}

                    $html .= '<div style="margin-top:20px; height:600px;">';
                    	$html .= '<form method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';
                          $html .= '<div id="paymentSection"></div>';
                    			$html .= '<button type="submit" id="payNowButton" class="button btn btn-primary">'. $gLanguage['API']['PAYMENT_BUTTON'] .'</button>';
                    			$html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
                    	$html .= '</form>';
                    	$html .= '<div id="result" style="margin-top:20px;"></div>';
                    $html .= '</div>';

                    $html .= '<script src="https://cdn.worldpay.com/v1/worldpay.js"></script>';
                    $html .= "<script>
                    $('#paymentForm').show();
                    $('#result').hide();
                    var poll, timeout = 100; // 10 seconds timeout
                    poll = function() {
                    	setTimeout(function() {
                    		timeout--;
                    		if ( typeof Worldpay !== 'undefined' ) { initWorldpay(); }
                    		else if ( timeout > 0 ) { poll(); }
                    		else { displayResult('External library failed to load.'); }
                    	}, 100);
                    };
                    poll();

                    function initWorldpay() {
                      Worldpay.useTemplateForm({
                        'clientKey':'". $publishableKey ."',
                        'form':'paymentForm',
                        'paymentSection':'paymentSection',
                        'display':'inline', // inline | modal
                        'saveButton':false,
                        'reusable':false,
                        ". (!empty($templateCode) ? "'code':'{$templateCode}'," : "") ."
                        'templateOptions': {
                          images:{enabled:false},
                          dimensions: {width:false, height:274}
                        },
                        'callback': function(obj) {
                          if (obj && obj.token) {
                            var _el = document.createElement('input');
                            _el.value = obj.token;
                            _el.type = 'hidden';
                            _el.name = 'token';
                            document.getElementById('paymentForm').appendChild(_el);
                            document.getElementById('paymentForm').submit();
                          }
                        }
                      });

                      Worldpay.getTemplateToken();

                    	$('#payNowButton').on('click', function(e) {
                    		Worldpay.submitTemplateForm();
                    		e.preventDefault();
                    	});

                    	function displayResult(resultText) {
                    		resultText = '<p class=\"alert alert-info\">'+ resultText +'</p>';
                    		$('#paymentForm').show();
                    		$('#result').html(resultText).show();
                    	}
                    }
                    </script>";

                break;
								case 'square':

										// https://connect.squareup.com/apps
                    // https://developer.squareup.com/docs/checkout-api/what-it-does
                    // https://developer.squareup.com/docs/testing/test-values
                    // https://squareupsandbox.com/dashboard

                    // Card number: 4111 1111 1111 1111
                    // Expiration date: 12/21
                    // CVV: 111

										$gConfig['auto_payment_redirection'] = 0;

										$totalAmount = $total * 100;
                    $legacyMode = (!isset($payment->params->legacy_mode) || (int)$payment->params->legacy_mode == 1) ? true : false;

										if (!empty((int)$payment->params->test_mode)) {
                        $apiUrl = 'https://connect.squareupsandbox.com';
												$accessToken = $payment->params->test_access_token;
                        $locationId = $payment->params->test_location_id;

												if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount * 100;
												}
										}
										else {
                        $apiUrl = 'https://connect.squareup.com';
												$accessToken = $payment->params->live_access_token;
                        $locationId = $payment->params->live_location_id;
										}

                    $language = explode('-', $gConfig['language']);
                    $langCode = ($language[1]) ? $language[1] : 'GB';

                    if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
												$langCode = trim($payment->params->language_code);
                    }

                    $paymentParams = [
												"redirect_url" => $notifyUrl,
											  "idempotency_key" => uniqid(),
											  "ask_for_shipping_address" => false,
											  "merchant_support_email" => config('site.company_email'),
											  "pre_populate_buyer_email" => $contactEmail,
											  "order" => [
											    "reference_id" => $qBooking->ref_number,
											    "line_items" => [
											      [
											        "name" => $orderDesc .' #'. $qBooking->ref_number,
											        "quantity" => "1",
											        "base_price_money" => [
											          "amount" => $totalAmount,
											          "currency" => ($payment->params->currency_code) ? trim($payment->params->currency_code) : "GBP"
											        ],
											      ],
											    ]
											  ],
                    ];

                    $apiConfig = new \SquareConnect\Configuration();
                    if ($legacyMode == false) {
                        $apiConfig->setHost($apiUrl);
                    }
                    $apiConfig->setAccessToken($accessToken);
                    $apiConfig->setSSLVerification(request()->isSecure());
                    $defaultApiClient = new \SquareConnect\ApiClient($apiConfig);
                    $checkoutClient = new SquareConnect\Api\CheckoutApi($defaultApiClient);

										try {
											  $apiResponse = $checkoutClient->createCheckout($locationId, $paymentParams);
											  $checkoutUrl = $apiResponse->getCheckout()->getCheckoutPageUrl();
											  $checkoutId =  $apiResponse->getCheckout()->getId();

												$updateTransaction = \App\Models\Transaction::find($transaction->id);
												$updateTransaction->response = json_encode([
														'checkout_id' => $checkoutId,
												]);
												$updateTransaction->save();

												$html = '<div style="margin-top:20px; height:300px;">';

												if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
														$html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
												}
												else {
														$html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
												}

												$html .= '<div style="margin-top: 20px;">
																		<a href="'. $checkoutUrl .'" class="button btn btn-primary" id="square-button" target="_top">'.$gLanguage['API']['PAYMENT_BUTTON'].'</a>
																		<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>
																	</div>';

												$html .= '<script>
          												setTimeout(function() {
          													// window.location.href = "'. $checkoutUrl .'";
          													$(\'#square-button\')[0].click();
          												}, 1000 * 3);
          												</script>';

												$html .= '</div>';
										}
										catch (Exception $e) {
                        $errMsg = 'The SquareConnect exception when calling CheckoutApi->createCheckout: '. $e->getMessage();
                        $html .= '<div class="alert alert-danger" style="text-align:center;">'. $errMsg .'</div>';
                        \Log::error($errMsg);
										}

                break;
								case 'gpwebpay':

										// https://www.gpwebpay.cz
										// https://test.portal.gpwebpay.com/portal/tools/GP_webpay_HTTP_API.pdf?locale=en_GB
                    // Card number: 5453010000071241
                    // Expiry date: 01/2024
                    // CVC2: 000

                    $totalAmount = $total; // Production amount

                    if ( !empty((int)$payment->params->test_mode) ) { // Test
                        $paymentUrl = 'https://test.3dsecure.gpwebpay.com/pgw/order.do';

												if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount;
												}
                    }
										else { // Production
                        $paymentUrl = 'https://3dsecure.gpwebpay.com/pgw/order.do';
                    }

                    $totalAmount = $totalAmount * 100;

                    $language = explode('-', $gConfig['language']);
                    $langCode = $language[0] ? $language[0] : 'en';

                    if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
												$langCode = trim($payment->params->language_code);
                    }

                    $privateKey = ($payment->params->private_key) ? trim($payment->params->private_key) : asset_path('uploads','safe/gpwebpay/gpwebpay-pvk.key');
                    $privateKeyPassword = ($payment->params->private_key_password) ? trim($payment->params->private_key_password) : '';
                    $publicKey = ($payment->params->public_key) ? trim($payment->params->public_key) : asset_path('uploads','safe/gpwebpay/gpe.signing_test.pem');

                    $digestHash = md5($transaction->id . time() . rand(100000, 1000000000));

										$paymentParams = array(
                        'MERCHANTNUMBER' => trim($payment->params->merchant_number ? $payment->params->merchant_number : ''),
												'OPERATION' => 'CREATE_ORDER',
												'ORDERNUMBER' => $transaction->id . time(),
                        'AMOUNT' => trim($totalAmount),
                        'CURRENCY' => trim($payment->params->currency_code ? $payment->params->currency_code : '203'),
                        'DEPOSITFLAG' => trim($payment->params->operation_mode ? $payment->params->operation_mode : '1'),
                        'MERORDERNUM' => $qBooking->id,
                        'URL' => trim($notifyUrl),
                        // 'DESCRIPTION' => htmlentities(trim($orderDesc .' '. $qBooking->ref_number)),
                        'MD' => $digestHash,
												'EMAIL' => trim($contactEmail),
										);

                    $paymentParamsString = '';
                    foreach($paymentParams as $key => $value) {
                        $prefix =  '|';
                        if ( empty($paymentParamsString) ) {
                            $prefix =  '';
                        }
                        $paymentParamsString .= $prefix . $value;
                    }

                    if (\File::exists($privateKey) && $privateKeyPassword && \File::exists($publicKey)) {
                        class Signer {
                            private $privateKey;
                            private $privateKeyResource;
                            private $privateKeyPassword;
                            private $publicKey;
                            private $publicKeyResource;
                            public function __construct (string $privateKey, string $privateKeyPassword, string $publicKey) {
                                if (!file_exists($privateKey) || !is_readable($privateKey)) {
                                    throw new \Exception("Private key ({$privateKey}) not exists or not readable!");
                                }
                                if (!file_exists($publicKey) || !is_readable($publicKey)) {
                                    throw new \Exception("Public key ({$publicKey}) not exists or not readable!");
                                }
                                $this->privateKey = $privateKey;
                                $this->privateKeyPassword = $privateKeyPassword;
                                $this->publicKey = $publicKey;
                            }

                            private function getPrivateKeyResource() {
                                if ($this->privateKeyResource) {
                                    return $this->privateKeyResource;
                                }
                                $key = file_get_contents($this->privateKey);
                                if (!($this->privateKeyResource = openssl_pkey_get_private($key, $this->privateKeyPassword))) {
                                    throw new \Exception("'{$this->privateKey}' is not valid PEM private key (or passphrase is incorrect).");
                                }
                                return $this->privateKeyResource;
                            }

                            public function sign(array $params) {
                                $digestText = implode('|', $params);
                                openssl_sign($digestText, $digest, $this->getPrivateKeyResource());
                                $digest = base64_encode($digest);
                                return $digest;
                            }

                            public function verify(array $params, $digest) {
                                $data = implode('|', $params);
                                $digest = base64_decode($digest);
                                $ok = openssl_verify($data, $digest, $this->getPublicKeyResource());
                                if ($ok !== 1) {
                                    throw new \Exception("Digest is not correct!");
                                }
                                return true;
                            }

                            private function getPublicKeyResource () {
                                if ($this->publicKeyResource) {
                                    return $this->publicKeyResource;
                                }
                                $fp = fopen($this->publicKey, "r");
                                $key = fread($fp, filesize($this->publicKey));
                                fclose($fp);
                                if (!($this->publicKeyResource = openssl_pkey_get_public($key))) {
                                    throw new \Exception("'{$this->publicKey}' is not valid PEM public key.");
                                }
                                return $this->publicKeyResource;
                            }
                        }

                        $signer = new \Signer($privateKey, $privateKeyPassword, $publicKey);
                        $signature = $signer->sign($paymentParams);
                    }
                    else {
                        $signature = '';
                    }

										$tempParams = array(
                        'LANG' => $langCode,
                        'DIGEST' => $signature,
										);

										$paymentParams = array_merge($paymentParams, $tempParams);
										$html = '';

										if ( !empty($payment->payment_page) && $gConfig['auto_payment_redirection'] <= 0 ) {
												$html .= '<div>'. SiteHelper::nl2br2($payment->payment_page) .'</div>';
										}
										else {
												$html .= '<div>'. $gLanguage['API']['PAYMENT_INFO'] .'</div>';
										}

										$html .= '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';
										foreach($paymentParams as $key => $value) {
												$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
										}
										$html .= '<input type="submit" name="submit" value="'.$gLanguage['API']['PAYMENT_BUTTON'].'" class="button btn btn-primary">';
										$html .= '<a href="'. route('booking.cancel') .'" class="button btn btn-primary" style="margin-left:10px;">'. trans('booking.page.cancel.btn_cancel') .'</a>';
										$html .= '</form>';

                    // Save digets
                    $updateTransaction = \App\Models\Transaction::find($transaction->id);
                    $updateTransaction->response = json_encode([
                        'digest_hash' => $digestHash,
                    ]);
                    $updateTransaction->save();

                    // Debug
                    // $gConfig['auto_payment_redirection'] = 0;
                    // \Log::debug([$paymentParams, $tempParams, $paymentParamsString]);

								break;
            }

            $data['redirect'] = $gConfig['auto_payment_redirection'];
						$sendEmail = 0;
				}
				else {
						$sendEmail = 0;
						$thankYouTemplate = 'payment';
				}
    }
    elseif ( $finishType == 'paymentThankYou' ) {
        $sendEmail = 0;
        $thankYouTemplate = 'payment';
    }
    elseif ( $finishType == 'manualThankYou' ) {
        $sendEmail = 1;
        $thankYouTemplate = 'quote';
    }
    else {
        $sendEmail = 1;
        $thankYouTemplate = 'booking';
    }


		// Thank you page
    if ( !empty($thankYouTemplate) ) {
        $eBooking = \App\Models\BookingRoute::find($firstRouteID);

        $qProfileConfig = \App\Models\Config::getBySiteId($eBooking->booking->site_id)->loadLocale()->mapData()->getData();
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
            'url_customer' => $pConfig['url_customer']
        ];

        $eSettings = (object)[
            'booking_summary_enable' => $pConfig['booking_summary_enable'],
            'booking_request_time' => $pConfig['booking_request_time'],
        ];

        $html = view('thank_you.'. $thankYouTemplate, [
            'company' => $eCompany,
            'settings' => $eSettings,
            'booking' => $eBooking,
            'transaction' => $transaction,
            'payment' => $payment,
        ])->render();
    }


    // Send notification
    if ( $sendEmail == 1 && !empty($qBookingRoute) && empty(session('notification_sent', 0)) ) {
        foreach($qBookingRoute as $key => $value) {
            $eBooking = \App\Models\BookingRoute::find($value->id);
						$notifications = [[
								// 'type' => (!empty($thankYouTemplate) && $thankYouTemplate == 'quote') ? 'quote' : 'pending',
								'type' => (!empty($thankYouTemplate) && $thankYouTemplate == 'quote') ? 'quote' : $eBooking->status,
						]];
						event(new \App\Events\BookingStatusChanged($eBooking, $notifications));
						session(['notification_sent' => 1]);
        }
    }

		// Adwords start
		$data['payment_currency'] = config('site.currency_code') ? config('site.currency_code') : '';
		$data['payment_method'] = '';
		$data['payment_value'] = 0;
		$data['payment_value_f'] = 0;

		if (!empty($transaction->id)) {
			$data['payment_method'] = $transaction->payment_name;
			$data['payment_value'] = $transaction->amount + $transaction->payment_charge;
			$data['payment_value_f'] = SiteHelper::formatPrice($transaction->amount + $transaction->payment_charge);
		}
		// Adwords end

		if (empty($html)) {
				$html = '<div class="alert alert-danger" style="text-align:center;">The transaction no longer exists. Please make sure you are using valid payment link.</div>';
		}

    $data['html'] = $html;
}
else {
    $data['message'][] = $gLanguage['API']['ERROR_NO_BOOKING'];
}
