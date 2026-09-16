<?php

// \Log::debug([\Request::fullUrl(), $etoPost]);

// Stripe webhook
if ( !empty($etoPost['webhook']) && $etoPost['webhook'] == 'stripe' ) {
		$postData = $etoPost;
		// \Log::debug((array)$postData);

		// https://example.com/etov2?apiType=frontend&task=notify&webhook=stripe
		// !strcmp($_SERVER['REQUEST_METHOD'],'POST')

		// $testRequest = json_decode('Enter webhook request here', true);
		// $postData = array_merge($postData, $testRequest);

		if (!empty($postData['type'])) {
				switch ($postData['type']) {
						case 'source.chargeable':
								sleep(5);

								if (!empty($postData['data']['object']['id'])) {
										$stripe_source = $postData['data']['object']['id'];
										$stripe_client_secret = $postData['data']['object']['client_secret'];
										$stripe_livemode = $postData['livemode'] ? 'true' : 'false';

										$transactions = \App\Models\Transaction::whereIn('payment_method', ['stripe', 'stripe_ideal'])
											->where('status', 'pending')
											->where('params', '!=', 'null')
											->get();

										foreach ($transactions as $transaction) {
												$params = !empty($transaction->params) ? json_decode($transaction->params) : new \stdClass;
												if (!empty($params->stripe_source) && $params->stripe_source == $stripe_source &&
													 !empty($params->stripe_client_secret) && $params->stripe_client_secret == $stripe_client_secret) {

													 $no_redirect = '';
													 if ($transaction->payment_method == 'stripe') {
															$no_redirect = '&no_redirect=1';
													 }

													 $redirectUrl = url('/etov2') .'?apiType=frontend&task=notify&wb=stripe'.
														'&pMethod='. $transaction->payment_method .
														'&tID='. $transaction->unique_key .
														'&livemode='. $stripe_livemode .
														'&client_secret='. $stripe_client_secret .
														'&source='. $stripe_source .
														$no_redirect;

													 // \Log::debug($redirectUrl);

													 header("Location: ". $redirectUrl);
													 die;
												}
										}
								}

						break;
						case 'checkout.session.completed':

								// https://stripe.com/docs/payments/checkout/fulfillment
								if (!empty($postData['data']['object']['client_reference_id'])) {
										$etoPost['pMethod'] = 'stripe';
										$etoPost['tID'] = $postData['data']['object']['client_reference_id'];
										$etoPost['no_redirect'] = 0;
								}

						break;
				}
		}

		// dd([$postData, $testRequest, $source, $transactions, $redirectUrl]);
}

// dd([$etoPost]);
// \Log::debug($etoPost);

$pMethod = (string)$etoPost['pMethod'];

if ( $pMethod == 'epdq' ) {
	$tID = (string)$etoPost['orderID'];
}
else {
	$tID = (string)$etoPost['tID'];
}

// Get Transaction
if ( !empty($tID) ) {
		$transaction = \App\Models\Transaction::where('relation_type', 'booking')->where('unique_key', $tID)->first();
}

// Get booking
if ( !empty($transaction->id) ) {
		// dd($transaction);

    $sql = "SELECT *, `ref_number` AS `general_ref_number`
            FROM `{$dbPrefix}booking`
            WHERE `id`='". $transaction->relation_id ."'
            LIMIT 1";

    $qBooking = $db->select($sql);
    if (!empty($qBooking[0])) {
        $qBooking = $qBooking[0];
    }

		if ( !empty($qBooking) ) {
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

				// Config
				$gConfig = array();

				$sql = "SELECT *
								FROM `{$dbPrefix}config`
								WHERE `site_id`='". $qBooking->site_id ."'
								ORDER BY `key` ASC";

				$qConfig = $db->select($sql);
				if ( !empty($qConfig) ) {
						foreach($qConfig as $key => $value) {
								$gConfig[$value->key] = $value->value;
						}
				}

				// Route
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

				if ( $transaction->status != 'paid') {

						// Transaction update
						if ( 1 ) {
							$transaction->response .= "\r\n\r\n-- ". $_SERVER['REMOTE_ADDR'] ." --------\r\n";
							if ( !empty($_GET) ) {
								$transaction->response .= "\r\nGET: ". http_build_query($_GET) ."\r\n";
							}
							if ( !empty($_POST) ) {
								$transaction->response .= "\r\nPOST: ". http_build_query($_POST) ."\r\n";
							}
						}

						$transaction->ip = $_SERVER['REMOTE_ADDR'];
						$transaction->updated_at = \Carbon\Carbon::now();

		        // http://domain.com/etov2?apiType=frontend&task=notify&tID=XXX&pMethod=epdq

						$sendEmail = 0;

		        switch( $payment->method ) {
		            case 'epdq':

		                if ( !strcmp($_SERVER['REQUEST_METHOD'],'POST') && !empty($etoPost) && !empty($_POST['SHASIGN']) ) {
		                    if ( $etoPost['STATUS'] == '5' || $etoPost['STATUS'] == '9' ) {
		                        $transaction->status = 'paid';
														$sendEmail = 1;
		                    }
		                }

		            break;
		            case 'paypal':

		                // https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNIntro/#id08CKFJ00JYK

										if ( !empty((int)$payment->params->test_mode) ) { // Test
												$paymentUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_notify-validate&'. http_build_query($_POST);
										}
										else { // Production
												$paymentUrl = 'https://www.paypal.com/cgi-bin/webscr?cmd=_notify-validate&'. http_build_query($_POST);
										}

										$agent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36';

										if (function_exists('curl_init')) {
												$curl = curl_init();
												curl_setopt($curl, CURLOPT_URL, $paymentUrl);
												curl_setopt($curl, CURLOPT_USERAGENT, $agent);
												curl_setopt($curl, CURLOPT_TIMEOUT, 30);
												curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
												curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
												curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
												$isVerified = curl_exec($curl);
												curl_close($curl);
										}
										else {
												$isVerified = file_get_contents($paymentUrl);
										}

										// \Log::debug((array)request()->all());
										// \Log::debug($paymentUrl);
										// \Log::debug($isVerified);

		                if ( !strstr($isVerified, 'VERIFIED' ) ) {
		                    return false;
		                }

										// && in_array($_SERVER['REMOTE_ADDR'] , array('213.254.248.98', '212.35.124.164', '213.121.209.27', '217.140.33.25'))
		                if ( !strcmp($_SERVER['REQUEST_METHOD'],'POST') && !empty($etoPost) ) {
		                    if ( $etoPost['payment_status'] == 'Completed' ) {
														$transaction->status = 'paid';
														$sendEmail = 1;
		                    }
		                    elseif ( $etoPost['payment_status'] == 'Pending' ) {
														$transaction->status = 'pending';
		                    }
		                }

		            break;
								case 'payzone':

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

										$paymentParams = array(
												'StatusCode' => $etoPost['StatusCode'],
												'Message' => $etoPost['Message'],
												'PreviousStatusCode' => $etoPost['PreviousStatusCode'],
												'PreviousMessage' => $etoPost['PreviousMessage'],
												'CrossReference' => $etoPost['CrossReference'],
												'AddressNumericCheckResult' => $etoPost['AddressNumericCheckResult'],
												'PostCodeCheckResult' => $etoPost['PostCodeCheckResult'],
												'CV2CheckResult' => $etoPost['CV2CheckResult'],
												'ThreeDSecureAuthenticationCheckResult' => $etoPost['ThreeDSecureAuthenticationCheckResult'],
												'CardType' => $etoPost['CardType'],
												'CardClass' => $etoPost['CardClass'],
												'CardIssuer' => $etoPost['CardIssuer'],
												'CardIssuerCountryCode' => $etoPost['CardIssuerCountryCode'],
												'Amount' => $etoPost['Amount'],
												'CurrencyCode' => $etoPost['CurrencyCode'],
												'OrderID' => $etoPost['OrderID'],
												'TransactionType' => $etoPost['TransactionType'],
												'TransactionDateTime' => $etoPost['TransactionDateTime'],
												'OrderDescription' => $etoPost['OrderDescription'],
												'CustomerName' => $etoPost['CustomerName'],
												'Address1' => $etoPost['Address1'],
												'Address2' => $etoPost['Address2'],
												'Address3' => $etoPost['Address3'],
												'Address4' => $etoPost['Address4'],
												'City' => $etoPost['City'],
												'State' => $etoPost['State'],
												'PostCode' => $etoPost['PostCode'],
												'CountryCode' => $etoPost['CountryCode'],
												'EmailAddress' => $etoPost['EmailAddress'],
												'PhoneNumber' => $etoPost['PhoneNumber'],
										);

										// magic quotes fix
										if ( get_magic_quotes_gpc() ) {
												$paymentParams = array_map('stripslashes', $paymentParams);
										}

										$hashcode = createhash($PreSharedKey, $MerchantID, $Password, $paymentParams);
										$posthashcode = $etoPost['HashDigest'];

										if ( !strcmp($_SERVER['REQUEST_METHOD'],'POST') && !empty($etoPost) && $hashcode == $posthashcode ) {
												if ( $etoPost['StatusCode'] == '0' ) {
														$transaction->status = 'paid';
														$sendEmail = 1;
												}
										}

								break;
		            case 'cardsave':

		                // http://www.cardsave.net/Developer-Support
		                // https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentFormHelper.aspx?HelperType=PaymentForm

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

		                $paymentParams = array(
		                    'StatusCode' => $etoPost['StatusCode'],
		                    'Message' => $etoPost['Message'],
		                    'PreviousStatusCode' => $etoPost['PreviousStatusCode'],
		                    'PreviousMessage' => $etoPost['PreviousMessage'],
		                    'CrossReference' => $etoPost['CrossReference'],
		                    'Amount' => $etoPost['Amount'],
		                    'CurrencyCode' => $etoPost['CurrencyCode'],
		                    'OrderID' => $etoPost['OrderID'],
		                    'TransactionType' => $etoPost['TransactionType'],
		                    'TransactionDateTime' => $etoPost['TransactionDateTime'],
		                    'OrderDescription' => $etoPost['OrderDescription'],
		                    'CustomerName' => $etoPost['CustomerName'],
		                    'Address1' => $etoPost['Address1'],
		                    'Address2' => $etoPost['Address2'],
		                    'Address3' => $etoPost['Address3'],
		                    'Address4' => $etoPost['Address4'],
		                    'City' => $etoPost['City'],
		                    'State' => $etoPost['State'],
		                    'PostCode' => $etoPost['PostCode'],
		                    'CountryCode' => $etoPost['CountryCode']
		                );

		                // magic quotes fix
		                if ( get_magic_quotes_gpc() ) {
		                    $paymentParams = array_map('stripslashes', $paymentParams);
		                }

		                $hashcode = createhash($PreSharedKey, $MerchantID, $Password, $paymentParams);
		                $posthashcode = $etoPost['HashDigest'];

		                if ( !strcmp($_SERVER['REQUEST_METHOD'],'POST') && !empty($etoPost) && $hashcode == $posthashcode ) {
		                    if ( $etoPost['StatusCode'] == '0' ) {
														$transaction->status = 'paid';
														$sendEmail = 1;
		                    }
		                }

		            break;
		            case 'worldpay':

		                if ( !strcmp($_SERVER['REQUEST_METHOD'],'POST') && !empty($etoPost) ) {
		                    if ( $etoPost['transStatus'] == 'Y' ) { // Successful
														$transaction->status = 'paid';
														$sendEmail = 1;
		                    }
		                    elseif ( $etoPost['transStatus'] == 'C' ) { // Cancelled
														$transaction->status = 'canceled';
		                    }
		                }

		            break;
		            case 'redsys':

		                if ( !strcmp($_SERVER['REQUEST_METHOD'],'POST') && !empty($etoPost) ) {
		                    $Ds_SignatureVersion = $etoPost["Ds_SignatureVersion"];
		                    $Ds_MerchantParameters = $etoPost["Ds_MerchantParameters"];
		                    $Ds_Signature = $etoPost["Ds_Signature"];

		                    include(base_path('vendor/easytaxioffice/apiRedsys.php'));

		                    $miObj = new \RedsysAPI;
		                    $encryptionKey = trim($payment->params->encryption_key);
		                    $merchantID = trim($payment->params->merchant_id);
		                    $decodec = $miObj->decodeMerchantParameters($Ds_MerchantParameters);
		                    $firma = $miObj->createMerchantSignatureNotif ($encryptionKey, $Ds_MerchantParameters);

		                    $total = $miObj->getParameter('Ds_Amount');
		                    $pedido = $miObj->getParameter('Ds_Order');
		                    $codigo = $miObj->getParameter('Ds_MerchantCode');
		                    $moneda = $miObj->getParameter('Ds_Currency');
		                    $respuesta = $miObj->getParameter('Ds_Response');
		                    $id_trans = $miObj->getParameter('Ds_AuthorisationCode');
		                    $fecha = $miObj->getParameter('Ds_Date');
		                    $hora = $miObj->getParameter('Ds_Hour');
		                    $respuesta = intval($respuesta);

												if ( !empty($respuesta) ) {
				                    // $transaction->response .= "\r\nResponse: ". $respuesta ."\r\n";
												}

		                    if ( $firma === $Ds_Signature && $respuesta < 101 && $merchantID == $codigo ) {
														$transaction->status = 'paid';
														$sendEmail = 1;
		                    }
		                }
		            break;
		            case 'stripe_ideal':

									if ( !empty($etoPost["source"]) && !empty($etoPost) ) {
											$stripeSource = $etoPost["source"];
											$stripeClientSecret = $etoPost["client_secret"];

											$total = $transaction->amount + $transaction->payment_charge;
											$totalAmount = $total * 100; // Production amount

											if ( !empty((int)$payment->params->test_mode) ) { // Test
													$secretKey = $payment->params->sk_test;

													if ( !empty((float)$payment->params->test_amount) ) {
															$totalAmount = (float)$payment->params->test_amount * 100; // Test amount
													}
											}
											else { // Production
													$secretKey = $payment->params->sk_live;
											}

			                if ( \Request::isSecure() ) {
			                    $curl = new \Stripe\HttpClient\CurlClient([CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1]);
			                    \Stripe\ApiRequestor::setHttpClient($curl);
			                }

			                \Stripe\Stripe::setApiKey($secretKey);

											try {
													$source = \Stripe\Source::retrieve($stripeSource);
													// $source->status = 'pending'; // Test only

													$params = new \stdClass;
													if (!empty($transaction->params)) {
															$params = json_decode($transaction->params);
													}
													$params->stripe_source = $stripeSource;
													$params->stripe_client_secret = $stripeClientSecret;
													$transaction->params = !empty($params) ? json_encode($params) : null;

													if ($source->status === 'chargeable') {
															try {
																	$customer = \Stripe\Customer::create([
																			'email' => $stripeEmail,
																			'description' => $contactName,
																			'source' => $stripeSource,
																	]);

																	try {
																			$charge = \Stripe\Charge::create([
																					'amount' => $totalAmount,
																					'currency' => 'eur',
																					'description' => trim($transaction->name .' #'. $qBooking->ref_number),
																					'customer' => $customer->id,
																					'source' => $stripeSource,
																			]);

																			// if ( !empty($charge) ) {
																			// 	$transaction->response .= "\r\nResponse: ". $charge ."\r\n";
																			// }

																			if ( $charge->paid ) {
																					$transaction->status = 'paid';
																					$sendEmail = 1;
																			}
																	}
																	catch(\Stripe\Error\Card $e) {
																			$transaction->status = 'declined';
																	}
															}
															catch(\Exception $e) {
																	$body = $e->getJsonBody();
																	$err  = $body['error'];
																	$data['error_message'] = $err['message'];
																	// dd([$e, $err]);
															}
													}
													elseif ($source->status === 'canceled') {
															$transaction->status = 'canceled';
													}
													elseif ($source->status === 'failed') {
															$transaction->status = 'declined';
													}
													elseif ($source->status === 'consumed') {
															$data['error_message'] = 'The payment has been already taken.';
													}
													elseif ($source->status === 'pending') {
															$transaction->status = 'pending';
													}

													// dd([$source, $source->status, $stripeSource, $transaction, $transaction->params, $data]);
											}
											catch(\Exception $e) {
													$body = $e->getJsonBody();
	  											$err  = $body['error'];
													$data['error_message'] = $err['message'];
													// dd([$e, $err]);
											}
									}

		            break;
		            case 'stripe':

										if (!empty((int)$payment->params->sca_mode) && !empty($etoPost['type']) && $etoPost['type'] == 'checkout.session.completed' && !empty($etoPost['data']['object']['payment_intent'])) {
												$payment_intent = $etoPost['data']['object']['payment_intent'];

												if (!empty((int)$payment->params->test_mode)) { // Test
														$secretKey = $payment->params->sk_test;
												}
												else { // Production
														$secretKey = $payment->params->sk_live;
												}

												try {
														if (\Request::isSecure()) {
						                    $curl = new \Stripe\HttpClient\CurlClient(array(CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1));
						                    \Stripe\ApiRequestor::setHttpClient($curl);
						                }

														\Stripe\Stripe::setApiKey($secretKey);
														$intent = \Stripe\PaymentIntent::retrieve($payment_intent);
														// \Log::debug($intent);
														$error_message = $intent->last_payment_error ? $intent->last_payment_error->message : "";

														if ($error_message) {
																\Log::error('Stripe notify payment intent error: '. $error_message);
														}

														if ($intent->status === 'succeeded') {
																$transaction->status = 'paid';
																$sendEmail = 1;
														}
														elseif ($intent->status === 'canceled') {
																$transaction->status = 'canceled';
														}
												}
												catch (\Exception $e) {
														$data['error_message'] = $e->getMessage();
														\Log::error('Stripe notify error: '. $e->getMessage());
												}

												// https://stripe.com/docs/payments/payment-intents/verifying-status
												// https://stripe.com/docs/api/payment_intents/object
										}
										elseif ( !strcmp($_SERVER['REQUEST_METHOD'],'POST') && !empty($etoPost) ) {
												$stripeSource = $etoPost["stripeSource"];
												$stripeToken = $etoPost["stripeToken"];
												$stripeTokenType = $etoPost["stripeTokenType"];
												$stripeEmail = $etoPost["stripeEmail"];

												$total = $transaction->amount + $transaction->payment_charge;
												$totalAmount = $total * 100; // Production amount

												if ( !empty((int)$payment->params->test_mode) ) { // Test
													$secretKey = $payment->params->sk_test;

													if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount * 100; // Test amount
													}
												}
												else { // Production
													$secretKey = $payment->params->sk_live;
												}

				                if ( \Request::isSecure() ) {
				                    $curl = new \Stripe\HttpClient\CurlClient(array(CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1));
				                    \Stripe\ApiRequestor::setHttpClient($curl);
				                }

				                \Stripe\Stripe::setApiKey($secretKey);

												if ( $stripeSource ) {
													$source = $stripeSource;
												}
												else {
													$source = $stripeToken;
												}

												$customer = \Stripe\Customer::create(array(
													'email' => $stripeEmail,
													'description' => $contactName,
													'source' => $source,
												));

			                	$chargeStatus = '';

												try {
													$charge = \Stripe\Charge::create(array(
														'amount' => $totalAmount,
														'currency' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP',
														'description' => trim($transaction->name .' #'. $qBooking->ref_number),
														'customer' => $customer->id,
														'source' => !empty($stripeSource) ? $source : null,
													));

													if ( $charge->paid ) {
														$chargeStatus = 'paid';
													}
												}
												catch(\Stripe\Error\Card $e) {
													$chargeStatus = 'declined';
												}

											// if ( !empty($charge) ) {
											// 	$transaction->response .= "\r\nResponse: ". $charge ."\r\n";
											// }

											if ( $chargeStatus == 'paid' ) {
												$transaction->status = 'paid';
												$sendEmail = 1;
											}
											elseif ( $chargeStatus == 'declined' ) {
												$transaction->status = 'declined';
											}
										}

		            break;
								case 'wpop':

										if (!strcmp($_SERVER['REQUEST_METHOD'],'POST') && !empty($etoPost)) {
												$worldpayErrorMsg = '';
												$worldpayToken = $etoPost["token"];
												$total = $transaction->amount + $transaction->payment_charge;
												$totalAmount = $total * 100; // Production amount

												if ( !empty((int)$payment->params->test_mode) ) { // Test
														$secretKey = $payment->params->sk_test;
														if ( !empty((float)$payment->params->test_amount) ) {
																$totalAmount = (float)$payment->params->test_amount * 100; // Test amount
														}
												}
												else { // Production
														$secretKey = $payment->params->sk_live;
												}

												if (empty($secretKey)) {
														\Log::warning('Missing Worldpay Online Payments service key is payment config tab.');
												}

												try {
														$worldpay = new \Worldpay\Worldpay($secretKey);

												    $response = $worldpay->createOrder(array(
												        'token' => $worldpayToken,
												        'amount' => $totalAmount,
												        'currencyCode' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP',
												        'orderDescription' => trim($transaction->name .' #'. $qBooking->ref_number),
												        'customerOrderCode' => $qBooking->ref_number,
																'name' => $contactName,
																// https://beta.developer.worldpay.com/docs/wpop/3d-secure
																// 'is3DSOrder' => (isset($payment->params->three_d_secure) ? $payment->params->three_d_secure : 'false'),
												        // 'billingAddress' => array(
																//     "address1"=>'123 House Road',
																//     "address2"=> 'A village',
																//     "address3"=> '',
																//     "postalCode"=> 'EC1 1AA',
																//     "city"=> 'London',
																//     "state"=> '',
																//     "countryCode"=> 'GB',
																// ),
												    ));

												    if ($response['paymentStatus'] === 'SUCCESS') {
												        $worldpayOrderCode = $response['orderCode'];
																$transaction->status = 'paid';
																$sendEmail = 1;
												    }
														else {
												        throw new \Worldpay\WorldpayException(print_r($response, true));
												    }
												}
												catch (\Worldpay\WorldpayException $e) {
												    $worldpayErrorMsg = "Error code: " .$e->getCustomCode() ."\r\nHTTP status code:". $e->getHttpStatusCode() ."\r\nError description: ". $e->getDescription()  ."\r\nError message: ". $e->getMessage();
														$transaction->status = 'declined';
												}
												catch (\Exception $e) {
												    $worldpayErrorMsg = 'Error message: '. $e->getMessage();
														$transaction->status = 'declined';
												}

												if (!empty($worldpayErrorMsg)) {
														$transaction->response .= "\r\nError: ". $worldpayErrorMsg ."\r\n";
												}

												// dd($etoPost, $worldpayErrorMsg, $response, $chargeStatus, $transaction->status);
										}

								break;
								case 'square':

										$total = $transaction->amount + $transaction->payment_charge;
										$totalAmount = $total * 100;
										$legacyMode = (!isset($payment->params->legacy_mode) || (int)$payment->params->legacy_mode == 1) ? true : false;

										if (!empty((int)$payment->params->test_mode)) {
                        $apiUrl = 'https://connect.squareupsandbox.com';
												$locationId = $payment->params->test_location_id;
												$accessToken = $payment->params->test_access_token;

												if ( !empty((float)$payment->params->test_amount) ) {
														$totalAmount = (float)$payment->params->test_amount * 100;
												}
										}
										else {
                        $apiUrl = 'https://connect.squareup.com';
												$locationId = $payment->params->live_location_id;
												$accessToken = $payment->params->live_access_token;
										}

										$total = $transaction->amount + $transaction->payment_charge;

										$returnedCheckoutId = $_GET["checkoutId"];
										$returnedOrderId = $_GET["referenceId"];
										$returnedTransactionId = $_GET["transactionId"];

										$apiConfig = new \SquareConnect\Configuration();
										if ($legacyMode == false) {
												$apiConfig->setHost($apiUrl);
										}
										$apiConfig->setAccessToken($accessToken);
										$apiConfig->setSSLVerification(request()->isSecure());
										$defaultApiClient = new \SquareConnect\ApiClient($apiConfig);
										$transactionClient = new \SquareConnect\Api\TransactionsApi($defaultApiClient);

										try {
												sleep(5);

												// Change it to webhook to avoid error that transaction is not found (sometimes redirection happens faster).
											  // $apiResponse = $transactionClient->retrieveTransaction($locationId, $returnedTransactionId);
												list($apiResponse, $apiStatusCode, $apiHttpHeader) = $transactionClient->retrieveTransactionWithHttpInfo($locationId, $returnedTransactionId);

												$savedCheckoutId = '';
												$oldTransaction = \App\Models\Transaction::find($transaction->id);

												if ($oldTransaction->id) {
														$response = json_decode($oldTransaction->response);

														if ($response && $response->checkout_id) {
																$savedCheckoutId = $response->checkout_id;
														}
												}

											  $savedOrderTotal = $totalAmount;

											  $calculatedOrderTotal = 0;
											  $cardCaptured = false;
											  $totalMatch = false;
											  $checkoutIdMatch = false;

											  foreach ($apiResponse['transaction']['tenders'] as $tender) {
												    $calculatedOrderTotal += $tender['amount_money']['amount'];

												    if ($tender['type'] == "CARD") {
													      $cardCaptured = ($tender['card_details']['status'] == "CAPTURED");
													      if (!$cardCaptured) { return false; }
												    }
											  }

											  $totalMatch = ($calculatedOrderTotal == $savedOrderTotal);
											  $checkoutIdMatch = ($returnedCheckoutId == $savedCheckoutId);

												if ($totalMatch && $cardCaptured && $checkoutIdMatch) {
														$transaction->status = 'paid';
														$sendEmail = 1;
												}
										}
										catch (Exception $e) {
												$errMsg = 'The SquareConnect exception when calling TransactionsApi->retrieveTransaction: '. $e->getMessage();
                        \Log::error($errMsg);
												echo $errMsg;
												exit;
										}

								break;
								case 'gpwebpay':

										if (isset($etoPost['PRCODE']) && isset($etoPost['DIGEST']) && isset($etoPost['MD']) && !empty($etoPost)) {
												$savedDigestHash = '';
												$oldTransaction = \App\Models\Transaction::find($transaction->id);

												if ($oldTransaction->id) {
													$response = json_decode($oldTransaction->response);

													if ($response && $response->digest_hash) {
															$savedDigestHash = $response->digest_hash;
													}
												}

												if ($etoPost['MD'] == $savedDigestHash) {
														$PRCODE = (int)$etoPost['PRCODE'];
														switch ($PRCODE) {
																case 0:
																		$transaction->status = 'paid';
																		$sendEmail = 1;
																break;
																case 28:
																case 30:
																		$transaction->status = 'declined';
																break;
														}

														if (!empty($etoPost['RESULTTEXT']) && $PRCODE != 0) {
																$data['error_message'] = $etoPost['RESULTTEXT'];
														}
												}
										}

										// dd([$etoPost, $transaction, $transaction->status, $sendEmail]);
										// \Log::debug([$savedDigestHash, $etoPost['DIGEST'], $etoPost['DIGEST1'], $etoPost, $transaction, $transaction->status, $sendEmail]);

								break;
		        }

						// $transaction->status = 'paid'; // Test only
		        // $sendEmail = 1; // Test only

						// Save transaction
						$transaction->response = trim($transaction->response);
						$transaction->save();


						// Update status
						if ( $transaction->status == 'paid' ) {
								$bookingRoutes = \App\Models\BookingRoute::where('booking_id', '=', $transaction->relation_id)
									->where('status', 'incomplete')->get();

								foreach ($bookingRoutes as $bKey => $bRoute) {
										$bStatus = 'pending';
										// dd($bRoute->params);
										if (!empty($bRoute->params->new_status) && $bRoute->params->new_status == 'requested') {
												$bStatus = $bRoute->params->new_status;
												// unset($bRoute->params->new_status);
										}
										$bRoute->status = $bStatus;
										$bRoute->save();
								}

								// \App\Models\BookingRoute::where('booking_id', '=', $transaction->relation_id)
								// 	->where('status', '=', 'incomplete')
								// 	->update(['status' => 'pending']);
						}


		        // Send notification
		        if ( $sendEmail == 1 ) {
								$sql = "SELECT *
												FROM `{$dbPrefix}booking_route`
												WHERE `booking_id`='" . $qBooking->id . "'
												ORDER BY `route` ASC";
								$queryRoute = $db->select($sql);

								if ( !empty($queryRoute) ) {
										foreach ($queryRoute as $routeKey => $routeValue) {
												$eBooking = \App\Models\BookingRoute::find($routeValue->id);
												event(new \App\Events\BookingStatusChanged($eBooking));
										}
								}
		        }
				}

				// Payment error message
				if (!empty($data['error_message'])) {
						$tMSG = '&tMSG='. $data['error_message'];
				}
				else {
						$tMSG = '';
				}

				if ( $payment->method == 'payzone' || $payment->method == 'cardsave' ) {
						echo("StatusCode=". $etoPost['StatusCode'] ."&Message=". $etoPost['Message']);
						die;
				}
				elseif ($payment->method == 'stripe' && !empty($payment->params->sca_mode) && !empty($etoPost['type']) && $etoPost['type'] == 'checkout.session.completed') {
						http_response_code(200);
						die;
				}
				elseif ( $payment->method == 'square' || $payment->method == 'stripe' || $payment->method == 'wpop' ) {
						header("Location: ". url('/booking') .'?finishType=paymentThankYou&bID='. $qBooking->unique_key .'&tID='. $transaction->unique_key .'&no_redirect=1'. $tMSG);
						die;
				}
				elseif ( $payment->method == 'stripe_ideal' ) {
						header("Location: ". url('/booking') .'?finishType=paymentThankYou&bID='. $qBooking->unique_key .'&tID='. $transaction->unique_key .''. $tMSG);
						die;
				}
				elseif ( $payment->method == 'gpwebpay' ) {
						if ($transaction->status == 'paid') {
								header("Location: ". url('/booking') .'?finishType=paymentThankYou&bID='. $qBooking->unique_key .'&tID='. $transaction->unique_key .''. $tMSG);
						}
						else {
								header("Location: ". url('/booking/cancel'));
						}
						die;
				}
		}
		else {
				$data['message'][] = $gLanguage['API']['ERROR_NO_BOOKING'];
		}
}
