<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($mode === 'place_order') {

        $cart = & Tygh::$app['session']['cart'];

        if (!empty($cart['processed_order_id'])) {
            // Save a chosen bluemedia payment id
            if (!empty($_REQUEST['payment_bluemedia_gateway'])) {
                $cart['payment_bluemedia_gateway'] = $_REQUEST['payment_bluemedia_gateway'];
            }
            // Save a Blik code.
            if (!empty($_REQUEST['payment_blik_code'])) {
                $cart['payment_blik_code'] = $_REQUEST['payment_blik_code'];
            }

            $_order_id = end($cart['processed_order_id']);
            $_payment_id = db_get_field(
                'SELECT payment_id FROM ?:orders WHERE order_id = ?i', $_order_id
            );
            $_bm_payment_ids = fn_2lm_bm_get_bluemedia_payment_ids();
            if (!empty($_bm_payment_ids)
                && !empty($_REQUEST['payment_id'])
                && in_array($_payment_id, $_bm_payment_ids)
                && in_array($_REQUEST['payment_id'], $_bm_payment_ids)
            ) {
                unset($cart['processed_order_id']);
                if (isset($cart['failed_order_id'])) {
                    unset($cart['failed_order_id']);
                }
            }

        } else {

            // Save a chosen bluemedia payment id
            if (!empty($_REQUEST['payment_bluemedia_gateway'])) {
                $cart['payment_bluemedia_gateway'] = $_REQUEST['payment_bluemedia_gateway'];
            }
            // Save a Blik code.
            if (!empty($_REQUEST['payment_blik_code'])) {
                $cart['payment_blik_code'] = $_REQUEST['payment_blik_code'];
            }

        }
    }

    return true;
}


if ($mode === 'checkout') {

    $cart = $_SESSION['cart'];
    if (!empty($_REQUEST['payment_id']) || !empty($cart['payment_id'])) {
        $bm_settings = Registry::get('addons.2lm_bm');

        $payment_id = !empty($_REQUEST['payment_id']) ? $_REQUEST['payment_id'] : $cart['payment_id'];
        $bm_payment_ids = fn_2lm_bm_get_bluemedia_payment_ids();

        if ($bm_settings['allow_select_gateway'] === 'Y' && in_array($payment_id, $bm_payment_ids)) {
            $gateways = [];

            $payway_list = fn_2lm_bm_get_gateways($payment_id);

            if (!fn_2lm_bm_is_blik_payment($payment_id)) {
                fn_2lm_bm_remove_blik_item($payway_list);

                if ($bm_settings['group_by_type'] === 'Y') {
                    $grouped_gateways = [];
                    if (isset($payway_list['gateway'])) {
                        foreach ($payway_list['gateway'] as $_gateway) {
                            $grouped_gateways[(string)$_gateway->gatewayType][(int)$_gateway->gatewayID] = (array)$_gateway;
                        }
                    }
                    $gateways = $grouped_gateways;
                } else {
                    foreach ($payway_list['gateway'] as $_gateway) {
                        $gateways[(int)$_gateway->gatewayID] = (array)$_gateway;
                    }
                }
            }

            Registry::get('view')->assign('bluemedia_group_by_type', $bm_settings['group_by_type']);
            Registry::get('view')->assign('bluemedia_gateways', $gateways);
        }
    }

}
