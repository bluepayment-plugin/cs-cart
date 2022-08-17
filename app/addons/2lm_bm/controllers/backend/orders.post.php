<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($mode === 'details') {

    $order_info = Registry::get('view')->getTemplateVars('order_info');
    $bm_payment_ids = fn_2lm_bm_get_bluemedia_payment_ids();
    if (in_array($order_info['payment_id'], $bm_payment_ids, true)) {
        $has_access_to_refund = fn_check_user_access($auth['user_id'], 'manage_2lm_bm_refund');
        $order_refunds = fn_2lm_bm_get_order_refunds($_REQUEST['order_id']);
        $allow_refund = true;
        $refund_total = 0;

        if (!empty($order_refunds)) {
            $order_total = $order_info['total'];
            $refund_total = fn_2lm_bm_get_refunds_sum($order_refunds);
            if ($order_total == $refund_total) {
                $allow_refund = false;
            }
        } else {
            $allow_refund = false;
            if (!empty($order_info['payment_info']['paymentStatus']) && $order_info['payment_info']['paymentStatus'] === 'SUCCESS') {
                $allow_refund = true;
            }
        }

        $subscr_status = db_get_field('SELECT status FROM ?:bluemedia_subscriptions WHERE order_id = ?i', $_REQUEST['order_id']);

        Tygh::$app['view']->assign('subscr_status', $subscr_status);
        Tygh::$app['view']->assign('has_access_to_refund', $has_access_to_refund);
        Tygh::$app['view']->assign('allow_refund', $allow_refund);
        Tygh::$app['view']->assign('refund_order_info', $order_refunds);
        Tygh::$app['view']->assign('refund_total', $refund_total);
    }
}
