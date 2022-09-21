<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

// modes
define('BLUEMEDIA_MODE_SANDBOX', 'sandbox');
define('BLUEMEDIA_MODE_LIVE', 'live');

// domains
define('BLUEMEDIA_PAYMENT_DOMAIN_SANDBOX', 'pay-accept.bm.pl');
define('BLUEMEDIA_PAYMENT_DOMAIN_LIVE', 'pay.bm.pl');

// action paths
define('BLUEMEDIA_PAYMENT_ACTON_BALANCE', '/balanceGet');
define('BLUEMEDIA_PAYMENT_ACTON_CANCEL', '/transactionCancel');
define('BLUEMEDIA_PAYMENT_ACTON_CONFIRMATION', '/confirmation/payment');
define('BLUEMEDIA_PAYMENT_ACTON_DEACTIVATE', '/deactivate_recurring');
define('BLUEMEDIA_PAYMENT_ACTON_PAYMENT', '/payment');
define('BLUEMEDIA_PAYMENT_ACTON_PAYWAY_LIST', '/paywayList');
define('BLUEMEDIA_PAYMENT_ACTON_GATEWAY_LIST', '/gatewayList/v2');
define('BLUEMEDIA_PAYMENT_ACTON_REFUND', '/transactionRefund');
define('BLUEMEDIA_PAYMENT_ACTON_SECURE', '/secure');
define('BLUEMEDIA_PAYMENT_ACTON_START_TRAN', '/startTran');
define('BLUEMEDIA_PAYMENT_ACTON_TEST_ECOMMERCE', '/test_ecommerce');

// payway form section markers
//define('BLUEMEDIA_FORM_PAYWAY_BEGIN', '<!-- PAYWAY FORM BEGIN -->');
//define('BLUEMEDIA_FORM_PAYWAY_END', '<!-- PAYWAY FORM END -->');

// confirmation statuses
define('BLUEMEDIA_STATUS_CONFIRMED', 'CONFIRMED');
define('BLUEMEDIA_STATUS_NOT_CONFIRMED', 'NOTCONFIRMED');

// headers
define('BLUEMEDIA_HEADER_TRANSACTION', 'pay-bm');
define('BLUEMEDIA_HEADER_PRE_TRANSACTION', 'pay-bm-continue-transaction-url');

// gateways
define('BLUEMEDIA_GATEWAY_ID_TEST', 106);
define('BLUEMEDIA_GATEWAY_ID_BLIK0', 509);
define('BLUEMEDIA_GATEWAY_ID_APPLE_PAY', 1513);
define('BLUEMEDIA_GATEWAY_ID_RECURRING', 1503);

// write to logs
define('BLUEMEDIA_ENABLE_LOGS', true);
