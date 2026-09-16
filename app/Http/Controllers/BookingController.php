<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Helpers\SiteHelper;

class BookingController extends Controller
{
    public function cancel($id = null)
    {
        $errors = [];
        $bIDs = [];

        if ($id) {
            // $id = base64_decode($id);
            $bIDs = (array)explode(',', $id);
            foreach( $bIDs as $k => $v ) {
                if ( !$v ) { unset($bIDs[$k]); }
            }
        }

        $bookings = \App\Models\BookingRoute::whereIn('id', $bIDs)->orderBy('ref_number', 'asc')->get();

        // if ( $bookings->isEmpty() ) {
        //     $errors[] = trans('booking.page.cancel.errors.no_bookings');
        // }

        $ref_number = '';
        foreach ($bookings as $bk => $booking) {
            $ref_number .= ($ref_number ? ', ' : '') .'<span class="ref-number">'. $booking->getRefNumber() .'</span>';
        }

        if ($errors) {
            return view('booking.error', [
                'errors' => $errors,
            ]);
        }

        return view('booking.cancel', [
            'bookings' => $bookings,
            'ref_number' => $ref_number,
        ]);
    }

    public function finish($id = null)
    {
        // Add option to resend booking email
        // Add language switcher

        $errors = [];
        $booking = null;
        $config = null;
        $bIDs = [];
        $grouped = [];

        if ( $id ) {
            // $id = base64_decode($id);
            $bIDs = (array)explode(',', $id);
            foreach( $bIDs as $k => $v ) {
                if ( !$v ) { unset($bIDs[$k]); }
            }
        }

        $bookings = \App\Models\BookingRoute::whereIn('id', $bIDs)->orderBy('ref_number', 'asc')->get();

        foreach( $bookings as $booking ) {
            switch( $booking->status ) {
                case 'requested':
                    $index = 'request';
                break;
                case 'quote':
                    $index = 'quote';
                break;
                default:
                    $index = 'confirm';
                break;
            }

            $grouped[$index][] = $booking;
        }

        if ( !$booking && !empty($bookings[0]) ) {
            $booking = $bookings[0];
        }

        if ( $booking && !empty($booking->booking->site_id) ) {
            $config = \App\Models\Config::getBySiteId($booking->booking->site_id)->mapData()->getData();
        }

        if ( $bookings->isEmpty() ) {
            $errors[] = trans('booking.page.finish.errors.no_bookings');
        }
        elseif ( !$booking ) {
            $errors[] = trans('booking.page.finish.errors.no_booking');
        }
        elseif ( !$config ) {
            $errors[] = trans('booking.page.finish.errors.no_config');
        }

        if ( $errors ) {
            return view('booking.error', [
                'errors' => $errors,
            ]);
        }

        return view('booking.finish', [
            'grouped' => $grouped,
        ]);
    }

    public function pay($booking_id = null, $transaction_id = null)
    {
        $html = '';
        $errors = [];
        $payment = null;
        $booking = null;
        $config = null;
        $tIDs = [];
        $bIDs = [];
        $total = 0;
        $desc = '';

        if ( $booking_id ) {
            // $booking_id = base64_decode($booking_id);
            $bIDs = (array)explode(',', $booking_id);
            foreach( $bIDs as $k => $v ) {
                if ( !$v ) { unset($bIDs[$k]); }
            }
        }

        if ( $transaction_id ) {
            // $transaction_id = base64_decode($transaction_id);
            $tIDs = (array)explode(',', $transaction_id);
            foreach( $tIDs as $k => $v ) {
                if ( !$v ) { unset($tIDs[$k]); }
            }
        }

        $q = \App\Models\Transaction::where('relation_type', 'booking')->whereIn('relation_id', $bIDs);
        if ( $tIDs ) {
            $q->whereIn('id', $tIDs);
        }
        $transactions = $q->get();

        // dd($bIDs, $tIDs, $transactions);

        foreach( $transactions as $k => $transaction ) {
            if ( !$transaction->payment_method ||
                in_array($transaction->payment_method, ['cash', 'account', 'bacs', 'none']) ||
                $transaction->status == 'paid' ) {
                unset($transactions[$k]);
                continue;
            }

            if ( !$payment && $transaction->payment_id ) {
                $payment = $transaction->payment;
                if ( $payment && $payment->params ) {
                    $payment->params = json_decode($payment->params);
                }
            }

            if ( !$booking ) {
                $booking = $transaction->booking;
            }

            $desc .= ($desc ? ', ' : '') . ($transaction->name ? $transaction->name : 'Booking');
            $total += $transaction->amount + $transaction->payment_charge;
        }

        if ( $booking && !empty($booking->booking->site_id) ) {
            $config = \App\Models\Config::getBySiteId($booking->booking->site_id)->mapData()->getData();
        }

        if ( $transactions->isEmpty() ) {
            $errors[] = trans('booking.page.pay.errors.no_transactions');
        }
        elseif ( !$payment ) {
            $errors[] = trans('booking.page.pay.errors.no_payment');
        }
        elseif ( !$booking ) {
            $errors[] = trans('booking.page.pay.errors.no_booking');
        }
        elseif ( !$config ) {
            $errors[] = trans('booking.page.pay.errors.no_config');
        }

        if ( $errors ) {
            return view('booking.error', [
                'errors' => $errors,
            ]);
        }

        $bIDs = implode(',', $bIDs);
        $tIDs = implode(',', $tIDs);

        $bookingUrl = $config->url_booking;
        $notifyUrl = route('booking.notify', $tIDs) .'?payment_method='. $payment->method;
        $finishUrl = route('booking.finish', $bIDs);
        $cancelUrl = route('booking.cancel', $bIDs);
    // Redirect to finish page immediately for cash payment
    if ($payment && $payment->method == 'cash') {
         return redirect()->route('booking.finish', $bIDs);

    }


        switch( $payment->method ) {
            case 'epdq':

                $epdqPassPhrase = trim($payment->params->pass_phrase);
                $langCode = str_replace('-', '_', $config->language);

                if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                    $langCode = trim($payment->params->language_code);
                }

                if ( !empty((int)$payment->params->test_mode) ) {
                    $paymentUrl = 'https://mdepayments.epdq.co.uk/ncol/test/orderstandard.asp';
                }
                else {
                    $paymentUrl = 'https://payments.epdq.co.uk/ncol/prod/orderstandard.asp';
                }

                if ( !empty((float)$payment->params->test_amount) ) {
                    $total = (float)$payment->params->test_amount * 100;
                }
                else {
                    $total = $total * 100;
                }

                $epdqParams = array(
                    'ACCEPTURL' => $finishUrl,
                    'AMOUNT' => $total,
                    'BACKURL' => $bookingUrl,
                    'CANCELURL' => $cancelUrl,
                    'CATALOGURL' => $bookingUrl,
                    'COM' => $desc .' #'. $booking->ref_number,
                    'CN' => $booking->contact_name,
                    'CURRENCY' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP',
                    'DECLINEURL' => $cancelUrl,
                    'EMAIL' => $booking->contact_email,
                    'EXCEPTIONURL' => $bookingUrl,
                    'HOMEURL' => $bookingUrl,
                    'LANGUAGE' => $langCode,
                    'OPERATION' => ($payment->params->operation_mode) ? trim($payment->params->operation_mode) : 'SAL', // RES: request for authorisation | SAL: request for sale (payment)
                    // 'ORDERID' => $booking->ref_number,
                    'ORDERID' => $tIDs,
                    'OWNERTELNO' => $booking->contact_mobile,
                    // 'OWNERADDRESS' => $booking->contact_address,
                    // 'OWNERZIP' => $booking->contact_postcode,
                    'PARAMVAR' => trim($payment->params->paramvar),
                    'PSPID' => trim($payment->params->pspid),
                    'TITLE' => $config->company_name
                );

                ksort($epdqParams);

                $inputs = '';
                $epdqShasign = '';
                foreach( $epdqParams as $k => $v ) {
                    if ( !empty($v) ) {
                        $inputs .= '<input type="hidden" name="'. $k .'" value="'. $v .'">';
                        $epdqShasign .= $k .'='. $v . $epdqPassPhrase;
                    }
                }
                $epdqShasign = strtoupper(sha1($epdqShasign));

                $html = '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">'.
                            $inputs.
                            '<input type="hidden" name="SHASIGN" value="'.$epdqShasign.'">'.
                            '<input type="submit" name="submit" value="'. trans('booking.page.pay.btn_pay_now') .'" class="button btn btn-primary">'.
                        '</form>';

            break;
            case 'paypal':

                // https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/

                if ( !empty((int)$payment->params->test_mode) ) {
                    $paymentUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
                }
                else {
                    $paymentUrl = 'https://www.paypal.com/cgi-bin/webscr';
                }

                if ( !empty((float)$payment->params->test_amount) ) {
                    $total = (float)$payment->params->test_amount;
                }
                else {
                    $total = $total;
                }

                $language = explode('-', $config->language);
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
                    'cancel_return' => $cancelUrl,
                    'charset' => 'utf-8',
                    'rm' => 0, // 0-1 get, 2 post
                    'no_note' => 1,
                    'no_shipping' => 1,
                    'first_name' => '', // $booking->contact_name
                    'last_name' => '',
                    'address1' => '',
                    'address2' => '',
                    'zip' => '',
                    'city' => '',
                    'state' => '',
                    'country' => '',
                    'email' => $booking->contact_email,
                    'night_phone_b' => '', // $booking->contact_mobile
                    'item_name' => $desc .' #'. $booking->ref_number,
                    'quantity' => 1,
                    'amount' => $total,
                    'currency_code' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP'
                );

                ksort($paymentParams);

                $inputs = '';
                foreach($paymentParams as $k => $v) {
                    if ( !empty($v) ) {
                        $inputs .= '<input type="hidden" name="'. $k .'" value="'. $v .'">';
                    }
                }

                $html = '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">'.
                            $inputs.
                            '<input type="submit" name="submit" value="'. trans('booking.page.pay.btn_pay_now') .'" class="button btn btn-primary">'.
                        '</form>';

            break;
            case 'payzone':

                // https://www.payzone.co.uk/media/1989/hostedpaymentform-integration-docs.pdf
                // https://mms.payzoneonlinepayments.com/Login.aspx

                $paymentUrl = 'https://mms.payzoneonlinepayments.com/Pages/PublicPages/PaymentForm.aspx';

                if ( !empty((float)$payment->params->test_amount) ) {
                    $total = (float)$payment->params->test_amount * 100;
                }
                else {
                    $total = $total * 100;
                }

                // Function to get date/time stamp as required by the gateway
                function gatewaydatetime() {
                    return date('Y-m-d H:i:s P');
                }

                // Function to generate a unique OrderID for the transaction (The OrderID can be any AlphaNumeric string - e.g. your own carts order ID if applicable
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
                    'Amount' => $total,
                    'CurrencyCode' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : '826',
                    'EchoAVSCheckResult' => 'true',
                    'EchoCV2CheckResult' => 'true',
                    'EchoThreeDSecureAuthenticationCheckResult' => 'true',
                    'EchoCardType' => 'true',
                    'OrderID' => guid(),
                    'TransactionType' => ($payment->params->operation_mode) ? trim($payment->params->operation_mode) : 'SALE',
                    'TransactionDateTime' => gatewaydatetime(),
                    'CallbackURL' => $finishUrl,
                    'OrderDescription' => $desc .' '. $booking->ref_number,
                    'CustomerName' => stripGWInvalidChars($booking->contact_name),
                    'Address1' => '',
                    'Address2' => '',
                    'Address3' => '',
                    'Address4' => '',
                    'City' => '',
                    'State' => '',
                    'PostCode' => '',
                    'CountryCode' => ($payment->params->country_code) ? trim($payment->params->country_code) : '826',
                    'EmailAddress' => stripGWInvalidChars($booking->contact_email),
                    'PhoneNumber' => stripGWInvalidChars($booking->contact_mobile),
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

                $inputs = '';
                foreach($paymentParams as $k => $v) {
                    $inputs .= '<input type="hidden" name="'. $k .'" value="'. $v .'">';
                }

                $html = '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">'.
                            $inputs.
                            '<input type="submit" name="submit" value="'. trans('booking.page.pay.btn_pay_now') .'" class="button btn btn-primary">'.
                        '</form>';

            break;
            case 'cardsave':

                // http://www.cardsave.net/Developer-Support/Integration-Methods/Redirect
                // https://github.com/CardSave/woocommerce-gateway-cardsave/blob/master/gateway-cardsave.php

                $paymentUrl = 'https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx';

                if ( !empty((float)$payment->params->test_amount) ) {
                    $total = (float)$payment->params->test_amount * 100;
                }
                else {
                    $total = $total * 100;
                }

                // Function to get date/time stamp as required by the gateway
                function gatewaydatetime() {
                    return date('Y-m-d H:i:s P');
                }

                // Function to generate a unique OrderID for the transaction (The OrderID can be any AlphaNumeric string - e.g. your own carts order ID if applicable
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
                    'Amount' => $total,
                    'CurrencyCode' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : '826',
                    'EchoAVSCheckResult' => 'true',
                    'EchoCV2CheckResult' => 'true',
                    'EchoThreeDSecureAuthenticationCheckResult' => 'true',
                    'EchoCardType' => 'true',
                    'OrderID' => guid(),
                    'TransactionType' => ($payment->params->operation_mode) ? trim($payment->params->operation_mode) : 'SALE',
                    'TransactionDateTime' => gatewaydatetime(),
                    'CallbackURL' => $finishUrl,
                    'OrderDescription' => $desc .' '. $booking->ref_number,
                    'CustomerName' => stripGWInvalidChars($booking->contact_name),
                    'Address1' => '',
                    'Address2' => '',
                    'Address3' => '',
                    'Address4' => '',
                    'City' => '',
                    'State' => '',
                    'PostCode' => '',
                    'CountryCode' => ($payment->params->country_code) ? trim($payment->params->country_code) : '826',
                    'EmailAddress' => stripGWInvalidChars($booking->contact_email),
                    'PhoneNumber' => stripGWInvalidChars($booking->contact_mobile),
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

                $inputs = '';
                foreach($paymentParams as $k => $v) {
                    $inputs .= '<input type="hidden" name="'. $k .'" value="'. $v .'">';
                }

                $html = '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">'.
                            $inputs.
                            '<input type="submit" name="submit" value="'. trans('booking.page.pay.btn_pay_now') .'" class="button btn btn-primary">'.
                        '</form>';

            break;
            case 'worldpay':

                // http://support.worldpay.com/support/kb/bg/pdf/rhtml.pdf
                // http://support.worldpay.com/support/kb/bg/testandgolive/tgl5103.html

                // Test details:
                // Visa Card Number: 4917610000000000 or MasterCard Number: 5454545454545454
                // Security Code: 123

                if ( !empty((int)$payment->params->test_mode) ) {
                    $paymentUrl = 'https://secure-test.worldpay.com/wcc/purchase';
                    $testMode = 100;
                }
                else {
                    $paymentUrl = 'https://secure.worldpay.com/wcc/purchase';
                    $testMode = 0;
                }

                if ( !empty((float)$payment->params->test_amount) ) {
                    $total = (float)$payment->params->test_amount;
                }
                else {
                    $total = $total;
                }

                $langCode = $config->language;

                if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                    $langCode = trim($payment->params->language_code);
                }

                $paymentParams = array(
                    'testMode' => $testMode,
                    'instId' => trim($payment->params->inst_id),
                    'cartId' => $booking->ref_number,
                    'amount' => $total,
                    'currency' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP',
                    'desc' => $desc .' #'. $booking->ref_number,
                    'name' => $booking->contact_name,
                    'email' => $booking->contact_email,
                    // 'tel' => $booking->contact_mobile,
                    'lang' => $langCode,
                    'compName' => $config->company_name,
                    'MC_finish_url' => $finishUrl,
                    'MC_notify_url' => $notifyUrl,
                    'MC_cancel_url' => $cancelUrl,
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

                $inputs = '';
                foreach( $paymentParams as $k => $v ) {
                    if ( !empty($v) ) {
                        $inputs .= '<input type="hidden" name="'. $k .'" value="'. $v .'">';
                    }
                }

                $html = '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">'.
                            $inputs.
                            '<input type="submit" name="submit" value="'. trans('booking.page.pay.btn_pay_now') .'" class="button btn btn-primary">'.
                        '</form>';

            break;
            case 'redsys':
                // http://www.redsys.es/en/index.html#descargas

                $language = explode('-', $config->language);
                $langCode = ($language[0]) ? $language[0] : 'en';

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

                if ( !empty((int)$payment->params->test_mode) ) {
                    $paymentUrl = 'https://sis-t.redsys.es:25443/sis/realizarPago';
                }
                else {
                    $paymentUrl = 'https://sis.redsys.es/sis/realizarPago';
                }

                if ( !empty((float)$payment->params->test_amount) ) {
                    $total = (float)$payment->params->test_amount * 100;
                }
                else {
                    $total = $total * 100;
                }

                $merchantID = trim($payment->params->merchant_id);
                $terminalID = trim($payment->params->terminal_id);
                $encryptionKey = trim($payment->params->encryption_key);
                $signatureVersion = ($payment->params->signature_version) ? trim($payment->params->signature_version) : 'HMAC_SHA256_V1';
                $currency = ($payment->params->currency_code) ? trim($payment->params->currency_code) : '826';
                $transactionType = ($payment->params->operation_mode) ? trim($payment->params->operation_mode) : '0';
                $id = time();
                $merchantName = $config->company_name;
                $description = $desc .' #'. $booking->ref_number;

                include(base_path('vendor/easytaxioffice/apiRedsys.php'));

                $miObj = new \RedsysAPI;
                $miObj->setParameter("DS_MERCHANT_AMOUNT", $total);
                $miObj->setParameter("DS_MERCHANT_ORDER", strval($id));
                $miObj->setParameter("DS_MERCHANT_MERCHANTCODE", $merchantID);
                $miObj->setParameter("DS_MERCHANT_CURRENCY", $currency);
                $miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE", $transactionType);
                $miObj->setParameter("DS_MERCHANT_TERMINAL", $terminalID);
                $miObj->setParameter("DS_MERCHANT_MERCHANTURL", $notifyUrl);
                $miObj->setParameter("DS_MERCHANT_URLOK", $finishUrl);
                $miObj->setParameter("DS_MERCHANT_URLKO", $cancelUrl);
                $miObj->setParameter("DS_MERCHANT_CONSUMERLANGUAGE", $langCode);
                $miObj->setParameter("DS_MERCHANT_PRODUCTDESCRIPTION", $description);
                $miObj->setParameter("DS_MERCHANT_CARDHOLDER", $booking->contact_name);
                $miObj->setParameter("DS_MERCHANT_MERCHANTNAME", $merchantName);

                $paymentParams = array(
                    'Ds_SignatureVersion' => $signatureVersion,
                    'Ds_MerchantParameters' => $miObj->createMerchantParameters(),
                    'Ds_Signature' => $miObj->createMerchantSignature($encryptionKey)
                );

                $inputs = '';
                foreach( $paymentParams as $k => $v ) {
                    if ( !empty($v) ) {
                        $inputs .= '<input type="hidden" name="'. $k .'" value="'. $v .'">';
                    }
                }

                $html = '<form target="_top" method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">'.
                            $inputs.
                            '<input type="submit" name="submit" value="'. trans('booking.page.pay.btn_pay_now') .'" class="button btn btn-primary">'.
                        '</form>';

            break;
            case 'stripe':

                // https://stripe.com/docs/checkout/tutorial
                // https://stripe.com/docs/testing#cards

                // Test details:
                // Visa Card Number: 4242424242424242
                // Expiry date: any date in future
                // Security Code: 123

                $paymentUrl = $notifyUrl;

                if ( !empty((int)$payment->params->test_mode) ) {
                    $publishableKey = $payment->params->pk_test;
                }
                else {
                    $publishableKey = $payment->params->pk_live;
                }

                if ( !empty((float)$payment->params->test_amount) ) {
                    $total = (float)$payment->params->test_amount * 100;
                }
                else {
                    $total = $total * 100;
                }

                $langCode = $config->language;

                if ( !empty($payment->params->language_code) && $payment->params->language_code != 'auto' ) {
                    $langCode = trim($payment->params->language_code);
                }

                $paymentParams = array(
                    'key' => $publishableKey,
                    'amount' => $total,
                    'name' => $config->company_name,
                    'description' => $desc .' #'. $booking->ref_number,
                    'email' => $booking->contact_email,
                    'image' => '',
                    'locale' => 'auto',
                    'label' => trans('booking.page.pay.btn_pay_now'),
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

                $html = '<div style="margin-top:20px; height:600px;">';
                  $html .= '<form method="post" action="'. $paymentUrl .'" id="paymentForm" name="paymentForm">';
                    $html .= '<button type="submit" id="payNowButton" class="button btn btn-primary">'. trans('booking.page.pay.btn_pay_now') .'</button>';
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
                    if ( typeof Stripe !== 'undefined' && typeof StripeCheckout !== 'undefined' ) {
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

            break;
        }

        // dd($transactions, $payment);


        // if ( $config->auto_payment_redirection ) {
        //     $html .= '<script>
        //       $(document).ready(function() {
        //           setTimeout(function() {
        //               $(\'#paymentForm [type="submit"]\').trigger(\'click\');
        //           }, 1000);
        //       });
        //       </script>';
        // }

        return view('booking.pay', [
            'payment' => $payment,
            'transactions' => $transactions,
            'html' => $html,
            'total' => $total,
        ]);
    }

    public function notify($id = null)
    {
        // http://localhost/ETO_v3/booking/pay/105,106/83,84,64?payment_method=paypal

        $errors = [];
        $payment = null;
        $booking = null;
        $config = null;
        $tIDs = [];
        $bIDs = [];
        $total = 0;
        $desc = '';

        $request = request();

        if ( $request->input('payment_method') == 'epdq' ) {
            $id = $request->input('orderID');
        }

        if ( $id ) {
            // $id = base64_decode($id);
            $tIDs = (array)explode(',', $id);
            foreach( $tIDs as $k => $v ) {
                if ( !$v ) { unset($tIDs[$k]); }
            }
        }

        $transactions = \App\Models\Transaction::where('relation_type', 'booking')->whereIn('id', $tIDs)->get();

        // dd($bIDs, $tIDs, $transactions);

        foreach( $transactions as $k => $transaction ) {
            if ( !$transaction->payment_method ||
                in_array($transaction->payment_method, ['cash', 'account', 'bacs', 'none']) ||
                $transaction->status == 'paid' ) {
                unset($transactions[$k]);
                continue;
            }

            if ( !$payment && $transaction->payment_id ) {
                $payment = $transaction->payment;
                if ( $payment && $payment->params ) {
                    $payment->params = json_decode($payment->params);
                }
            }

            if ( !$booking ) {
                $booking = $transaction->booking;
            }

            if ( $transaction->relation_type == 'booking' ) {
                $bIDs[] = $transaction->relation_id;
            }

            $desc .= ($desc ? ', ' : '') . ($transaction->name ? $transaction->name : 'Booking');
            $total += $transaction->amount + $transaction->payment_charge;
        }

        if ( $booking && !empty($booking->booking->site_id) ) {
            $config = \App\Models\Config::getBySiteId($booking->booking->site_id)->mapData()->getData();
        }

        if ( $transactions->isEmpty() ) {
            $errors[] = trans('booking.page.pay.errors.no_transactions');
        }
        elseif ( !$payment ) {
            $errors[] = trans('booking.page.pay.errors.no_payment');
        }
        elseif ( !$booking ) {
            $errors[] = trans('booking.page.pay.errors.no_booking');
        }
        elseif ( !$config ) {
            $errors[] = trans('booking.page.pay.errors.no_config');
        }

        if ( $errors ) {
            return view('booking.error', [
                'errors' => $errors,
            ]);
        }

        // $bIDs = implode(',', $bIDs);
        // $tIDs = implode(',', $tIDs);

        // $bookingUrl = $config->url_booking;
        // $notifyUrl = route('booking.notify', $tIDs) .'?payment_method='. $payment->method;
        // $finishUrl = route('booking.finish', $bIDs);
        $finishUrl = route('booking.finish', implode(',', $bIDs));
        // $cancelUrl = route('booking.cancel', $bIDs);


        $status = '';
        $send = 0;

        $response = "\r\n\r\n-- ". $_SERVER['REMOTE_ADDR'] ." --------\r\n";
        if ( !empty($_GET) ) {
            $response .= "\r\nGET: ". http_build_query($_GET) ."\r\n";
        }
        if ( !empty($_POST) ) {
            $response .= "\r\nPOST: ". http_build_query($_POST) ."\r\n";
        }

        switch( $payment->method ) {
            case 'epdq':

                // https://support.epdq.co.uk/en/guides/user%20guides/statuses-and-errors/statuses

                if ( $request->isMethod('post') && $request->input('SHASIGN') ) {
                    switch( $request->input('STATUS') ) {
                        case '1':
                            $status = 'canceled';
                        break;
                        case '5':
                            $status = 'authorised';
                            $send = 1;
                        break;
                        case '8':
                            $status = 'refunded';
                        break;
                        case '9':
                            $status = 'paid';
                            $send = 1;
                        break;
                    }
                }

            break;
            case 'paypal':

                // https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNIntro/#id08CKFJ00JYK
                // https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNandPDTVariables/?mark=payment_status

                if ( !empty((int)$payment->params->test_mode) ) {
                    $paymentUrl = 'https://www.paypal.com/cgi-bin/webscr?cmd=_notify-validate&'. http_build_query($_POST);
                }
                else {
                    $paymentUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_notify-validate&'. http_build_query($_POST);
                }

                if ( function_exists('file_get_contents') && !strstr(file_get_contents($paymentUrl), 'VERIFIED') ) {
                    return false;
                }

                // && in_array($_SERVER['REMOTE_ADDR'] , array('213.254.248.98', '212.35.124.164', '213.121.209.27', '217.140.33.25'))

                if ( $request->isMethod('post') ) {
                    switch( $request->input('payment_status') ) {
                        case 'Completed':
                            $status = 'paid';
                            $send = 1;
                        break;
                        case 'Pending':
                            $status = 'pending';
                        break;
                        case 'Refunded':
                            $status = 'refunded';
                        break;
                        case 'Denied':
                            $status = 'declined';
                        break;
                    }
                }

            break;
            case 'payzone':

                // https://www.payzone.co.uk/media/2257/getting-started-payzone-payment-gateway.pdf

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
                $MerchantID = trim($payment->params->merchant_id);
                $Password = trim($payment->params->password);

                $paymentParams = array(
                    'StatusCode' => $request->input('StatusCode'),
                    'Message' => $request->input('Message'),
                    'PreviousStatusCode' => $request->input('PreviousStatusCode'),
                    'PreviousMessage' => $request->input('PreviousMessage'),
                    'CrossReference' => $request->input('CrossReference'),
                    'AddressNumericCheckResult' => $request->input('AddressNumericCheckResult'),
                    'PostCodeCheckResult' => $request->input('PostCodeCheckResult'),
                    'CV2CheckResult' => $request->input('CV2CheckResult'),
                    'ThreeDSecureAuthenticationCheckResult' => $request->input('ThreeDSecureAuthenticationCheckResult'),
                    'CardType' => $request->input('CardType'),
                    'CardClass' => $request->input('CardClass'),
                    'CardIssuer' => $request->input('CardIssuer'),
                    'CardIssuerCountryCode' => $request->input('CardIssuerCountryCode'),
                    'Amount' => $request->input('Amount'),
                    'CurrencyCode' => $request->input('CurrencyCode'),
                    'OrderID' => $request->input('OrderID'),
                    'TransactionType' => $request->input('TransactionType'),
                    'TransactionDateTime' => $request->input('TransactionDateTime'),
                    'OrderDescription' => $request->input('OrderDescription'),
                    'CustomerName' => $request->input('CustomerName'),
                    'Address1' => $request->input('Address1'),
                    'Address2' => $request->input('Address2'),
                    'Address3' => $request->input('Address3'),
                    'Address4' => $request->input('Address4'),
                    'City' => $request->input('City'),
                    'State' => $request->input('State'),
                    'PostCode' => $request->input('PostCode'),
                    'CountryCode' => $request->input('CountryCode'),
                    'EmailAddress' => $request->input('EmailAddress'),
                    'PhoneNumber' => $request->input('PhoneNumber'),
                );

                if ( get_magic_quotes_gpc() ) {
                    $paymentParams = array_map('stripslashes', $paymentParams);
                }

                $hashcode = createhash($PreSharedKey, $MerchantID, $Password, $paymentParams);
                $posthashcode = $request->input('HashDigest');

                if ( $request->isMethod('post') && $hashcode == $posthashcode ) {
                    switch( $request->input('StatusCode') ) {
                        case '0':
                            $status = 'paid';
                            $send = 1;
                        break;
                        case '5':
                            $status = 'declined';
                        break;
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
                    'StatusCode' => $request->input('StatusCode'),
                    'Message' => $request->input('Message'),
                    'PreviousStatusCode' => $request->input('PreviousStatusCode'),
                    'PreviousMessage' => $request->input('PreviousMessage'),
                    'CrossReference' => $request->input('CrossReference'),
                    'Amount' => $request->input('Amount'),
                    'CurrencyCode' => $request->input('CurrencyCode'),
                    'OrderID' => $request->input('OrderID'),
                    'TransactionType' => $request->input('TransactionType'),
                    'TransactionDateTime' => $request->input('TransactionDateTime'),
                    'OrderDescription' => $request->input('OrderDescription'),
                    'CustomerName' => $request->input('CustomerName'),
                    'Address1' => $request->input('Address1'),
                    'Address2' => $request->input('Address2'),
                    'Address3' => $request->input('Address3'),
                    'Address4' => $request->input('Address4'),
                    'City' => $request->input('City'),
                    'State' => $request->input('State'),
                    'PostCode' => $request->input('PostCode'),
                    'CountryCode' => $request->input('CountryCode'),
                );

                // magic quotes fix
                if ( get_magic_quotes_gpc() ) {
                    $paymentParams = array_map('stripslashes', $paymentParams);
                }

                $hashcode = createhash($PreSharedKey, $MerchantID, $Password, $paymentParams);
                $posthashcode = $request->input('HashDigest');

                if ( $request->isMethod('post') && $hashcode == $posthashcode ) {
                    switch( $request->input('StatusCode') ) {
                        case '0':
                            $status = 'paid';
                            $send = 1;
                        break;
                        case '5':
                            $status = 'declined';
                        break;
                    }
                }

            break;
            case 'worldpay':

                // http://support.worldpay.com/support/kb/bg/paymentresponse/pr5304.html

                if ( $request->isMethod('post') ) {
                    switch( $request->input('transStatus') ) {
                        case 'Y':
                            $status = 'paid';
                            $send = 1;
                        break;
                        case 'C':
                            $status = 'canceled';
                        break;
                    }
                }

            break;
            case 'redsys':

                if ( $request->isMethod('post') ) {
                    $Ds_SignatureVersion = $request->input('Ds_SignatureVersion');
                    $Ds_MerchantParameters = $request->input('Ds_MerchantParameters');
                    $Ds_Signature = $request->input('Ds_Signature');

                    include(base_path('vendor/easytaxioffice/apiRedsys.php'));

                    $miObj = new \RedsysAPI;
                    $encryptionKey = trim($payment->params->encryption_key);
                    $merchantID = trim($payment->params->merchant_id);
                    $decodec = $miObj->decodeMerchantParameters($Ds_MerchantParameters);
                    $firma = $miObj->createMerchantSignatureNotif ($encryptionKey, $Ds_MerchantParameters);

                    $total      = $miObj->getParameter('Ds_Amount');
                    $pedido     = $miObj->getParameter('Ds_Order');
                    $codigo     = $miObj->getParameter('Ds_MerchantCode');
                    $moneda     = $miObj->getParameter('Ds_Currency');
                    $respuesta  = $miObj->getParameter('Ds_Response');
                    $id_trans   = $miObj->getParameter('Ds_AuthorisationCode');
                    $fecha= $miObj->getParameter('Ds_Date');
                    $hora= $miObj->getParameter('Ds_Hour');
                    $respuesta = intval($respuesta);

                    if ( !empty($respuesta) ) {
                        $response .= "\r\nResponse: ". $respuesta ."\r\n";
                    }

                    if ( $firma === $Ds_Signature && $respuesta < 101 && $merchantID == $codigo ) {
                        $status = 'paid';
                        $send = 1;
                    }
                }
            break;
            case 'stripe':

                if ( $request->isMethod('post') ) {
                    $stripeSource = $request->input('stripeSource');
                    $stripeToken = $request->input('stripeToken');
                    $stripeTokenType = $request->input('stripeTokenType');
                    $stripeEmail = $request->input('stripeEmail');

                    if ( !empty((float)$payment->params->test_amount) ) {
                        $total = (float)$payment->params->test_amount * 100;
                    }
                    else {
                        $total = $total * 100;
                    }

                    if ( !empty((int)$payment->params->test_mode) ) {
                        $secretKey = $payment->params->sk_test;
                    }
                    else {
                        $secretKey = $payment->params->sk_live;
                    }

                    if ( $request->isSecure() ) {
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
                        'description' => $booking->contact_name,
                        'source' => $source,
                    ));

                    $chargeStatus = '';

                    try {
                        $charge = \Stripe\Charge::create(array(
                            'amount' => $total,
                            'currency' => ($payment->params->currency_code) ? trim($payment->params->currency_code) : 'GBP',
                            'description' => trim($desc .' #'. $booking->ref_number),
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

                    if ( !empty($charge) ) {
                        $response .= "\r\nResponse: ". $charge ."\r\n";
                    }

                    switch( $chargeStatus ) {
                        case 'paid':
                            $status = 'paid';
                            $send = 1;
                        break;
                        case 'declined':
                            $status = 'declined';
                        break;
                    }
                }

            break;
        }

        foreach( $transactions as $k => $transaction ) {
            $transaction->ip = $_SERVER['REMOTE_ADDR'];
            $transaction->updated_at = \Carbon\Carbon::now();

            if ( $status ) {
                $transaction->status = $status;
            }

            if ( 1 ) {
                $transaction->response = trim($transaction->response . $response);
            }

            $transaction->save();
        }

        // dd($status);

        // Update status
        if ( $status == 'paid' ) {
            \App\Models\BookingRoute::whereIn('id', $bIDs)
                ->where('status', '=', 'incomplete')
                ->update(['status' => 'pending']);
        }


        if ( $payment->method == 'payzone' ) {
            echo "StatusCode=". $request->input('StatusCode') ."&Message=". $request->input('Message');
            die;
        }
        elseif ( $payment->method == 'cardsave' ) {
            echo "StatusCode=". $request->input('StatusCode') ."&Message=". $request->input('Message');
            die;
        }
        elseif ( $payment->method == 'stripe' ) {
            return redirect($finishUrl);
          // dd($finishUrl);
            // header("Location: ". url('/booking') .'?finishType=paymentThankYou&bID='. $qBooking->unique_key .'&tID='. $transaction->unique_key);
            // header("Location: ". $finishUrl);
            // die;
        }

        ////////
    }

    public function booking()
    {
        // echo '<pre>'; print_r( session()->all() ); echo '</pre>';
        // echo '<pre>'; print_r( config('site') ); echo '</pre>';

        // if ( config('site.embed') && !empty(config('site.url_booking')) ) {
            // echo request()->cookie('eto_redirect_booking_url');
            // echo \Cookie::get('eto_redirect_booking_url');
            // echo cookie('eto_redirect_booking_url');
            // redirect(config('site.url_booking'));
            // return redirect()->away( config('site.url_booking') );
        // }
        return view('booking.booking');
    }

    public function widget()
    {
        return view('booking.widget');
    }

    public function availability($id)
    {
        $vehicle = \App\Models\VehicleType::findOrFail($id);

        // Requested date
        $rsd = Carbon::now()->timestamp(request()->get('rstart'));
        $red = Carbon::now()->timestamp(request()->get('rend'));

        // Travel date
        $tsd = Carbon::now()->timestamp(request()->get('tstart'));
        $ted = Carbon::now()->timestamp(request()->get('tend'));

        $defaultDate = $rsd->toDateString();

        if ( request()->ajax() )
        {
            $vsdUTC = Carbon::now('UTC')->timestamp(request()->get('viewStart'));
            $vedUTC = Carbon::now('UTC')->timestamp(request()->get('viewEnd'));

            $vsd = Carbon::parse($vsdUTC->toDateTimeString());
            $ved = Carbon::parse($vedUTC->toDateTimeString());

            $eventsList = [];

            // Events
            $events = \App\Models\Event::where('relation_type', '=', 'user')
                ->where('relation_id', '=', $vehicle->user_id)
                ->orderBy('ordering', 'asc');

            //     $events->where('start_at', '>=', $vsd);
            //     $events->where('end_at', '<=', $ved);

            // Add service types filter

            foreach ($events->get() as $event) {
                $event->repeat_days = !empty($event->repeat_days) ? json_decode($event->repeat_days) : [$event->start_at->dayOfWeek];
                $event->repeat_interval = !empty($event->repeat_interval) ? $event->repeat_interval : 1;

                if ($event->repeat_type == 'none') {
                    $sd = $event->start_at->copy();
                    $ed = $event->end_at->copy();

                    $eventsList[] = [
                        'event_type' => 'event',
                        'id' => $event->id,
                        'title' => $event->name,
                        'ordering' => $event->ordering,
                        'start' => $sd->toDateTimeString(),
                        'end' => $ed->toDateTimeString(),
                        'rendering' => 'background',
                        'className' => ($event->status == 'active') ? 'fc-event_booking_active' : 'fc-event_booking_inactive'
                    ];

                    continue;
                }

                if ($event->start_at->lt($vsd)) {
                    $vsd->subSeconds($event->start_at->diffInSeconds($vsd));
                }

                if ($event->end_at->gt($ved)) {
                    $ved->addSeconds($event->end_at->diffInSeconds($ved));
                }

                for ($i = 0; $i <= $vsd->diffInDays($ved); $i++) {
                    $sd = $vsd->copy()->addDays($i)->setTime($event->start_at->hour, $event->start_at->minute, $event->start_at->second);
                    $ed = $sd->copy()->addSeconds($event->start_at->diffInSeconds($event->end_at));

                    $skip = 0;
                    $diff = 0;
                    $diffSpan = 0;
                    $time = 0;

                    switch ($event->repeat_type) {
                        case 'daily':
                            $diff = $sd->diffInDays($event->start_at);
                            $diffSpan = $event->start_at->diffInDays($event->end_at);
                            $time = $sd->copy()->subDays($diff)->diffInSeconds($event->start_at);
                        break;
                        case 'weekly':
                            $diff = $sd->diffInDays($event->start_at);
                            $diffSpan = $event->start_at->diffInDays($event->end_at);
                            $time = $sd->copy()->subDays($diff)->diffInSeconds($event->start_at);

                            $repeat_days = $event->repeat_days;
                            if ($event->start_at->diffInDays($event->end_at) > 0) {
                                for ($j = 0; $j <= $event->start_at->diffInDays($event->end_at); $j++) {
                                    $repeat_days[] = (string)$sd->copy()->addDays($j)->dayOfWeek;
                                }
                            }
                            $event->repeat_days = $repeat_days;

                            if ( !in_array($sd->dayOfWeek, $event->repeat_days) ) {
                                $skip = 1;
                            }
                        break;
                        case 'monthly':
                            $diff = $sd->diffInMonths($event->start_at);
                            $diffSpan = $event->start_at->diffInMonths($event->end_at);
                            $time = $sd->copy()->subMonths($diff)->diffInSeconds($event->start_at);
                        break;
                        case 'yearly':
                            $diff = $sd->diffInYears($event->start_at);
                            $diffSpan = $event->start_at->diffInYears($event->end_at);
                            $time = $sd->copy()->subYears($diff)->diffInSeconds($event->start_at);
                        break;
                        default:
                            $diff = $sd->diffInDays($event->start_at);
                            $diffSpan = $event->start_at->diffInDays($event->end_at);
                            $time = $sd->copy()->subDays($diff)->diffInSeconds($event->start_at);

                            $event->repeat_interval = 1;
                            $event->repeat_limit = 1;
                        break;
                    }

                    // The same day of week
                    if ( $time != 0 ) {
                        $skip = 1;
                    }

                    // Lower then start date
                    if ( $sd->lt($event->start_at) ) {
                        $skip = 1;
                    }

                    // Repeat end date
                    if ( !empty($event->repeat_end) && $ed->gte($event->repeat_end) ) {
                        $skip = 1;
                    }

                    // Repeat interval
                    if ( !empty($event->repeat_interval) && ($diff % $event->repeat_interval) - $diffSpan > 0 ) {
                        $skip = 1;
                    }

                    // Repeat limit
                    if ( !empty($event->repeat_limit) && $diff >= ($event->repeat_limit * $event->repeat_interval) ) {
                        $skip = 1;
                    }

                    if ( $skip ) {
                        continue;
                    }

                    $eventsList[] = [
                        'event_type' => 'event',
                        'id' => $event->id,
                        'title' => $event->name,
                        'ordering' => $event->ordering,
                        'start' => $sd->toDateTimeString(),
                        'end' => $ed->toDateTimeString(),
                        'rendering' => 'background',
                        'className' => ($event->status == 'active') ? 'fc-event_booking_active' : 'fc-event_booking_inactive'
                    ];
                }
            }

            // Bookings
            $extraTime = 0;

            $bookings = \App\Models\BookingRoute::whereRaw("
                            DATE_ADD(`date`, INTERVAL (`duration` + (`service_duration` * 60) + `duration_base_end`) MINUTE) >= '". $vsd->toDateTimeString() ."'
                                AND
                            DATE_SUB(`date`, INTERVAL `duration_base_start` MINUTE) <= '". $ved->toDateTimeString() ."'
                        ")
                        ->whereNotIn('status', ['quote', 'canceled'])
                        ->where('vehicle', 'regexp', '\"id\":\"'. $vehicle->id .'\",\"amount\":\"[1-9]\"');

            foreach ($bookings->get() as $booking) {
                $duration = $booking->duration + ($booking->service_duration * 60);

                $bd = Carbon::parse($booking->getOriginal('date'));

                $bsd = $bd->copy();
                $bed = $bd->copy()->addMinutes($duration);

                $sd = $bsd->copy()->subMinutes($booking->duration_base_start + ($extraTime / 2));
                $ed = $bed->copy()->addMinutes($booking->duration_base_end + ($extraTime / 2));

                $elapsed = SiteHelper::displayElapsedTime(Carbon::now()->addSeconds($sd->diffInSeconds($ed)));

                $title = trans('booking.calendar.booked');
                // if ( !empty(trim($elapsed)) ) {
                //     $title .= ' ('. trim($elapsed) .')';
                // }

                $eventsList[] = [
                    'title' => $title,
                    'ordering' => 0,
                    'start' => $sd->toDateTimeString(),
                    'end' => $ed->toDateTimeString(),
                    'rendering' => 'background',
                    'className' => 'fc-event_booking_booked'
                ];
            }

            // Time needed for driver (Base to Start)
            $sd = $rsd->copy();
            $ed = $tsd->copy();
            $elapsed = SiteHelper::displayElapsedTime(Carbon::now()->addSeconds($sd->diffInSeconds($ed)));
            $title = trans('booking.calendar.base_to_start');
            if ( !empty(trim($elapsed)) ) {
                $title .= ' ('. trim($elapsed) .')';
            }

            $eventsList[] = [
                'title' => $title,
                'ordering' => 0,
                'start' => $sd->toDateTimeString(),
                'end' => $ed->toDateTimeString(),
                'className' => 'fc-event_booking_base_to_start',
            ];

            // Time needed for booking (Start to End)
            $sd = $tsd->copy();
            $ed = $ted->copy();
            $elapsed = SiteHelper::displayElapsedTime(Carbon::now()->addSeconds($sd->diffInSeconds($ed)));
            $title = trans('booking.calendar.start_to_end');
            if ( !empty(trim($elapsed)) ) {
                $title .= ' ('. trim($elapsed) .')';
            }

            $eventsList[] = [
                'title' => $title,
                'ordering' => 0,
                'start' => $sd->toDateTimeString(),
                'end' => $ed->toDateTimeString(),
                'className' => 'fc-event_booking_start_to_end',
            ];

            // Time needed for driver (End to Base)
            $sd = $ted->copy();
            $ed = $red->copy();
            $elapsed = SiteHelper::displayElapsedTime(Carbon::now()->addSeconds($sd->diffInSeconds($ed)));
            $title = trans('booking.calendar.end_to_base');
            if ( !empty(trim($elapsed)) ) {
                $title .= ' ('. trim($elapsed) .')';
            }

            $eventsList[] = [
                'title' => $title,
                'ordering' => 0,
                'start' => $sd->toDateTimeString(),
                'end' => $ed->toDateTimeString(),
                'className' => 'fc-event_booking_end_to_base',
            ];

            return $eventsList;
        }

        return view('booking.availability', [
            'url' => route('booking.availability', $vehicle->id) .'?rstart='. $rsd->timestamp .'&rend='. $red->timestamp .'&tstart='. $tsd->timestamp .'&tend='. $ted->timestamp,
            'locale' => app()->getLocale(),
            'defaultDate' => $defaultDate
        ]);
    }

    public function terms()
    {
        $terms = config('site.terms_text');
        $terms = SiteHelper::translate($terms);
        $terms = ltrim($terms);
        $terms = SiteHelper::nl2br2($terms);

        $view = view('booking.terms', [
            'html' => $terms,
        ]);

        if ( request('action') == 'download' ) {
            $filename = trans('booking.page.terms.page_title') .'.pdf';
            $html = $view->render();

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

            return $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
        }
        else {
            return $view;
        }
    }

    public function details($slug) {
        if (config('site.customer_attach_booking_details_access_link')) {
            $params = \App\Models\BookingParam::where('key', 'access_uuid')->where('value', $slug)->first();

            if (!empty($params->value)) {
                $booking = \App\Models\BookingRoute::find($params->booking_id);

                if ($booking && !empty($booking->booking->site_id)) {
                    $config = \App\Models\Config::getBySiteId($booking->booking->site_id)->mapData()->getData();
                }

                $allow = true;
                if (config('site.customer_attach_booking_details_access_link_auto_lock')) {
                    $allow = $booking->date >= \Carbon\Carbon::now()->subHours(config('site.customer_attach_booking_details_access_link_auto_lock'));
                }

                $driver = $booking->assignedDriver();
                $vehicle = $booking->assignedVehicle();

                if (!empty($booking->id) && $allow) {
                    return view('booking.details', [
                        'booking' => $booking,
                        'driver' => $driver,
                        'vehicle' => $vehicle,
                    ]);
                }
            }
        }

        return view('errors.general', ['title' => trans('booking.tracking.no_data')]);
    }
}
