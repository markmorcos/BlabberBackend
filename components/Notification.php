<?php
 
namespace app\components;

class Notification {
 
    /**
     * @inheritdoc
     */
    public static function sendNotification($firebase_token, $title, $body, $data = null)
    {
        if (isset($data) && isset($data['payload'])) {
            $data = array_merge($data, $data['payload']);
            unset($data['payload']);
        }

        $server_key = 'AAAAqGzljtM:APA91bGRz5hiS-IyHW6HPnK-yrIJRFkzqP85PzByvWlI0YYCfLF_NH94Rybgg31bDs2d0EfxzD_zYmb4fNwSH1x6HOXFY_a-solzKgn7xiSi336sUYQjrXZuCWrk29ioaHBZLL7p0LfO';
        $postData = [
            'to' => $firebase_token,
            'priority' => 'high',
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
        ];

        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Key='.$server_key,
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($postData)
        ));

        $response = curl_exec($ch);
        if($response === FALSE){
            echo curl_error($ch);
        }

        // var_dump($firebase_token, $title, $body, $data, $response); //TODO: remove in production
        return json_decode($response, TRUE);
    }
}