<?php

add_action('smpl_mono_cron', function () {

    $orders = wc_get_orders([
        'status' => 'pending',
        'limit' => 20,
        'meta_key' => '_mono_invoice_id'
    ]);

    $gateway = new WC_SMPL_Mono_Gateway();

    foreach ($orders as $order) {

        $invoice = get_post_meta($order->get_id(), '_mono_invoice_id', true);
        if (!$invoice) continue;

        $response = wp_remote_get(
            'https://api.monobank.ua/api/merchant/invoice/status?invoiceId=' . $invoice,
            [
                'headers' => [
                    'X-Token' => $gateway->token
                ]
            ]
        );

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['status'])) {

            update_post_meta($order->get_id(), '_mono_status', $body['status']);

            if ($body['status'] === 'success' && !$order->is_paid()) {
                $order->payment_complete();
            }
        }
    }
});

add_filter('cron_schedules', function ($schedules) {
    $schedules['five_min'] = [
        'interval' => 300,
        'display' => 'Every 5 minutes'
    ];
    return $schedules;
});

if (!wp_next_scheduled('smpl_mono_cron')) {
    wp_schedule_event(time(), 'five_min', 'smpl_mono_cron');
}