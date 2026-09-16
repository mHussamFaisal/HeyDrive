<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class SmsChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // http://guzzle.readthedocs.io/en/latest/quickstart.html
        // https://www.textlocal.com/integrations/api/
        // http://api.txtlocal.com/docs/sendsms
        // http://smsgateway.me/sms-api-documentation/messages/sending-a-sms-message

        $response = false;

        switch (config('services.sms_service_type')) {
            case 'textlocal':

                $data = $notification->toSMS($notifiable);

                $params = [
                    'apiKey' => config('services.textlocal.key'),
                    'test' => config('services.textlocal.test') ? true : false,
                    'sender' => substr(config('app.name'), 0, 11),
                    'numbers' => '',
                    'message' => '',
                ];

                if (!empty($data)) {
                    foreach($data as $key => $value) {
                        switch( $key ) {
                            case 'sender':
                                $value = urlencode($value);
                            break;
                            case 'numbers':
                                $value = implode(',', $value);
                            break;
                            case 'message':
                                $value = rawurlencode($value);
                            break;
                        }
                        $params[$key] = $value;
                    }
                }

                if (empty($params['apiKey']) ||
                    empty($params['sender']) ||
                    empty($params['numbers']) ||
                    empty($params['message'])) {
                    return false;
                }

                try {
                    $client = new \GuzzleHttp\Client();

                    $response = $client->request('POST', 'https://api.txtlocal.com/send/', [
                        'form_params' => $params
                    ]);

                    $response = json_decode($response->getBody());

                    if ($response->status == 'failure' && $response->errors) {
                        $errors = [];
                        foreach ($response->errors as $k => $v) {
                            $errors[] = $v->message .' (Code '. $v->code .')';
                        }
                        \Log::error('SMS notification error (Textlocal): '. implode(', ', $errors));
                    }
                }
                catch (\Exception $e) {
                    \Log::error('SMS notification error (Textlocal): '. $e->getMessage());
                }

            break;
            case 'twilio':

                // https://www.twilio.com/console
                // https://www.twilio.com/docs/iam/test-credentials
                // https://www.twilio.com/docs/sms/api/message-resource
                // https://www.twilio.com/docs/sms/send-messages

                $data = $notification->toSMS($notifiable);

                $params = [
                    'sid' => config('services.twilio.sid'),
                    'token' => config('services.twilio.token'),
                    'from' => config('services.twilio.phone_number'),
                    'sender' => substr(config('app.name'), 0, 11),
                    'numbers' => '',
                    'message' => '',
                ];

                if (!empty($data)) {
                    foreach($data as $key => $value) {
                        switch( $key ) {
                            case 'sender':
                                $value = urlencode($value);
                            break;
                            case 'numbers':
                                $value = implode(',', $value);
                            break;
                            // case 'message':
                            //     $value = rawurlencode($value);
                            // break;
                        }
                        $params[$key] = $value;
                    }
                }

                if (empty($params['sid']) ||
                    empty($params['token']) ||
                    empty($params['sender']) ||
                    empty($params['numbers']) ||
                    empty($params['message'])) {
                    return false;
                }

                try {
                    $client = new \GuzzleHttp\Client();

                    $response = $client->request('POST', 'https://api.twilio.com/2010-04-01/Accounts/'. $params['sid'] .'/Messages.json', [
                        'form_params' => [
                          'To' => $params['numbers'],
                          'From' => $params['from'],
                          'Body' => $params['message']
                        ],
                        'auth' => [$params['sid'], $params['token']]
                    ]);

                    $response = json_decode($response->getBody());

                    // $twilio = new \Twilio\Rest\Client($params['sid'], $params['token']);
                    // $response = $twilio->messages->create($params['numbers'], [
                    //     'from' => $params['from'],
                    //     'body' => $params['message']
                    // ]);

                    // \Log::info([
                    //     'sid' => $response->sid,
                    //     'status' => $response->status,
                    //     'to' => $response->to,
                    //     'from' => $response->from,
                    //     'body' => $response->body,
                    // ]);

                    if (in_array($response->status, ['failed', 'undelivered']) && $response->error_message) {
                        \Log::error('SMS notification error (Twilio): '. $response->error_message .' (Code '. $v->error_code .')');
                    }
                }
                catch (\Exception $e) {
                    \Log::error('SMS notification error (Twilio): '. $e->getMessage());
                }

            break;
            case 'smsgateway':

                $data = $notification->toSMS($notifiable);

                $params = [
                    'apiKey' => config('services.smsgateway.key'),
                    'deviceId' => config('services.smsgateway.device_id'),
                    'numbers' => [],
                    'message' => '',
                ];

                if (!empty($data)) {
                    foreach($data as $key => $value) {
                        $params[$key] = $value;
                    }
                }

                if (empty($params['apiKey']) ||
                    empty($params['deviceId']) ||
                    empty($params['numbers']) ||
                    empty($params['message'])) {
                    return false;
                }

                try {
                    require_once(base_path('vendor/easytaxioffice/smsgatewayme/autoload.php'));

                    $config = \SMSGatewayMe\Client\Configuration::getDefaultConfiguration();
                    $config->setApiKey('Authorization', config('services.smsgateway.key'));
                    $client = new \SMSGatewayMe\Client\ApiClient($config);
                    $messageApi = new \SMSGatewayMe\Client\Api\MessageApi($client);
                    $messages = [];

                    foreach($params['numbers'] as $number) {
                        $messages[] = new \SMSGatewayMe\Client\Model\SendMessageRequest([
                            'phoneNumber' => trim($number),
                            'message' => $params['message'],
                            'deviceId' => $params['deviceId']
                        ]);
                    }

                    $response = $messageApi->sendMessages($messages);
                }
                catch (\Exception $e) {
                    \Log::error('SMS notification error (SMSGateway): '. $e->getMessage());
                }

            break;
        }

        // \Log::info([$params, (array)$response]);
        // dd($params, $response);
        // echo'<pre>'; print_r($response); echo'</pre>';

        return $response;
    }
}
