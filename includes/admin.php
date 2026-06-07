<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function () {

    add_menu_page(
        __('Mono Payments', 'smpl-mono-gateway'),
        __('Mono Payments', 'smpl-mono-gateway'),
        'manage_woocommerce',
        'smpl-mono',
        'smpl_mono_admin_page',
        'dashicons-money'
    );

});

add_action('admin_init', function () {

    $page = isset($_GET['page'])
        ? sanitize_text_field(wp_unslash($_GET['page']))
        : '';

    $check_order = isset($_GET['smpl_check_order'])
        ? absint(wp_unslash($_GET['smpl_check_order']))
        : 0;

    if (
        $page !== 'smpl-mono' ||
        !$check_order
    ) {
        return;
    }

    if (!current_user_can('manage_woocommerce')) {
        wp_die(
            esc_html__('You do not have permission to perform this action.', 'smpl-mono-gateway')
        );
    }

    $order_id = $check_order;

    check_admin_referer(
        'smpl_mono_check_order_' . $order_id
    );

    $order = wc_get_order($order_id);

    if (!$order) {
        wp_safe_redirect(admin_url('admin.php?page=smpl-mono'));
        exit;
    }

    $invoice = get_post_meta(
        $order_id,
        '_mono_invoice_id',
        true
    );

    if (!$invoice) {
        wp_safe_redirect(admin_url('admin.php?page=smpl-mono'));
        exit;
    }

    $settings = get_option(
        'woocommerce_smpl_mono_settings'
    );

    $token = $settings['token'] ?? '';

    if (!$token) {
        wp_safe_redirect(admin_url('admin.php?page=smpl-mono'));
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
        wp_safe_redirect(admin_url('admin.php?page=smpl-mono'));
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

    wp_safe_redirect(admin_url('admin.php?page=smpl-mono'));
    exit;

});

function smpl_mono_get_status_badge($status) {

    $status = strtolower(trim($status));

    $statuses = [

        'success' => [
            'color' => '#46b450',
            'label' => __('Success', 'smpl-mono-gateway')
        ],

        'processing' => [
            'color' => '#00a0d2',
            'label' => __('Processing', 'smpl-mono-gateway')
        ],

        'pending' => [
            'color' => '#ffb900',
            'label' => __('Pending', 'smpl-mono-gateway')
        ],

        'created' => [
            'color' => '#ffb900',
            'label' => __('Created', 'smpl-mono-gateway')
        ],

        'failure' => [
            'color' => '#dc3232',
            'label' => __('Failed', 'smpl-mono-gateway')
        ],

        'expired' => [
            'color' => '#777',
            'label' => __('Expired', 'smpl-mono-gateway')
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
        esc_html__('Unknown', 'smpl-mono-gateway') .
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

function smpl_mono_admin_page() {

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
    esc_html__('Mono Payments', 'smpl-mono-gateway') .
    '</h1>';

    echo '<table class="widefat striped">';

    echo '<thead>
        <tr>
            <th>' . esc_html__('Order', 'smpl-mono-gateway') . '</th>
            <th>' . esc_html__('Invoice', 'smpl-mono-gateway') . '</th>
            <th>' . esc_html__('Status', 'smpl-mono-gateway') . '</th>
            <th>' . esc_html__('Updated', 'smpl-mono-gateway') . '</th>
            <th>' . esc_html__('Action', 'smpl-mono-gateway') . '</th>
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
                'admin.php?page=smpl-mono&smpl_check_order=' . $order->get_id()
            ),
            'smpl_mono_check_order_' . $order->get_id()
        );

        echo '<tr>

            <td>
                #' . esc_html($order->get_id()) . '
            </td>

            <td>
                ' . esc_html($invoice) . '
            </td>

            <td>
                ' . wp_kses_post(smpl_mono_get_status_badge($status)) . '
            </td>

            <td>
                ' . esc_html($updated) . '
            </td>

            <td>
                <a href="' . esc_url($check_url) . '" class="button button-small">
                    ' . esc_html__('Check', 'smpl-mono-gateway') . '
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