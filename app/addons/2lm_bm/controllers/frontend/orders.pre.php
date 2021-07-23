<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}


if ($mode === 'details') {

    if (!empty($_REQUEST['payment_id']) && !empty($_REQUEST['order_id'])) {
        $order_info = fn_get_order_info($_REQUEST['order_id']);
        $bm_payment_ids = fn_2lm_bm_get_bluemedia_payment_ids();
        $bm_settings = Registry::get('addons.2lm_bm');

        if ($bm_settings['allow_select_gateway'] === 'Y' && in_array($_REQUEST['payment_id'], $bm_payment_ids)) {
            $gateways = [];
            $payway_list = fn_2lm_bm_do_payway_list($_REQUEST['payment_id']);

            if ($bm_settings['group_by_type'] === 'Y') {
                $grouped_gateways = [];
                if (isset($payway_list['gateway'])) {
                    foreach ($payway_list['gateway'] as $_gateway) {
                        $grouped_gateways[(string) $_gateway->gatewayType][(int) $_gateway->gatewayID] = (array) $_gateway;
                    }
                }
                $gateways = $grouped_gateways;
            }

            Registry::get('view')->assign('bluemedia_group_by_type', $bm_settings['group_by_type']);
            Registry::get('view')->assign('bluemedia_gateways', $gateways);
        }
    }

} elseif ($mode === 'subscriptions' && !empty($_REQUEST['order_id'])) {

    $order_id = $_REQUEST['order_id'];

    if ($action === 'deactivate') {
        // Deaktywacja zamówienia abonamentowego

        $payment_id = fn_2lm_bm_get_payment_id_from_order($order_id);
        $processor_params = fn_2lm_bm_get_processor_params($payment_id);

        $response = fn_2lm_bm_subscription_deactivate($processor_params, $order_id);

        if (!empty($response)) {
            fn_set_notification('N', __('notice'), __('2lm_bm_msg_auto_deactivation_request_send'));
        } else {
            fn_set_notification('E', __('error'), __('2lm_bm_unexpected_error_occurred'));
        }

        return [CONTROLLER_STATUS_REDIRECT, 'orders.details?order_id=' . $order_id];

    } else {
        // Tworzy zamówienie cykliczne (abonamentowe) (polega to na utworzeniu nowego zamówienia na bazie już istniejącego,
        // ale użyta zostanie inna wartość gateway_id (przypisana do zamówień cyklicznych)

        $cart = &Tygh::$app['session']['cart'];
        list($cart['payment_id'], ) = fn_2lm_bm_get_bluemedia_payment_ids(true);
        $cart['bm_subscription_start'] = true;
        $cart['bm_subscription_order_id'] = $order_id;

        return [CONTROLLER_STATUS_REDIRECT, 'orders.reorder?order_id=' . $order_id];
    }
}
