<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('yevhenb_mono_cron', function () {

    $settings = get_option(
        'woocommerce_smpl_mono_settings'
    );

    $token = $settings['token'] ?? '';

    if (empty($token)) {
        return;
    }

    $orders = wc_get_orders([
        'status'   => 'pending',
        'limit'    => 20,
        'meta_key' => '_mono_invoice_id'
    ]);

    foreach ($orders as $order) {

        $invoice = get_post_meta(
            $order->get_id(),
            '_mono_invoice_id',
            true
        );

        if (!$invoice) {
            continue;
        }

        $response = wp_remote_get(
            'https://api.monobank.ua/api/merchant/invoice/status?invoiceId=' . rawurlencode($invoice),
            [
                'headers' => [
                    'X-Token' => $token
                ]
            ]
        );

        if (is_wp_error($response)) {
            continue;
        }

        $body = json_decode(
            wp_remote_retrieve_body($response),
            true
        );

        if (!empty($body['status'])) {

            $status = sanitize_text_field(
                $body['status']
            );

            update_post_meta(
                $order->get_id(),
                '_mono_status',
                $status
            );

            update_post_meta(
                $order->get_id(),
                '_mono_updated',
                current_time('mysql')
            );

            if (
                in_array($status, ['success', 'processing', 'hold'], true)
                && !$order->is_paid()
            ) {

                $order->payment_complete();

            }

        }

    }

});

add_filter('cron_schedules', function ($schedules) {

    $schedules['yevhenb_mono_five_min'] = [
        'interval' => 300,
        'display'  => __('Every 5 minutes', 'yevhenb-payments-with-mono-for-woocommerce')
    ];

    return $schedules;

});

if (!wp_next_scheduled('yevhenb_mono_cron')) {

    wp_schedule_event(
        time(),
        'yevhenb_mono_five_min',
        'yevhenb_mono_cron'
    );

}