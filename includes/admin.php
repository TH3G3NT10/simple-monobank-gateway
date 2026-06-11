<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function () {

    add_menu_page(
        __('YevhenB Payments', 'yevhenb-payments-with-mono-for-woocommerce'),
        __('YevhenB Payments', 'yevhenb-payments-with-mono-for-woocommerce'),
        'manage_woocommerce',
        'yevhenb-mono-payments',
        'yevhenb_mono_admin_page',
        'dashicons-money'
    );

});

add_action('admin_init', function () {

    $page = isset($_GET['page'])
        ? sanitize_text_field(wp_unslash($_GET['page']))
        : '';

    $check_order = isset($_GET['yevhenb_check_order'])
        ? absint(wp_unslash($_GET['yevhenb_check_order']))
        : 0;

    if (
        $page !== 'yevhenb-mono-payments' ||
        !$check_order
    ) {
        return;
    }

    if (!current_user_can('manage_woocommerce')) {
        wp_die(
            esc_html__('You do not have permission to perform this action.', 'yevhenb-payments-with-mono-for-woocommerce')
        );
    }

    $order_id = $check_order;

    check_admin_referer(
        'yevhenb_mono_check_order_' . $order_id
    );

    $order = wc_get_order($order_id);

    if (!$order) {
        wp_safe_redirect(admin_url('admin.php?page=yevhenb-mono-payments'));
        exit;
    }

    $invoice = get_post_meta(
        $order_id,
        '_mono_invoice_id',
        true
    );

    if (!$invoice) {
        wp_safe_redirect(admin_url('admin.php?page=yevhenb-mono-payments'));
        exit;
    }

    $settings = get_option(
        'woocommerce_smpl_mono_settings'
    );

    $token = $settings['token'] ?? '';

    if (!$token) {
        wp_safe_redirect(admin_url('admin.php?page=yevhenb-mono-payments'));
        exit;
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
        wp_safe_redirect(admin_url('admin.php?page=yevhenb-mono-payments'));
        exit;
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

    }

    wp_safe_redirect(admin_url('admin.php?page=yevhenb-mono-payments'));
    exit;

});

function yevhenb_mono_get_status_badge($status) {

    $status = strtolower(trim($status));

    $statuses = [

        'success' => [
            'color' => '#46b450',
            'label' => __('Success', 'yevhenb-payments-with-mono-for-woocommerce')
        ],

        'processing' => [
            'color' => '#00a0d2',
            'label' => __('Processing', 'yevhenb-payments-with-mono-for-woocommerce')
        ],

        'pending' => [
            'color' => '#ffb900',
            'label' => __('Pending', 'yevhenb-payments-with-mono-for-woocommerce')
        ],

        'created' => [
            'color' => '#ffb900',
            'label' => __('Created', 'yevhenb-payments-with-mono-for-woocommerce')
        ],

        'failure' => [
            'color' => '#dc3232',
            'label' => __('Failed', 'yevhenb-payments-with-mono-for-woocommerce')
        ],

        'expired' => [
            'color' => '#777',
            'label' => __('Expired', 'yevhenb-payments-with-mono-for-woocommerce')
        ]

    ];

    if (!isset($statuses[$status])) {

        return '<span style="
            background:#777;
            color:#fff;
            padding:4px 8px;
            border-radius:20px;
            font-size:12px;
        ">' .
        esc_html__('Unknown', 'yevhenb-payments-with-mono-for-woocommerce') .
        '</span>';

    }

    return '<span style="
        background:' . esc_attr($statuses[$status]['color']) . ';
        color:#fff;
        padding:4px 8px;
        border-radius:20px;
        font-size:12px;
        font-weight:600;
    ">' .
    esc_html($statuses[$status]['label']) .
    '</span>';

}

function yevhenb_mono_admin_page() {

    $current_page = isset($_GET['paged'])
        ? max(1, absint(wp_unslash($_GET['paged'])))
        : 1;

    $per_page = 20;

    $orders = wc_get_orders([
        'limit'   => $per_page,
        'paged'   => $current_page,
        'orderby' => 'date',
        'order'   => 'DESC'
    ]);

    $total_orders = wc_get_orders([
        'return' => 'ids',
        'limit'  => -1
    ]);

    $total_pages = ceil(
        count($total_orders) / $per_page
    );

    echo '<div class="wrap">';

    echo '<h1>' .
    esc_html__('YevhenB Payments', 'yevhenb-payments-with-mono-for-woocommerce') .
    '</h1>';

    echo '<table class="widefat striped">';

    echo '<thead>
        <tr>
            <th>' . esc_html__('Order', 'yevhenb-payments-with-mono-for-woocommerce') . '</th>
            <th>' . esc_html__('Invoice', 'yevhenb-payments-with-mono-for-woocommerce') . '</th>
            <th>' . esc_html__('Status', 'yevhenb-payments-with-mono-for-woocommerce') . '</th>
            <th>' . esc_html__('Updated', 'yevhenb-payments-with-mono-for-woocommerce') . '</th>
            <th>' . esc_html__('Action', 'yevhenb-payments-with-mono-for-woocommerce') . '</th>
        </tr>
    </thead>';

    echo '<tbody>';

    foreach ($orders as $order) {

        $invoice = get_post_meta(
            $order->get_id(),
            '_mono_invoice_id',
            true
        );

        $status = get_post_meta(
            $order->get_id(),
            '_mono_status',
            true
        );

        $updated = get_post_meta(
            $order->get_id(),
            '_mono_updated',
            true
        );

        $check_url = wp_nonce_url(
            admin_url(
                'admin.php?page=yevhenb-mono-payments&yevhenb_check_order=' . $order->get_id()
            ),
            'yevhenb_mono_check_order_' . $order->get_id()
        );

        echo '<tr>

            <td>
                #' . esc_html($order->get_id()) . '
            </td>

            <td>
                ' . esc_html($invoice) . '
            </td>

            <td>
                ' . wp_kses_post(yevhenb_mono_get_status_badge($status)) . '
            </td>

            <td>
                ' . esc_html($updated) . '
            </td>

            <td>
                <a href="' . esc_url($check_url) . '" class="button button-small">
                    ' . esc_html__('Check', 'yevhenb-payments-with-mono-for-woocommerce') . '
                </a>
            </td>

        </tr>';

    }

    echo '</tbody>';

    echo '</table>';

    echo '<div class="tablenav">';
    echo '<div class="tablenav-pages">';

    echo wp_kses_post(
        paginate_links([
            'base'      => add_query_arg(
                'paged',
                '%#%'
            ),
            'format'    => '',
            'current'   => $current_page,
            'total'     => $total_pages,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;'
        ])
    );

    echo '</div>';
    echo '</div>';

    echo '</div>';

}