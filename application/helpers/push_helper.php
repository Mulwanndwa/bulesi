<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Send an FCM v1 push notification to one or more device tokens.
 *
 * Uses the service account credentials in config/push.php to obtain a
 * short-lived OAuth2 access token, then sends one FCM v1 request per token.
 *
 * @param  string|array  $tokens   Single token or array of device tokens.
 * @param  string        $title
 * @param  string        $body
 * @param  array         $data     Optional key/value payload (values must be strings).
 * @return bool  TRUE if at least one message was dispatched without a cURL error.
 */
function send_push($tokens, $title, $body, array $data = [])
{
    $CI = &get_instance();

    $project_id   = $CI->config->item('fcm_project_id');
    $client_email = $CI->config->item('fcm_client_email');
    $private_key  = $CI->config->item('fcm_private_key');

    if (!$project_id || !$client_email || !$private_key) {
        log_message('error', 'push_helper: FCM credentials not configured.');
        return FALSE;
    }

    $tokens = array_values(array_filter((array)$tokens));
    if (empty($tokens)) {
        return FALSE;
    }

    $access_token = _fcm_access_token($client_email, $private_key);
    if (!$access_token) {
        log_message('error', 'push_helper: Failed to obtain FCM access token.');
        return FALSE;
    }

    // FCM v1 requires all data values to be strings
    $string_data = array_map('strval', $data);

    $url     = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    ];

    $ok = FALSE;
    foreach ($tokens as $token) {
        $payload = json_encode([
            'message' => [
                'token'        => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'android' => [
                    'notification' => ['sound' => 'default'],
                    'priority'     => 'high',
                ],
                'apns' => [
                    'payload' => ['aps' => ['sound' => 'default']],
                ],
                'data' => $string_data,
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'push_helper: cURL error — ' . $error);
        } else {
            log_message('info', 'push_helper: FCM v1 response — ' . $response);
            $ok = TRUE;
        }
    }

    return $ok;
}

/**
 * Exchange the service account private key for a short-lived OAuth2 access token.
 */
function _fcm_access_token($client_email, $private_key)
{
    $now = time();

    $header  = _fcm_b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $payload = _fcm_b64url(json_encode([
        'iss'   => $client_email,
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'iat'   => $now,
        'exp'   => $now + 3600,
    ]));

    $signing_input = $header . '.' . $payload;

    $pkey = openssl_pkey_get_private($private_key);
    if (!$pkey) {
        log_message('error', 'push_helper: Could not load FCM private key.');
        return NULL;
    }

    openssl_sign($signing_input, $signature, $pkey, 'SHA256');
    $jwt = $signing_input . '.' . _fcm_b64url($signature);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST           => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]),
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        log_message('error', 'push_helper: Token exchange cURL error — ' . $error);
        return NULL;
    }

    $decoded = json_decode($response, TRUE);
    return $decoded['access_token'] ?? NULL;
}

function _fcm_b64url($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
