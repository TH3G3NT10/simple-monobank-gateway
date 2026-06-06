<?php

add_action('init', function () {

    if (
        trim($_SERVER['REQUEST_URI'], '/') !== 'smpl-mono-webhook'
    ) {
        return;
    }

    $body = file_get_contents('php://input');

    $x_sign = $_SERVER['HTTP_X_SIGN'] ?? '';

    if (empty($x_sign)) {

        status_header(403);
        exit;

    }

    $settings = get_option(
        'woocommerce_smpl_mono_settings'
    );

    $token = $settings['token'] ?? '';

    if (empty($token)) {

        status_header(500);
        exit;

    }

    $response = wp_remote_get(
        'https://api.monobank.ua/api/merchant/pubkey',
        [
            'headers' => [
                'X-Token' => $token
            ]
        ]
    );

    if (is_wp_error($response)) {

        status_header(500);
        exit;

    }

    $pubkey = json_decode(
        wp_remote_retrieve_body($response),
        true
    );

    if (empty($pubkey['key'])) {

        status_header(500);
        exit;

    }

    $public_key = base64_decode(
        $pubkey['key']
    );

    $signature = base64_decode(
        $x_sign
    );

    $verified = openssl_verify(
        $body,
        $signature,
        $public_key,
        OPENSSL_ALGO_SHA256
    );

    if ($verified !== 1) {

        status_header(403);
        exit;

    }

    $data = json_decode(
        $body,
        true
    );

    if (empty($data)) {

        status_header(200);
        exit;

    }

    $order_id = absint(
        $data['reference'] ?? 0
    );

    $invoice_id = sanitize_text_field(
        $data['invoiceId'] ?? ''
    );

    $status = sanitize_text_field(
        $data['status'] ?? ''
    );

    if (!$order_id) {

        status_header(200);
        exit;

    }

    $order = wc_get_order($order_id);

    if (!$order) {

        status_header(200);
        exit;

    }

    if (!empty($invoice_id)) {

        update_post_meta(
            $order_id,
            '_mono_invoice_id',
            $invoice_id
        );

    }

    update_post_meta(
        $order_id,
        '_mono_status',
        $status
    );

    update_post_meta(
        $order_id,
        '_mono_updated',
        current_time('mysql')
    );

    if (
        in_array($status, ['success', 'processing', 'hold'], true)
        && !$order->is_paid()
    ) {

        $order->payment_complete();

    }

    status_header(200);
    exit;

});