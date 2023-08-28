<?php

use Tygh\Registry;
use Tygh\Languages\Languages;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

/**
 * Display info about "How to start" in the addon configuration popup.
 *
 * @return string
 */
function fn_bm_info()
{
    if (isset(Tygh::$app['view'])) {
        return Tygh::$app['view']->fetch('addons/2lm_bm/settings/bm_info.tpl');
    }
}

/**
 * Check a PHP environment.
 *
 * @return array
 */
function fn_2lm_bm_check_php_environment()
{
    $errors = [];

    if (!version_compare(PHP_VERSION, '5.5', '>=')) {
        $errors[] = __('2lm_bm_error_wrong_php_version', ['[PHPVERSION]' => PHP_VERSION]);
    }
    if (!extension_loaded('xmlwriter') || !class_exists('XMLWriter')) {
        $errors[] = __('2lm_bm_error_xmlwriter_is_required');
    }
    if (!extension_loaded('xmlreader') || !class_exists('XMLReader')) {
        $errors[] = __('2lm_bm_error_xmlreader_is_required');
    }
    if (!extension_loaded('iconv')) {
        $errors[] = __('2lm_bm_error_iconv_is_required');
    }
    if (!extension_loaded('mbstring')) {
        $errors[] = __('2lm_bm_error_mbstring_is_required');
    }
    if (!extension_loaded('hash')) {
        $errors[] = __('2lm_bm_error_hash_is_required');
    }

    return $errors;
}

/**
 * Install an addon.
 */
function fn_2lm_bm_install()
{
    $company_id = fn_get_runtime_company_id();

    $id = db_query('INSERT INTO ?:payment_processors ?e', [
        'processor' => 'BlueMedia',
        'processor_script' => 'bm.php',
        'processor_template' => 'addons/2lm_bm/views/orders/components/payments/bluemedia.tpl',
        'admin_template' => 'bluemedia.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'addon' => '2lm_bm'
    ]);

    $id_arr[] = fn_2lm_create_bm_payment_method($id, $company_id, '');
    $id_arr[] = fn_2lm_create_bm_payment_method($id, $company_id, 'blik');
    $id_arr[] = fn_2lm_create_bm_payment_method($id, $company_id, 'apple');
    $id_arr[] = fn_2lm_create_bm_payment_method($id, $company_id, 'visamobile');

    if (fn_allowed_for('ULTIMATE')) {
        foreach($id_arr as $_id) {
            db_query(
                "INSERT INTO ?:ult_objects_sharing (share_company_id, share_object_id, share_object_type) VALUES (?i, ?i, 'payments');",
                $company_id, $_id
            );
        }
    }
}

/**
 * Dodaj nową metodę płatności.
 *
 * @param int $id
 * @param int $company_id
 * @param int $gateway
 *
 * @return mixed
 */
function fn_2lm_create_bm_payment_method($id, $company_id, $gateway)
{
    $status = 'D';
    $processor_params = '';
    if ($gateway === 'apple') {
        $processor_params = serialize(['gateway_id' => BLUEMEDIA_GATEWAY_ID_APPLE_PAY]);
    } elseif ($gateway === 'visamobile') {
        $processor_params = serialize(['gateway_id' => BLUEMEDIA_GATEWAY_ID_VISA_MOBILE]);
        $status = 'D';
    }
    $pid = db_query(
        'INSERT INTO ?:payments ?e', [
        'company_id' => $company_id,
        'usergroup_ids' => '0',
        'position' => 0,
        'status' => $status,
        'template' => 'addons/2lm_bm/views/orders/components/payments/bluemedia.tpl',
        'processor_id' => $id,
        'processor_params' => $processor_params,
        'a_surcharge' => 0.0,
        'p_surcharge' => 0.0,
        'tax_ids' => '',
        'localization' => '',
        'payment_category' => 'tab1'
    ]);

    $data = [
        'payment_id' => $pid,
        'payment' => '',
        'description' => '',
        'instructions' => '',
        'surcharge_title' => '',
    ];
    if (!empty($gateway)) {
        $gateway .= '_';
    }
    foreach (Languages::getAll() as $data['lang_code'] => $_v) {
        $data['payment'] = __('2lm_bm_payment_' . $gateway . 'title', null, $data['lang_code']);
        $data['description'] = __('2lm_bm_payment_' . $gateway . 'description', null, $data['lang_code']);
        db_query('INSERT INTO ?:payment_descriptions ?e', $data);
    }

    return $pid;
}

/**
 * Uninstall an addon.
 */
function fn_2lm_bm_uninstall()
{
    db_query("DELETE FROM ?:payment_descriptions WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script = 'bm.php'))");
    if (fn_allowed_for('ULTIMATE')) {
        db_query("DELETE FROM ?:ult_objects_sharing WHERE share_object_type = ?s AND share_object_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script = 'bm.php'))", 'payments');
    }
    db_query("DELETE FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script = ?s)", 'bm.php');
    db_query('DELETE FROM ?:payment_processors WHERE processor_script = ?s', 'bm.php');
}

/**
 * @param int    $order_id
 * @param string $hash
 *
 * @return bool|mixed
 */
function fn_2lm_bm_update_order_hash($order_id, $hash)
{
    if (empty($order_id) || empty($hash)) {
        return false;
    }

    $data = [
        'order_id' => (int)$order_id,
        'hash' => $hash,
    ];

    return db_query('INSERT INTO ?:bluemedia_order_hash ?e ON DUPLICATE KEY UPDATE hash = ?s', $data, $hash);
}

/**
 * Delete order hash data.
 *
 * @deprecated
 * @param int $order_id
 *
 * @return mixed
 */
function fn_2lm_bm_delete_order_hash($order_id)
{
    if (empty($order_id)) {
        return false;
    }

    return db_query('DELETE FROM ?:bluemedia_order_hash WHERE order_id = ?i', $order_id);
}

/**
 * Returns Autopay transaction status info.
 *
 * @param string $key
 *
 * @return string|null
 */
function fn_2lm_bm_transaction_status_info($key = '')
{
    $statuses = [
        'PENDING' => __('2lm_bm_transaction_status_pending'),
        'SUCCESS' => __('2lm_bm_transaction_status_success'), // Serwis Partnera otrzyma środki za transakcje - można wydać towar/usługę
        'FAILURE' => __('2lm_bm_transaction_status_failure'),
    ];

    return (!empty($key) && array_key_exists($key, $statuses)) ? $statuses[$key] : null;
}

/**
 * Returns Autopay payment statuses.
 *
 * @return array
 */
function fn_2lm_bm_get_bluemedia_payment_statuses()
{
    return [
        'SUCCESS',
        'PENDING',
        'FAILURE',
    ];
}

/**
 * Return Autopay payment statuses details.
 *
 * @param string $status
 *
 * @return string
 */
function fn_2lm_bm_get_bluemedia_payment_statuses_details($status)
{
    $statuses = [
        // Statusy ogólne (niezależne od Kanału Płatności)
        'AUTHORIZED' => 'transakcja zautoryzowana przez Kanał Płatności',
        'ACCEPTED' => 'transakcja zatwierdzona przez Call Center (np. w wyniku pozytywnie rozpatrzonej reklamacji)',
        'REJECTED' => 'transakcja przerwana przez Kanał Płatności (bank/agenta rozliczeniowego)',
        'REJECTED_BY_USER' => 'transakcja przerwana przez Klienta',
        'INCORRECT_AMOUNT' => 'zautoryzowana kwota różna od kwoty podanej przy starcie transakcji',
        'EXPIRED' => 'transakcja przeterminowana',
        'CANCELLED' => 'transakcja anulowana przez Serwis Partnera lub Call Center (np. na prośbę Klienta)',
        'RECURSION_INACTIVE' => 'błąd aktywności płatności cyklicznej',
        'ANOTHER_ERROR' => 'wystąpił inny błąd przy przetwarzaniu transakcji',
        // Statusy kartowe (specyficzne dla płatności kartowych);
        'CONNECTION_ERROR' => 'błąd z połączeniem do banku wystawcy karty płatniczej',
        'CARD_LIMIT_EXCEEDED' => 'błąd limitów na karcie płatniczej',
        'SECURITY_ERROR' => 'błąd bezpieczeństwa (np. nieprawidłowy cvv)',
        'DO_NOT_HONOR' => 'odmowa autoryzacji w banku; sugerowany kontakt klienta z wystawcą karty',
        'CARD_EXPIRED' => 'karta nieważna',
        'INCORRECT_CARD_NUMBER' => 'nieprawidłowy numer karty',
        'FRAUD_SUSPECT' => 'podejrzenie fraudu (np. zagubiona karta itp)',
        'STOP_RECURRING' => 'rekurencja niemożliwa z powodu anulowania dyspozycji klienta',
        'VOID' => 'transakcja nieudana w systemie 3DS lub błąd komunikacyjny',
        'UNCLASSIFIED' => 'błąd nieopisany',
    ];

    return isset($statuses[$status]) ? $statuses[$status] : $status;
}

/**
 * Get order payment id.
 *
 * @param int $order_id
 *
 * @return int
 */
function fn_2lm_bm_get_order_payment_id($order_id)
{
    if (empty($order_id)) {
        return 0;
    }

    return db_get_field('SELECT payment_id FROM ?:orders WHERE order_id = ?i', $order_id);
}

/**
 * Get BM payment ids.
 *
 * @param boolean $exclude_blik
 * @return array
 */
function fn_2lm_bm_get_bluemedia_payment_ids($exclude_blik = false)
{
    if ($exclude_blik) {
        return db_get_fields(
            'SELECT ?:payments.payment_id FROM ?:payments ' .
            'INNER JOIN ?:payment_processors ON ?:payments.processor_id = ?:payment_processors.processor_id ' .
            'INNER JOIN ?:payment_descriptions ON ?:payment_descriptions.payment_id = ?:payments.payment_id AND ?:payment_descriptions.lang_code = ?s ' .
            'WHERE ?:payment_processors.processor_script = ?s AND ?:payments.status = ?s AND ?:payment_descriptions.payment NOT LIKE ?s ' .
            'ORDER BY ?:payments.processor_id DESC', CART_LANGUAGE, 'bm.php', 'A', '%BLIK%'
        );
    }

    return db_get_fields(
        'SELECT ?:payments.payment_id FROM ?:payments ' .
        'INNER JOIN ?:payment_processors ON ?:payments.processor_id = ?:payment_processors.processor_id ' .
        'WHERE ?:payment_processors.processor_script = ?s AND ?:payments.status = ?s ' .
        'ORDER BY ?:payments.processor_id DESC', 'bm.php', 'A'
    );
}

/**
 * Save data to log table.
 *
 * @param int $order_id
 * @param array $data
 * @param string $action_name
 */
function fn_2lm_bm_write_to_log_table($order_id = 0, array $data = [], $action_name = '')
{
    if (defined('BLUEMEDIA_ENABLE_LOGS') && BLUEMEDIA_ENABLE_LOGS && !empty($data)) {
        $raw = null;
        if (isset($data['raw'])) {
            $raw = $data['raw'];
            unset($data['raw']);
        }

        $data = [
            'order_id' => $order_id,
            'date' => date('Y-m-d H:i:s'),
            'action' => $action_name,
            'data' => (string)print_r($data, true),
            'data_raw' => $raw,
        ];

        db_query('INSERT INTO ?:bluemedia_log ?e', $data);
    }
}

/**
 * Format amount value (to float).
 *
 * @param float|number $amount
 *
 * @return string
 */
function fn_2lm_bm_format_amount($amount)
{
    $amount = str_replace([',', ' '], '', $amount);
    return number_format((float)$amount, 2, '.', '');
}

/**
 * Format description text.
 *
 * @param string $value
 *
 * @return string
 */
function fn_2lm_bm_format_description($value)
{
    $value = trim($value);
    if (extension_loaded('iconv')) {
        return iconv('UTF-8', 'ASCII//translit', $value);
    }

    return $value;
}

/**
 * Add a hash value to the form data.
 *
 * @param array $config_data
 * @param array &$form_data
 *
 * @return array
 */
function fn_2lm_bm_generate_and_add_hash(array $config_data, array &$form_data)
{
    if (empty($config_data) || empty($form_data)) {
        return [];
    }

    $form_data['Hash'] = fn_2lm_bm_generate_hash($config_data, $form_data);

    return $form_data;
}

/**
 * Returns a hash for given $form_data data.
 *
 * @param array $config_data
 * @param array $form_data
 *
 * @return bool|string
 */
function fn_2lm_bm_generate_hash(array $config_data, array $form_data = [])
{
    if (empty($config_data) || empty($form_data)) {
        return false;
    }

    $result = fn_2lm_bm_hash_generate_helper($form_data, $config_data['separator']);

    return hash($config_data['algorithm'], $result . $config_data['api_key']);
}

/**
 * Generate a data for a hash.
 *
 * @param array $data
 * @param string $separator Default: |
 *
 * @return string
 */
function fn_2lm_bm_hash_generate_helper(array $data, $separator = '|')
{
    $result = '';
    if (!empty($data)) {
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                if (mb_strtolower($key) === 'hash') {
                    continue;
                }

                if (!empty($value) || $value == 0) {
                    $result .= $value . $separator;
                }
            } else {
                $result .= fn_2lm_bm_hash_generate_helper($value, $separator);
            }
        }
    }

    return $result;
}

/**
 * Prepare a form data for submit to new order payment.
 *
 * @param array $cart
 * @param mixed $from_order_id Parent order ID for recurring orders (null or int for start recurring)
 *
 * @return array
 */
function fn_2lm_bm_prepare_form_data_for_new_order_payment(array &$cart, $from_order_id = null)
{
    if (empty($cart)) {
        return [];
    }

    $order_id = $cart['processed_order_id'][0];
    $gateway_id = !empty($cart['payment_bluemedia_gateway']) ? $cart['payment_bluemedia_gateway'] : 0;

    $order_info = fn_get_order_info($order_id);
    $payment_data = fn_get_payment_method_data($order_info['payment_id']);
    $order_description = $order_id;
    if (!empty($payment_data['processor_params']['bm_description'])) {
        $order_description = fn_2lm_bm_format_description($payment_data['processor_params']['bm_description'] . ' ' . $order_id);
    }

    $customer_ip = fn_get_ip();
    $form_data = [
        'ServiceID' => $payment_data['processor_params']['service_id'],
        'OrderID' => $order_id,
        'Amount' => fn_2lm_bm_format_price_by_currency(
            $order_info['total'],
            CART_PRIMARY_CURRENCY,
            $order_info['secondary_currency']
        ),
        'Description' => fn_2lm_bm_format_description($order_description),
        'GatewayID' => $gateway_id,
        'Currency' => strtoupper($order_info['secondary_currency']),
        'CustomerEmail' => $order_info['email'],
        'CustomerIP' => $customer_ip['host'],
    ];

    if (!empty($from_order_id)) { // Dla płatności abonamentowych
        $form_data['RecurringAcceptanceState'] = 'ACCEPTED';
        $form_data['RecurringAction'] = 'INIT_WITH_PAYMENT';
    }

    if (isset($cart['payment_blik_code'])) {
        if ((int)$gateway_id === BLUEMEDIA_GATEWAY_ID_BLIK0) {
            $form_data['AuthorizationCode'] = $cart['payment_blik_code'];
        } else {
            unset($cart['payment_blik_code']);
        }
    }

    $form_data['PlatformName'] = PRODUCT_NAME;
    $form_data['PlatformVersion'] = PRODUCT_VERSION;
    $form_data['PlatformPluginVersion'] = fn_2lm_bm_get_addon_version();

    return $form_data;
}

/**
 * Converts price from once currency to other
 *
 * @param float  $price         value to be converted
 * @param string $currency_from in what currency did we get the value
 * @param string $currency_to   in what currency should we send the result
 *
 * @return float converted value
 */
function fn_2lm_bm_format_price_by_currency($price, $currency_from = CART_PRIMARY_CURRENCY, $currency_to = CART_SECONDARY_CURRENCY)
{
    return number_format(
        (float)fn_format_price_by_currency($price, $currency_from, $currency_to),
        2,
        '.',
        ''
    );
}

/**
 * Check if current payment is Autopay payment.
 *
 * @return bool
 */
function is_bluemedia_payment()
{
    if (empty($_REQUEST['payment_id'])) {
        return false;
    }

    return in_array($_REQUEST['payment_id'], fn_2lm_bm_get_bluemedia_payment_ids());
}


/**
 * Prepare XML data for notify response status.
 *
 * @param array $data
 * @param string $type Default: itn
 *
 * @return string
 */
function fn_2lm_bm_return_notify_status(array $data = [], $type = 'itn')
{
    if (empty($data)) {
        return '';
    }

    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startElement('confirmationList');
    $xml->writeElement('serviceID', $data['service_id']);

    if ($type === 'itn') {

        $xml->startElement('transactionsConfirmations');
        $xml->startElement('transactionConfirmed');
        $xml->writeElement('orderID', $data['order_id']);
        $xml->writeElement('confirmation', $data['status']);
        $xml->endElement();
        $xml->endElement();

    } elseif ($type === 'rpan' || $type === 'rpdn') {

        $xml->startElement('recurringConfirmations');
        $xml->startElement('recurringConfirmed');
        $xml->writeElement('clientHash', $data['client_hash']);
        $xml->writeElement('confirmation', $data['status']);
        $xml->endElement();
        $xml->endElement();

    }

    $xml->writeElement('hash', $data['Hash']);
    $xml->endElement();

    return $xml->outputMemory();
}

/**
 * Returns payment service action URL.
 *
 * @param string $mode
 * @param string $action
 *
 * @return string
 */
function fn_2lm_bm_get_action_url($mode, $action)
{
    $domain = fn_2lm_bm_map_mode_to_domain($mode);

    switch ($action) {
        case BLUEMEDIA_PAYMENT_ACTON_BALANCE:
        case BLUEMEDIA_PAYMENT_ACTON_CANCEL:
        case BLUEMEDIA_PAYMENT_ACTON_CONFIRMATION:
        case BLUEMEDIA_PAYMENT_ACTON_DEACTIVATE:
        case BLUEMEDIA_PAYMENT_ACTON_PAYMENT:
        case BLUEMEDIA_PAYMENT_ACTON_PAYWAY_LIST:
        case BLUEMEDIA_PAYMENT_ACTON_GATEWAY_LIST:
        case BLUEMEDIA_PAYMENT_ACTON_REFUND:
        case BLUEMEDIA_PAYMENT_ACTON_SECURE:
        case BLUEMEDIA_PAYMENT_ACTON_START_TRAN:
        case BLUEMEDIA_PAYMENT_ACTON_TEST_ECOMMERCE:
            break;

        default:
            $action = BLUEMEDIA_PAYMENT_ACTON_SECURE;
            break;
    }

    return sprintf('https://%s%s', $domain, $action);
}

/**
 * Maps payment mode to service payment domain.
 *
 * @param string $mode
 *
 * @return string
 */
function fn_2lm_bm_map_mode_to_domain($mode)
{
    $domain = BLUEMEDIA_PAYMENT_DOMAIN_SANDBOX;
    if ($mode === BLUEMEDIA_MODE_LIVE) {
        $domain = BLUEMEDIA_PAYMENT_DOMAIN_LIVE;
    }

    return $domain;
}

/**
 * Decode transaction data.
 *
 * @param string $transactions
 *
 * @return array
 */
function fn_2lm_bm_itn_post_transactions_decode($transactions)
{
    $json = json_encode(simplexml_load_string(base64_decode($transactions)));

    return json_decode($json, true);
}

/**
 * Prepare PP response data.
 *
 * @param array $order_info
 * @param array $payment_data
 * @param array $transaction
 *
 * @return array
 */
function fn_2lm_bm_prepare_pp_response_data($order_info, $transaction)
{
    $bm_order_statuses_mapping = $order_info['payment_method']['processor_params']['statuses'];

    $pp_response = count($order_info['payment_info']) ? $order_info['payment_info'] : [];
    $pp_response['order_status'] = $bm_order_statuses_mapping[strtolower($transaction['paymentStatus'])];
    $pp_response['reason_text'] = fn_2lm_bm_transaction_status_info($transaction['paymentStatus']);
    $pp_response['current_status_bm'] = $transaction['paymentStatus'];
    $pp_response['remoteID'] = $transaction['remoteID'];
    if (array_key_exists('remoteOutID', $transaction)) {
        $pp_response['remoteOutID'] = $transaction['remoteOutID'];
    }
    if (array_key_exists('gatewayID', $transaction)) {
        $pp_response['gatewayID'] = $transaction['gatewayID'];
    }
    $pp_response['paymentDate'] = $transaction['paymentDate'];
    $pp_response['paymentStatus'] = $transaction['paymentStatus'];
    $pp_response['amount'] = $transaction['amount'];
    $pp_response['currency'] = $transaction['currency'];
    if (array_key_exists('paymentStatusDetails', $transaction)) {
        $pp_response['paymentStatusDetails'] = fn_2lm_bm_get_bluemedia_payment_statuses_details($transaction['paymentStatusDetails']);
    }
    if (array_key_exists('customerData', $transaction)) {
        if (is_array($transaction['customerData'])) {
            foreach ($transaction['customerData'] as $name => $value) {
                if (!empty($value)) {
                    $pp_response['customerData_' . $name] = $value;
                }
            }
        } else {
            $pp_response['customerData'] = $transaction['customerData'];
        }
        unset($transaction['customerData']);
    }

    return $pp_response;
}

/**
 * Get a payway list data.
 *
 * @param int $payment_id
 *
 * @return array
 */
function fn_2lm_bm_do_payway_list($payment_id)
{
    if (empty($payment_id)) {
        return [];
    }
    $processor_params = fn_2lm_bm_get_processor_params($payment_id);
    if (empty($processor_params)) {
        return [];
    }
    $data = [
        'ServiceID' => $processor_params['service_id'],
        'MessageID' => md5(time()),
    ];
    fn_2lm_bm_generate_and_add_hash($processor_params, $data);

    return fn_2lm_bm_send_request($processor_params['mode'], BLUEMEDIA_PAYMENT_ACTON_PAYWAY_LIST, $data, BLUEMEDIA_HEADER_TRANSACTION);
}

/**
 * Get a gateway list data.
 *
 * @param int $payment_id
 *
 * @return array
 */
function fn_2lm_bm_do_gateway_list($payment_id)
{
    if (empty($payment_id)) {
        return [];
    }

    $processor_params = fn_2lm_bm_get_processor_params($payment_id);
    if (empty($processor_params) || empty($processor_params['service_id'])) {
        return [];
    }
    $data = [
        'ServiceID' => $processor_params['service_id'],
        'MessageID' => md5(time()),
        'Currencies' => CART_SECONDARY_CURRENCY
    ];
    fn_2lm_bm_generate_and_add_hash($processor_params, $data);
    $response_data = fn_2lm_bm_send_request(
        $processor_params['mode'],
        BLUEMEDIA_PAYMENT_ACTON_GATEWAY_LIST,
        $data,
        BLUEMEDIA_HEADER_TRANSACTION,
        true
    );

    $response_data->gateway = $response_data->gatewayList;
    unset($response_data->gatewayList);

    return (array)$response_data;
}

/**
 * Starts transaction in background.
 *
 * @param array $config_data
 * @param array $transaction_data
 * @param string $url_type
 *
 * @return array
 */
function fn_2lm_bm_start_background_transaction(array $config_data, array $transaction_data, $url_type = BLUEMEDIA_PAYMENT_ACTON_PAYMENT)
{
    if (empty($config_data) || empty($transaction_data)) {
        return [];
    }

    return fn_2lm_bm_send_request($config_data['mode'], $url_type, $transaction_data, BLUEMEDIA_HEADER_PRE_TRANSACTION);
}

/**
 * Do a refund.
 *
 * @param array $config_data
 * @param array $transaction_data
 *
 * @return array
 */
function fn_2lm_bm_do_refund(array $config_data, array $transaction_data)
{
    if (empty($config_data) || empty($transaction_data)) {
        return [];
    }

    $data = [
        'ServiceID' => $config_data['service_id'],
        'MessageID' => md5(time()),
    ];
    $data = array_merge($data, $transaction_data);
    fn_2lm_bm_generate_and_add_hash($config_data, $data);

    return fn_2lm_bm_send_request($config_data['mode'], BLUEMEDIA_PAYMENT_ACTON_REFUND, $data, BLUEMEDIA_HEADER_TRANSACTION);
}

/**
 * Get a balance.
 *
 * @param array $config_data
 * @param string $header_type Types: BLUEMEDIA_HEADER_TRANSACTION (default), BLUEMEDIA_HEADER_PRE_TRANSACTION
 *
 * @return array
 */
function fn_2lm_bm_get_balance(array $config_data, $header_type = BLUEMEDIA_HEADER_TRANSACTION)
{
    if (empty($config_data)) {
        return [];
    }

    $data = [
        'ServiceID' => $config_data['service_id'],
        'MessageID' => md5(time()),
    ];
    fn_2lm_bm_generate_and_add_hash($config_data, $data);

    return fn_2lm_bm_send_request($config_data['mode'], BLUEMEDIA_PAYMENT_ACTON_BALANCE, $data, $header_type);
}

/**
 * Send a request to the BM service.
 *
 * @param string $mode Live or Test
 * @param string $url_type
 * @param mixed  $data
 * @param string $header_type
 * @param bool   $use_json Czy dane request'u powinny być wysłane jako json
 *
 * @return array
 */
function fn_2lm_bm_send_request($mode, $url_type, $data, $header_type = BLUEMEDIA_HEADER_TRANSACTION, $use_json = false)
{
    $bm_url = fn_2lm_bm_get_action_url($mode, $url_type);

    $header = ['BmHeader: ' . $header_type];
    if ($use_json) {
        $header = array_merge($header, ['Content-Type: application/json']);
        $header = array_merge($header, ['Cache-Control: no-cache', 'Pragma: no-cache']);
        $header[] = 'Content-Type: application/json';
        $header[] = 'Cache-Control: no-cache';
        $header[] = 'Pragma: no-cache';

        $fields = is_array($data) ? json_encode($data) : $data;
    } else {
        $fields = is_array($data) ? http_build_query($data) : $data;
    }

    $curl = curl_init($bm_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);
    curl_close($curl);

    if ($use_json) {
        return json_decode($curl_response);
    }

    fn_2lm_bm_is_error_response($curl_response);

    return fn_2lm_bm_parse_xml_response_to_array($curl_response);
}

/**
 * Change xml data to array.
 *
 * @param string $xml_data
 *
 * @return array
 */
function fn_2lm_bm_parse_xml_response_to_array($xml_data = '')
{
    if (empty($xml_data)) {
        return [];
    }

    $response_parsed = new SimpleXMLElement($xml_data);

    return (array)$response_parsed;
}

/**
 * Display specific confirmation xml.
 *
 * @param array $bm_response
 * @param array $order_info
 * @param string $type Type of the request action (itn, rpan, rpdn)
 *
 * @return string XML confirm data
 */
function fn_2lm_bm_show_notify_status_data(array $bm_response, array $order_info, $type = 'itn')
{
    $xml_confirm = '';
    if ($type === 'itn') {
        $xml_confirm = fn_2lm_bm_show_notify_status_data_for_itn($bm_response, $order_info);
    } elseif ($type === 'rpan') {
        $xml_confirm = fn_2lm_bm_show_notify_status_data_for_rpan($bm_response, $order_info);
    } elseif ($type === 'rpdn') {
        $xml_confirm = fn_2lm_bm_show_notify_status_data_for_rpdn($bm_response, $order_info);
    }

    return $xml_confirm;
}

/**
 * Confirmation for a ITN request.
 *
 * @param array $itnResponse
 * @param array $order_info
 *
 * @return string
 */
function fn_2lm_bm_show_notify_status_data_for_itn(array $itnResponse, array $order_info)
{
    if (empty($itnResponse) || !isset($order_info['order_id'])) {
        $status = BLUEMEDIA_STATUS_NOT_CONFIRMED;
    }

    $order_id = isset($order_info['order_id']) ? $order_info['order_id'] : 0;
    $transaction_order_id = $itnResponse['transactions']['transaction']['orderID'];
    $hash = fn_2lm_bm_generate_hash($order_info['payment_method']['processor_params'], $itnResponse);
    $status = (!isset($status) && $itnResponse['hash'] === $hash && $order_id === $transaction_order_id) ?
        BLUEMEDIA_STATUS_CONFIRMED : BLUEMEDIA_STATUS_NOT_CONFIRMED;

    $result = [
        'service_id' => $order_info['payment_method']['processor_params']['service_id'],
        'order_id' => $transaction_order_id,
        'status' => $status,
    ];
    fn_2lm_bm_generate_and_add_hash($order_info['payment_method']['processor_params'], $result);

    return fn_2lm_bm_return_notify_status($result);
}

/**
 * Confirmation for a RPAN request.
 *
 * @param array $rpanResponse
 * @param array $order_info
 *
 * @return string
 */
function fn_2lm_bm_show_notify_status_data_for_rpan(array $rpanResponse, array $order_info)
{
    if (empty($rpanResponse) || !isset($order_info['order_id'])) {
        $status = BLUEMEDIA_STATUS_NOT_CONFIRMED;
    }

    $order_id = isset($order_info['order_id']) ? $order_info['order_id'] : 0;
    $transaction_order_id = $rpanResponse['transaction']['orderID'];
    $hash = fn_2lm_bm_generate_hash($order_info['payment_method']['processor_params'], $rpanResponse);
    $status = (!isset($status) && $rpanResponse['hash'] === $hash && $order_id === $transaction_order_id) ?
        BLUEMEDIA_STATUS_CONFIRMED : BLUEMEDIA_STATUS_NOT_CONFIRMED;

    $client_hash = db_get_field('SELECT client_hash FROM ?:bluemedia_subscriptions WHERE order_id = ?i', $order_id);

    $result = [
        'service_id' => $order_info['payment_method']['processor_params']['service_id'],
        'client_hash' => $client_hash,
        'status' => $status,
    ];
    fn_2lm_bm_generate_and_add_hash($order_info['payment_method']['processor_params'], $result);

    return fn_2lm_bm_return_notify_status($result, 'rpan');
}

/**
 * Confirmation for a RPDN request.
 *
 * @param array $config_data
 * @param array $rpdnResponse
 * @param array $order_info
 *
 * @return string
 */
function fn_2lm_bm_show_notify_status_data_for_rpdn(array $rpdnResponse, array $order_info)
{
    if (empty($rpdnResponse) || !isset($order_info['order_id'])) {
        $status = BLUEMEDIA_STATUS_NOT_CONFIRMED;
    }

    $order_id = isset($order_info['order_id']) ? $order_info['order_id'] : 0;
    $client_hash = db_get_field('SELECT client_hash FROM ?:bluemedia_subscriptions WHERE order_id = ?i', $order_id);
    $status = (!isset($status) && isset($rpdnResponse['transactions']['recurringData']['clientHash'])
        && $rpdnResponse['transactions']['recurringData']['clientHash'] == $client_hash)
        ? BLUEMEDIA_STATUS_CONFIRMED : BLUEMEDIA_STATUS_NOT_CONFIRMED;

    $result['client_hash'] = $client_hash;
    $result = [
        'service_id' => $order_info['payment_method']['processor_params']['service_id'],
        'client_hash' => $client_hash,
        'status' => $status,
    ];
    fn_2lm_bm_generate_and_add_hash($order_info['payment_method']['processor_params'], $result);

    return fn_2lm_bm_return_notify_status($result, 'rpdn');
}

/**
 * Checking for errors...
 *
 * @param string $response
 *
 * @thows RuntimeException  If some error occurred.
 */
function fn_2lm_bm_is_error_response($response)
{
    if (preg_match_all('@<error>(.*)</error>@Usi', $response, $data, PREG_PATTERN_ORDER)) {
        $response = fn_2lm_bm_parse_xml_response_to_array($response);
        fn_2lm_bm_write_to_log_table(
            null, $response, sprintf('Error (#BM1): "%s", code: "%s"', $response['name'] . ': ' . $response['description'], $response['statusCode'])
        );

        throw new RuntimeException((string)$response['name'] . ': ' . $response['description'], $response['statusCode']);

    } elseif (preg_match_all('/error(.*)/si', $response, $data, PREG_PATTERN_ORDER)) {
        $response = fn_2lm_bm_parse_xml_response_to_array($response);
        fn_2lm_bm_write_to_log_table(
            null, $response, sprintf('Error (#BM2): "%s", code: "%s"', $response['name'], $response['statusCode'])
        );

        throw new RuntimeException($response['name']);
    }
}

/**
 * Check if the test request comes from the correct IP address.
 *
 * @return bool
 */
function fn_2lm_bm_is_bm_test_ip()
{
    $bluemedia_remote_ips = [
        '195.182.23.3',
        '195.187.142.236',
        '195.187.142.14',
        '195.182.23.215',
        '195.182.23.249',
    ];

    return in_array($_SERVER['REMOTE_ADDR'], $bluemedia_remote_ips, true);
}

/**
 * @param array $addon_settings
 */
function fn_2lm_bm_security_check_ip($addon_settings)
{
    return; // not needed anymore
    if ($addon_settings['mode'] === 'sandbox' && !fn_2lm_bm_is_bm_test_ip()) {
        die('Sorry, Wrong IP!');
    }
}

/**
 * Save info about the refund to the datatable.
 *
 * @param array $refund_data
 *
 * @return bool|mixed
 */
function fn_2lm_bm_save_order_refund_data(array $refund_data)
{
    if (empty($refund_data)) {
        return false;
    }

    return db_query('INSERT INTO ?:bluemedia_order_refunds ?e', $refund_data);
}

/**
 * Get order refunds info.
 *
 * @param int $order_id
 *
 * @return array
 */
function fn_2lm_bm_get_order_refunds($order_id)
{
    if (empty($order_id)) {
        return [];
    }

    return db_get_array('SELECT amount, timestamp, user_id FROM ?:bluemedia_order_refunds WHERE order_id = ?i', $order_id);
}

/**
 * Get order refunds info.
 *
 * @param int $order_id
 *
 * @return float
 */
function fn_2lm_bm_get_order_refunds_sum($order_id)
{
    if (empty($order_id)) {
        return 0.0;
    }

    return db_get_field('SELECT SUM(amount) AS total FROM ?:bluemedia_order_refunds WHERE order_id = ?i', $order_id);
}

/**
 * Get a sum of refunds.
 *
 * @param array $order_refunds
 *
 * @return float
 */
function fn_2lm_bm_get_refunds_sum($order_refunds)
{
    $total = 0.0;
    foreach ($order_refunds as $refund) {
        $total += $refund['amount'];
    }

    return $total;
}

/**
 * Returns initials from user data.
 *
 * @param string $user_name
 *
 * @return string
 */
function fn_2lm_bm_get_user_initials($user_name = '')
{
    $initials = implode('.', array_map(
            function ($v) {
                return $v[0];
            },
            array_filter(
                array_map('trim', explode(' ', $user_name)))
        )
    );
    if (!empty($initials)) {
        $initials .= '.';
    }

    return $initials;
}

/**
 * @param array $init_data
 *
 * @return mixed
 */
function fn_2lm_bm_init_order_subscription(array $init_data)
{
    if (empty($init_data)) {
        return false;
    }

    return db_query(
        'INSERT INTO ?:bluemedia_subscriptions ?e ON DUPLICATE KEY UPDATE updated = ?s', $init_data, date('Y-m-d H:i:s')
    );
}

/**
 * @param array $processor_params
 * @param int $order_id
 *
 * @return array
 */
function fn_2lm_bm_subscription_deactivate($processor_params, $order_id)
{
    $client_hash = db_get_field('SELECT client_hash FROM ?:bluemedia_subscriptions WHERE order_id = ?i', $order_id);

    $form_data = [
        'ServiceID' => $processor_params['service_id'],
        'MessageID' => md5(time()),
        'ClientHash' => $client_hash,
    ];
    fn_2lm_bm_generate_and_add_hash($processor_params, $form_data);
    $response_parsed = fn_2lm_bm_start_background_transaction($processor_params, $form_data, BLUEMEDIA_PAYMENT_ACTON_DEACTIVATE);

    fn_2lm_bm_write_to_log_table(
        $order_id, ['request' => $form_data, 'response' => $response_parsed], '[SUBSCRIPTION: deactivation request] response data'
    );

    return $response_parsed;
}

/**
 * @param $order_id
 * @return array
 */
function fn_2lm_bm_get_order_subscriptions_data($order_id)
{
    return [];
}

/**
 * Set a correct flag 'order_subscription_enabled' for recurring orders.
 *
 * @param array $order_info
 * @param array $additional_data
 */
function fn_2lm_bm_get_order_info(&$order_info, $additional_data)
{
    if (AREA === 'C' && Registry::get('runtime.controller') === 'orders' && Registry::get('runtime.mode') === 'details') {
        $bm_payment_ids = fn_2lm_bm_get_bluemedia_payment_ids();
        if (in_array($order_info['payment_id'], $bm_payment_ids, true)) {
            $product_ids = array_column($order_info['products'], 'product_id');
            $blocked_products = db_get_fields(
                'SELECT bluemedia_exclude_from_rp FROM ?:products ' .
                'WHERE product_id IN (?n) AND bluemedia_exclude_from_rp = ?s', $product_ids, 'Y'
            );

            $subscription_used = db_get_field(
                'SELECT order_id FROM ?:bluemedia_subscriptions ' .
                'WHERE bm_order_id = ?i AND status = ?s', $order_info['order_id'], 'activated'
            );

            if (Registry::get('addons.2lm_bm.exclude_orders_with_discount') === 'Y') {
                $order_info['order_subscription_enabled'] = empty($blocked_products) && (float)$order_info['discount'] === 0.0
                    && (float)$order_info['subtotal_discount'] === 0.0 && empty($subscription_used);
            } else {
                $order_info['order_subscription_enabled'] = empty($blocked_products) && empty($subscription_used);
            }
        }
    }
}

/**
 * Mark recurring orders at the orders list, limit results only to recurring orders (if specific 'bm_recurring_purchases'
 * param is set) or to bm refunds.
 *
 * @param array $params
 * @param array $fields
 * @param array $sortings
 * @param string $condition
 * @param string $join
 * @param string $group
 */
function fn_2lm_bm_get_orders($params, &$fields, $sortings, &$condition, &$join, $group)
{
    if (AREA === 'A') {
        $fields[] = 'CASE WHEN BMS.status IS NULL THEN 0 ELSE BMS.status END AS bm_recurring_purchases';
        $fields[] = 'CASE WHEN BMR.remote_id IS NULL THEN 0 ELSE 1 END AS bm_refund';
        $join .= ' LEFT JOIN ?:bluemedia_subscriptions AS BMS ON BMS.order_id = ?:orders.order_id';
        $join .= ' LEFT JOIN ?:bluemedia_order_refunds AS BMR ON BMR.order_id = ?:orders.order_id';

        if (!empty($params['bm_recurring_purchases'])) {
            $condition .= db_quote(' AND BMS.status IS NOT NULL');
        }

        if (!empty($params['bm_refunds'])) {
            $condition .= db_quote(' AND BMR.remote_id IS NOT NULL');
        }
    }
}

/**
 * Przygotowanie danych do pobrania opłaty za zamówienie abonamentowe.
 *
 * @param int $order_id
 *
 * @return array
 */
function fn_2lm_bm_prepare_form_data_for_get_payment_action($order_id)
{
    if (empty($order_id)) {
        return [];
    }

    $order_info = fn_get_order_info($order_id);
    $payment_data = fn_get_payment_method_data($order_info['payment_id']);
    $order_description = $order_id;
    if (!empty($payment_data['processor_params']['bm_description'])) {
        $order_description = fn_2lm_bm_format_description($payment_data['processor_params']['bm_description'] . ' ' . $order_id);
    }

    $customer_ip = fn_get_ip();
    $form_data = [
        'ServiceID' => $payment_data['processor_params']['service_id'],
        'OrderID' => $order_id,
        'Amount' => fn_2lm_bm_format_amount($order_info['total']),
        'Description' => fn_2lm_bm_format_description($order_description),
        'GatewayID' => BLUEMEDIA_GATEWAY_ID_RECURRING,
        'Currency' => strtoupper($order_info['secondary_currency']),
        'CustomerEmail' => $order_info['email'],
        'CustomerIP' => $customer_ip['host'],
    ];

    $form_data['RecurringAcceptanceState'] = 'NOT_APPLICABLE';
    $form_data['RecurringAction'] = 'AUTO';

    return $form_data;
}

/**
 * Check if the payment is a BLIK payment.
 *
 * @param int $payment_id
 *
 * @return bool
 */
function fn_2lm_bm_is_blik_payment($payment_id)
{
    $payment_data = fn_get_payment_data($payment_id);

    return !empty($payment_data) && strpos($payment_data['payment'], 'BLIK') !== false;
}

/**
 * Remove a BLIK item from the payway list.
 *
 * @param array &$payway_list
 */
function fn_2lm_bm_remove_blik_item(&$payway_list)
{
    if (!empty($payway_list['gateway'])) {
        foreach ($payway_list['gateway'] as $id => $item) {
            if ($item->gatewayID == BLUEMEDIA_GATEWAY_ID_BLIK0) {
                unset($payway_list['gateway'][$id]);
                break;
            }
        }
    }
}

/**
 * Get payment_id from the specific order
 *
 * @param $order_id
 * @return string
 */
function fn_2lm_bm_get_payment_id_from_order($order_id)
{
    return db_get_field('SELECT payment_id FROM ?:orders WHERE order_id = ?i', $order_id);
}

/**
 * Get payment processor parameters
 *
 * @param $payment_id
 * @return array
 */
function fn_2lm_bm_get_processor_params($payment_id)
{
    $processor_params = fn_get_payment_method_data($payment_id);

    return (empty($processor_params)) ? [] : $processor_params['processor_params'];
}

/**
 * Zaktualizuj listę metod płatności usuwając nie obsługiwane metody lub ich kanały.
 *
 * @param array $params
 * @param array $payments
 */
function fn_2lm_bm_get_payments_post($params, &$payments)
{
    if (AREA === 'C') {
        $bm_settings = Registry::get('addons.2lm_bm');
        $cart = & Tygh::$app['session']['cart'];
        $cart_total = fn_2lm_bm_format_price_by_currency($cart['total'], CART_PRIMARY_CURRENCY, CART_SECONDARY_CURRENCY);

        $bm_payment_ids = fn_2lm_bm_get_bluemedia_payment_ids();
        foreach ($payments as $id => $payment) {
            $bm_first_cond = in_array($id, $bm_payment_ids) && $payment['processor_script'] === 'bm.php';

            if ($bm_first_cond && (!isset($payment['processor_params']) || empty($payment['processor_params']))) {
                unset($payments[$id]);
            }
            if ($bm_first_cond && !empty($payment['processor_params'])) {
                $gateway = fn_2lm_bm_get_gateways($id);
                if (empty($gateway['hash'])) {
                    unset($payments[$id]);
                    continue;
                }

                $params = unserialize($payment['processor_params']);
                if (empty($params['gateway'])) {
                    if (empty($gateway['gateway'])) {
                        unset($payments[$id]);
                        continue;
                    }
                    $payments[$id]['gateway'] = $gateway['gateway'];

                    if (!fn_2lm_bm_is_blik_payment($id)) {
                        fn_2lm_bm_remove_blik_item($payments[$id]['gateway']);

                        $gateways = [];
                        foreach ($payments[$id]['gateway'] as $_gid => $_gateway) {
                            $gatewayOk = false;

                            if (!empty($_gateway->currencyList)) {
                                foreach ($_gateway->currencyList as $_gconditions) {
                                    if (fn_strtoupper($_gconditions->currency) === CART_SECONDARY_CURRENCY) {
                                        $gatewayOk = true;
                                    } else {
                                        continue;
                                    }
                                    if ((!empty($_gconditions->minAmount) && $_gconditions->minAmount > $cart_total) || (!empty($_gconditions->maxAmount) && $_gconditions->maxAmount < $cart_total)) {
                                        $gatewayOk = false;
                                    }
                                }
                            }

                            if (!$gatewayOk) {
                                unset($gateways[(int)$_gateway->gatewayID]);
                            } else {
                                $gateways[(int)$_gateway->gatewayID] = (array)$_gateway;
                            }
                        }

                        if (!empty($gateways)) {
                            $payments[$id]['bluemedia_group_by_type'] = $bm_settings['group_by_type'];
                            $payments[$id]['bluemedia_gateways'] = $gateways;
                        } else {
                            unset($payments[$id]);
                        }
                    }
                    unset($gateway);
                }
                unset($params);
            }


            if ($bm_settings['group_by_type'] === 'Y') {
                $grouped_gateways = [];
                if (!empty($payments[$id]['bluemedia_gateways'])) {
                    foreach ($payments[$id]['bluemedia_gateways'] as $_gateway) {
                        $grouped_gateways[(string)$_gateway['gatewayType']][(int)$_gateway['gatewayID']] = (array)$_gateway;
                    }
                }
                $gateways = $grouped_gateways;
                $payments[$id]['bluemedia_gateways'] = $gateways;
            }
        }
    }
}

/**
 * Pobierz z bazy danych dane z dodatkowej kolumny 'processor_script'.
 *
 * @param array $params
 * @param array $fields
 * @param array $join
 * @param array $order
 * @param array $condition
 * @param array $having
 */
function fn_2lm_bm_get_payments($params, &$fields, $join, $order, $condition, $having) {
    if (AREA === 'C') {
        $field = '?:payment_processors.processor_script AS processor_script';
        if (!in_array($field, $fields)) {
            $fields[] = $field;
        }
    }
}

/**
 * Adds additional actions before payment updating
 *
 * @param array  $payment_data               Payment data
 * @param int    $payment_id                 Payment identifier
 * @param string $lang_code                  Language code
 * @param array  $certificate_file
 * @param string $certificates_dir
 * @param string $can_remove_offline_payment_params Whether offline payment parameters should be removed
 */
function fn_2lm_bm_update_payment_pre(&$payment_data, &$payment_id, &$lang_code, &$certificate_file, &$certificates_dir)
{
    if (!empty($payment_data['processor_id']) && fn_2lm_bm_is_bm_processor($payment_data['processor_id'])) {
        $img_key = 'payment_image';
        $src_type = !empty($_REQUEST["type_{$img_key}_image_icon"][0]) ? $_REQUEST["type_{$img_key}_image_icon"][0] : 'local';
        if (empty($payment_id)
            && (
                ($src_type === 'local' && empty($_FILES["file_{$img_key}_image_icon"]['name'][0]))
                || ($src_type === 'server' && empty($_REQUEST["file_{$img_key}_image_icon"][0]))
            )
        ) {
            $logo_url = Registry::get('config.current_location') . fn_get_theme_path('/[relative]/media/images/addons/2lm_bm/logo_bm.png');
            if (!empty($payment_data['processor_params']['gateway_id']) && $payment_data['processor_params']['gateway_id'] == BLUEMEDIA_GATEWAY_ID_VISA_MOBILE) {
                $logo_url = Registry::get('config.current_location') . fn_get_theme_path('/[relative]/media/images/addons/2lm_bm/VisaMobile.png');
            }

            $_REQUEST["file_{$img_key}_image_icon"][0] = $logo_url;
            $_REQUEST["type_{$img_key}_image_icon"][0] = 'url';
        }
    }
}

/**
 * Checks if payment processor is the one provided by the add-on.
 *
 * @param int $processor_id
 *
 * @return bool True if processor is BlueMedia-based
 */
function fn_2lm_bm_is_bm_processor($processor_id = 0)
{
    return (bool) db_get_field(
        'SELECT 1 FROM ?:payment_processors WHERE processor_id = ?i AND addon = ?s',
        $processor_id,
        '2lm_bm'
    );
}

/**
 * Zwróć listę metod płatności BM, które nie mają zdefiniowanej konkretnej wartości gateway_id.
 *
 * @return array
 */
function fn_2lm_bm_get_gateway_ids()
{
    $gateway_ids = [];
    $payments = db_get_fields(
        "SELECT ?:payments.processor_params FROM ?:payments 
        INNER JOIN ?:payment_processors ON ?:payments.processor_id = ?:payment_processors.processor_id 
        WHERE ?:payments.status = ?s AND ?:payment_processors.processor_script = ?s",
        'A',
        'bm.php'
    );
    foreach ($payments as $pp) {
        if (!empty($pp)) {
            $pp = unserialize($pp);
            if (!empty($pp['gateway_id'])){
                $gateway_ids[] = $pp['gateway_id'];
            }
        }
    }

    return $gateway_ids;
}

/**
 * Pobierz listę dostępnych kanałów płatności.
 * Pobrane wartości są zapisywane do cache (i doczytywane z niego)
 *
 * @param int $payment_id
 *
 * @return array|bool|mixed|string|null
 */
function fn_2lm_bm_get_gateways($payment_id) {
    static $init_cache = false;
    $cache_name = 'bm_gateway_list';
    $key = CART_SECONDARY_CURRENCY . '_' . fn_crc32($payment_id . '_' . CART_SECONDARY_CURRENCY);

    if (!$init_cache) {
        Registry::registerCache($cache_name, [], Registry::cacheLevel('static'));
        $init_cache = true;
    }

    $payway_list = Registry::get($cache_name . '.' . $key);
    if (empty($payway_list)) {
        $payway_list = fn_2lm_bm_do_gateway_list($payment_id);

        if (!empty($payway_list) && $payway_list['result'] === 'OK' && !empty($payway_list['hash'])) {
            Registry::set($cache_name . '.' . $key, $payway_list);
        }
    }

    return $payway_list;
}

/**
 * Zwróć numer wersji dodatku 2LM BLueMedia.
 *
 * @return string
 */
function fn_2lm_bm_get_addon_version() {
    return db_get_field('SELECT version FROM ?:addons WHERE addon = ?s', '2lm_bm');
}