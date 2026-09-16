<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class ExpoPushChannel
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
        // https://docs.expo.io/versions/latest/guides/push-notifications.html
        // http://guzzle.readthedocs.io/en/latest/quickstart.html

        $response = false;
        $data = $notification->toExpoPush($notifiable);

        $params = [
            'to' => isset($notifiable->push_token) ? $notifiable->push_token : '',
            'title' => '',
            'body' => '',
            'sound' => 'default',
            'channelId' => 'notifications',
        ];

        if (!empty($data)) {
            foreach($data as $key => $value) {
                $params[$key] = $value;
            }
        }

        if (empty($params['to']) ||
            empty($params['title']) ||
            empty($params['body'])) {
            return false;
        }

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request('POST', 'https://exp.host/--/api/v2/push/send', [
                'headers' => [
                    'accept' => 'application/json',
                    'accept-encoding' => 'gzip, deflate',
                    'content-type' => 'application/json'
                ],
                'body' => json_encode($params)
            ]);

            $response = json_decode($response->getBody());
        }
        catch (\Exception $e) {
            \Log::error('Push notification error (ExpoPush): '. $e->getMessage());
        }

        // \Log::info([$params, (array)$response]);
        // dd($params, $response);
        // echo'<pre>'; print_r($response); echo'</pre>';

        return $response;
    }
}
