<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($mode === 'place_order') {

        $cart = & Tygh::$app['session']['cart'];

        if (!empty($cart['processed_order_id'])) {
            // Save a chosen autopay payment id
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

            // Save a chosen autopay payment id
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