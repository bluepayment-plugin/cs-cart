<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($mode === 'details') {

    if (!empty($_REQUEST['order_id'])) {
        $pictogram = '';
        $label = __('2lm_bm_label_auto_order_create');
        $url = 'orders.subscriptions?order_id=' . $_REQUEST['order_id'];
        $new_window = '';

        $status = db_get_field('SELECT status FROM ?:bluemedia_subscriptions WHERE order_id = ?i', $_REQUEST['order_id']);
        if (!empty($status)) {
            if (strtolower($status) === 'pending') {
                $label = __('2lm_bm_label_auto_order_pending');
                Registry::get('view')->assign('bm_order_subscription_not_active', true);

            } elseif (strtolower($status) === 'activated') {
                $label = __('2lm_bm_label_auto_order_deactivate');
                $url = 'orders.subscriptions.deactivate?order_id=' . $_REQUEST['order_id'];
                $pictogram = 'ty-icon-minus-circle';
            }
        } else {
            $pictogram = 'ty-icon-plus-circle';
            $new_window = 'cm-new-window';
        }

        Registry::get('view')->assign('bm_order_subscription_label', $label);
        Registry::get('view')->assign('bm_order_subscription_url', $url);
        Registry::get('view')->assign('bm_order_subscription_icon', $pictogram);
        Registry::get('view')->assign('open_in_new_window', $new_window);
    }
}
