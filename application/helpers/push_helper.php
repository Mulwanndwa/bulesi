<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Send an FCM push notification to one or more device tokens.
 *
 * @param  string|array  $tokens   Single token or array of tokens.
 * @param  string        $title
 * @param  string        $body
 * @param  array         $data     Optional key/value payload.
 * @return bool  TRUE if the request was sent (regardless of delivery status).
 */
function send_push($tokens, $title, $body, array $data = [])
{
    $CI  =& get_instance();
    $key = $CI->config->item('fcm_server_key');

    if (!$key) {
        log_message('error', 'push_helper: fcm_server_key is not configured');
        return FALSE;
    }

    $tokens = array_values(array_filter((array)$tokens));
    if (empty($tokens)) {
        return FALSE;
    }

    $payload = [
        'registration_ids' => $tokens,
        'notification'     => [
            'title' => $title,
            'body'  => $body,
            'sound' => 'default',
        ],
        'data' => $data,
    ];

    $ch = curl_init('https://fcm.googleapis.com/fcm/send');
    curl_setopt_array($ch, [
        CURLOPT_POST           => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER     => [
            'Authorization: key=' . $key,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT    => 10,
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        log_message('error', 'push_helper: cURL error — ' . $error);
        return FALSE;
    }

    log_message('info', 'push_helper: FCM response — ' . $response);
    return TRUE;
}
