<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode === 'checkout') {

    $bm_payment_ids = fn_2lm_bm_get_bluemedia_payment_ids();

    $cart = Registry::get('view')->getTemplateVars('cart');
    if (isset($cart['bm_subscription_order_id']) && $cart['bm_subscription_start'] && in_array($cart['payment_id'], $bm_payment_ids, true)) {
        $bm_payment_id = $cart['payment_id']; //TODO_RAV: check it!!
        $payment_info = Registry::get('view')->getTemplateVars('payment_info');
        $payment_method = Registry::get('view')->getTemplateVars('payment_method');

        $payment_methods = Registry::get('view')->getTemplateVars('payment_methods');
        foreach ($payment_methods as $tab_id => $tab) {
            if (!isset($tab[$bm_payment_id])) {
                unset($payment_methods[$tab_id]);
            } else {
                foreach ($tab as $payment_id => $payment) {
                    if ($payment_id != $bm_payment_id) {
                        unset($payment_methods[$tab_id][$payment_id]);
                    } else {
                        $payment_methods[$tab_id][$payment_id]['payment'] .= ' ' . __('2lm_bm_suffix_recurring_purchases');

                        $base_order_url = '<a href="'. fn_url('orders.details&order_id=' . $cart['bm_subscription_order_id']) .'">' . $cart['bm_subscription_order_id'] . '</a>';
                        $clear_cart_url = '<a href="' . fn_url('checkout.clear') . '">' . __('clear_cart') . '</a>';
                        $mapping = ['[LINK_TO_BASE_ORDER]' => $base_order_url, '[LINK_TO_CLEAR_CART]' => $clear_cart_url];

                        $payment_methods[$tab_id][$payment_id]['instructions'] .= __('2lm_bm_recurring_purchases_instructions', $mapping);
                    }
                }
            }
        }

        Tygh::$app['view']->assign('payment_methods', $payment_methods);
    }
}
