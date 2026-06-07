<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', function () {

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    add_filter('woocommerce_payment_gateways', function ($gateways) {
        $gateways[] = 'WC_SMPL_Mono_Gateway';
        return $gateways;
    });

    class WC_SMPL_Mono_Gateway extends WC_Payment_Gateway {

        public $token;

        public function __construct() {

            $this->id = 'smpl_mono';
            $this->method_title = __('Monobank', 'smpl-mono-gateway');
            $this->has_fields = false;

            $this->init_form_fields();
            $this->init_settings();

            $this->title       = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->token       = $this->get_option('token');

            add_action(
                'woocommerce_update_options_payment_gateways_' . $this->id,
                [$this, 'process_admin_options']
            );
        }

        public function init_form_fields() {

            $this->form_fields = [

                'enabled' => [
                    'title'   => __('Enable', 'smpl-mono-gateway'),
                    'type'    => 'checkbox',
                    'default' => 'yes'
                ],

                'title' => [
                    'title'   => __('Title', 'smpl-mono-gateway'),
                    'type'    => 'text',
                    'default' => __('Pay via Monobank', 'smpl-mono-gateway')
                ],

                'description' => [
                    'title' => __('Description', 'smpl-mono-gateway'),
                    'type'  => 'textarea'
                ],

                'token' => [
                    'title' => __('API Token', 'smpl-mono-gateway'),
                    'type'  => 'text'
                ]

            ];

        }

        public function payment_fields() {

            if (!empty($this->description)) {
                echo '<p>' . esc_html($this->description) . '</p>';
            }

        }

        public function process_payment($order_id) {

            $order = wc_get_order($order_id);

            if (!$order) {

                wc_add_notice(
                    esc_html__('Payment error', 'smpl-mono-gateway'),
                    'error'
                );

                return;

            }

            /*
             * Keep this endpoint synchronized with includes/webhook.php.
             * If webhook.php checks "smpl-mono-webhook", keep this URL unchanged.
             */
            $webhook_url = home_url('/smpl-mono-webhook');

            $response = wp_remote_post(
                'https://api.monobank.ua/api/merchant/invoice/create',
                [
                    'headers' => [
                        'X-Token'      => $this->token,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => wp_json_encode([
                        'amount' => intval($order->get_total() * 100),
                        'ccy'    => 980,
                        'merchantPaymInfo' => [
                            'reference' => strval($order_id)
                        ],
                        'redirectUrl' => $this->get_return_url($order),
                        'webHookUrl'  => $webhook_url
                    ])
                ]
            );

            if (is_wp_error($response)) {

                wc_add_notice(
                    esc_html__('Payment error', 'smpl-mono-gateway'),
                    'error'
                );

                return;

            }

            $body = json_decode(
                wp_remote_retrieve_body($response),
                true
            );

            if (empty($body['invoiceId']) || empty($body['pageUrl'])) {

                wc_add_notice(
                    esc_html__('Payment error', 'smpl-mono-gateway'),
                    'error'
                );

                return;

            }

            update_post_meta(
                $order_id,
                '_mono_invoice_id',
                sanitize_text_field($body['invoiceId'])
            );

            return [
                'result'   => 'success',
                'redirect' => esc_url_raw($body['pageUrl'])
            ];

        }

    }

});