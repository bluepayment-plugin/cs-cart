<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

fn_register_hooks(
    'get_order_info',
    'get_orders',
    'get_payments_post',
    'update_payment_pre',
    'get_payments'
);
