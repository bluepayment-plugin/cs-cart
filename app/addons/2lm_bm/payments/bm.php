<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if (defined('PAYMENT_NOTIFICATION')) {

    if ($_REQUEST['payment'] === 'bm') {
        //------------------------------ NOTYFIKACJE Z SERWERA PŁATNOŚCI BLUEMEDIA -------------------------------------

        if ($mode === 'notify') {

            if ($action === 'rpan') {
                //---------------------------- DLA USŁUGI PŁATNOŚCI AUTOMATYCZNYCH -------------------------------------

                if (!empty($_POST['recurring'])) {
                    $itnResponse = fn_2lm_bm_itn_post_transactions_decode($_POST['recurring']);
                    $order_id = $itnResponse['transaction']['orderID'];
                    $recurring_data = $itnResponse['recurringData'];
                    $cart_data = $itnResponse['cardData'];
                    $order_info = fn_get_order_info($order_id, false, true, true, true);
                    fn_2lm_bm_security_check_ip($order_info['payment_method']['processor_params']);

                    fn_2lm_bm_write_to_log_table($order_id, [$itnResponse, 'raw' => $_POST['recurring']], '[NOTIFY RPAN] "Recurring"');
                    db_query(
                        'UPDATE ?:bluemedia_subscriptions SET client_hash = ?s, status = ?s, updated = ?s WHERE order_id = ?i',
                        $recurring_data['clientHash'], 'activated', date('Y-m-d H:i:s'), $order_id
                    );

                    // report to BlueMedia RPAN if message is received ('send' a confirmation)
                    $xml_confirm_data = fn_2lm_bm_show_notify_status_data($itnResponse, $order_info, 'rpan');
                    fn_2lm_bm_write_to_log_table($order_id, [$xml_confirm_data], '[CONFIRM RPAN] XML confirmation data');
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/xml; charset: UTF-8');
                    echo '<?xml version="1.0" encoding="UTF-8"?>';
                    echo trim($xml_confirm_data);
                    die();
                }

            } elseif ($action === 'rpdn') {
                //------------------------------ DEZAKTYWACJA PŁATNOŚCI AUTOMATYCZNYCH ---------------------------------

                fn_2lm_bm_write_to_log_table(0, $_REQUEST, '[NOTIFY RPDN] Request');
                if (!empty($_POST['recurring'])) {
                    $rpdnResponse = fn_2lm_bm_itn_post_transactions_decode($_POST['recurring']);
                    fn_2lm_bm_write_to_log_table(0, ['request' => $_REQUEST, 'response' => $rpdnResponse], '[NOTIFY RPDN] Request');

                    $recurringData = $rpdnResponse['recurringData'];
                    $order_id = db_get_field('SELECT order_id FROM ?:bluemedia_subscriptions WHERE client_hash = ?s', $recurringData['clientHash']);
                    $order_info = fn_get_order_info($order_id, false, true, true, true);
                    fn_2lm_bm_security_check_ip($order_info['payment_method']['processor_params']);

                    if ($recurringData['recurringAction'] === 'DEACTIVATE') {
                        $update_status = db_query(
                            'UPDATE ?:bluemedia_subscriptions SET status = ?s, updated = ?s, comment = ?s WHERE client_hash = ?s',
                            'disactivated', date('Y-m-d H:i:s'), $recurringData['deactivationSource'], $recurringData['clientHash']
                        );
                        if ($update_status) {
                            fn_2lm_bm_write_to_log_table(0, [$rpdnResponse], '[RPDN] Order subscription deactivated');
                        }
                    }

                    // report to BlueMedia RPDN if message is received ('send' a confirmation)
                    $xml_confirm_data = fn_2lm_bm_show_notify_status_data($rpdnResponse, $order_info, 'rpdn');
                    fn_2lm_bm_write_to_log_table($order_id, [$xml_confirm_data], '[CONFIRM RPDN] XML confirmation data');
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/xml; charset: UTF-8');
                    echo '<?xml version="1.0" encoding="UTF-8"?>';
                    echo trim($xml_confirm_data);
                    die();
                }

            } elseif ($action === 'itn') {
                // ------------------------- POWIADOMIENIA KANAŁEM ITN Z SERWISU BM ------------------------------------

                try {
                    if (empty($_POST['transactions'])) {
                        throw new Exception("'transactions' is empty in POST request");
                    }

                    $itnResponse = fn_2lm_bm_itn_post_transactions_decode($_POST['transactions']);
                    $order_id = $itnResponse['transactions']['transaction']['orderID'];

                    if (empty($order_id)) {
                        throw new Exception("'orderID' is empty in POST request");
                    }

                    fn_2lm_bm_write_to_log_table($order_id, ['raw' => $_POST['transactions'], 'data' => $itnResponse], '[NOTIFY] "Transactions"');

                    if (!fn_check_payment_script('bm.php', $order_id)) {
                        throw new Exception(
                            sprintf("Order ID %s was placed with another payment method", $order_id)
                        );
                    }

                    $order_info = fn_get_order_info($order_id, false, true, true, true);
                    fn_2lm_bm_security_check_ip($order_info['payment_method']['processor_params']);

                    if (empty($order_info)) {
                        throw new Exception("Order info is empty");
                    }

                    $pp_response = fn_2lm_bm_prepare_pp_response_data(
                        $order_info, $itnResponse['transactions']['transaction']
                    );

                    if (empty($pp_response['order_status'])) {
                        throw new Exception("Order status is not mapped for processor status " . $itnResponse['transactions']['transaction']['paymentStatus']);
                    }

                    if ($pp_response['paymentStatus'] !== 'SUCCESS') {
                        //sleep(10);
                        $valid_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);

                        $payment_info = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = 'P'", $order_id);
                        $payment_info = !empty($payment_info) ? unserialize(fn_decrypt_text($payment_info)) : [];

                        if (!empty($valid_id) &&
                            (empty($payment_info['paymentStatus']) || $payment_info['paymentStatus'] !== 'SUCCESS') &&
                            $order_info['status'] !== 'I'
                        ) {
                            fn_update_order_payment_info($order_id, $pp_response);
                            fn_change_order_status($order_id, $pp_response['order_status']);
                        }
                    } else {
                        fn_finish_payment($order_id, $pp_response);
                    }

                    $xml_confirm_data = fn_2lm_bm_show_notify_status_data($itnResponse, $order_info, 'itn');

                    fn_2lm_bm_write_to_log_table($order_id, ['xml' => $xml_confirm_data, 'pp_response' => $pp_response], '[CONFIRM ITN] XML confirmation data');

                    header('Content-Type: application/xml; charset: UTF-8');
                    echo '<?xml version="1.0" encoding="UTF-8"?>';
                    echo $xml_confirm_data;
                    die();

                } catch (Exception $e) {
                    http_response_code(400);
                    echo $e->getMessage();
                    die();
                }
            }

        } elseif ($mode === 'return') {
            //----------------------------------------- POWRÓT DO SKLEPU -----------------------------------------------

            fn_2lm_bm_return_to_shop_action($_REQUEST);

        } else {
            //----------------------------- PRZYPADEK NIEOKREŚLONY - LOGUJE DANE ---------------------------------------

            if (isset($_POST['transactions'])) {
                $_POST['decoded'] = fn_2lm_bm_itn_post_transactions_decode($_POST['transactions']);
            }
            fn_2lm_bm_write_to_log_table(0, $_REQUEST, 'unknown mode');

            fn_order_placement_routines('index_redirect');
        }

        exit;
    }

} elseif ($mode === 'place_order' || $mode === 'repay') {
    //----------------------------------------- ZŁOŻENIE NOWEGO ZAMÓWIENIA ---------------------------------------------
    $cart = &Tygh::$app['session']['cart'];
    if (!isset($cart['processed_order_id'][0])) {
        fn_set_notification('E', __('error'), 'order ID not exist');
        fn_2lm_bm_write_to_log_table(0, $_REQUEST, '[PLACE_ORDER] order ID not exist');

        return [CONTROLLER_STATUS_REDIRECT];
    }

    $processed_order_id = $cart['processed_order_id'][0];
    $from_order_id = null;

    if (!empty($cart['bm_subscription_start'])) {
        $from_order_id = $cart['bm_subscription_order_id'];
        unset($cart['bm_subscription_start'], $cart['bm_subscription_order_id']);
    }

    $form_data = fn_2lm_bm_prepare_form_data_for_new_order_payment($cart, $from_order_id);

    if (empty($form_data['GatewayID']) &&
        !empty($order_info['payment_method']['processor_params']['gateway_id'])
    ) {
        $form_data['GatewayID'] = intval($order_info['payment_method']['processor_params']['gateway_id']);
    }

    fn_2lm_bm_generate_and_add_hash($order_info['payment_method']['processor_params'], $form_data);

    if (!fn_2lm_bm_update_order_hash($processed_order_id, $form_data['Hash'])) {
        fn_set_notification('E', __('error'), "Something went wrong - order with give id {$processed_order_id} exists");
        fn_2lm_bm_write_to_log_table($processed_order_id, $_REQUEST, '[PLACE_ORDER] order with this id exists');

        return [CONTROLLER_STATUS_REDIRECT];
    }

    $gateway_id = !empty($cart['payment_bluemedia_gateway']) ? (int) $cart['payment_bluemedia_gateway'] : 0;

    if ($gateway_id === BLUEMEDIA_GATEWAY_ID_BLIK0) {
        //------------------------------------------- PŁATNOŚĆ BLIK'IEM ------------------------------------------------

        try {
            $response_parsed = fn_2lm_bm_start_background_transaction($order_info['payment_method']['processor_params'], $form_data, BLUEMEDIA_PAYMENT_ACTON_PAYMENT);

            fn_2lm_bm_write_to_log_table(
                $processed_order_id, ['url' => BLUEMEDIA_PAYMENT_ACTON_PAYMENT, 'data' => $form_data, 'response' => $response_parsed,],
                '[PLACE_ORDER - BLIK] new order: send form to the BM server'
            );

            if ($response_parsed['confirmation'] !== 'CONFIRMED') {
                fn_2lm_bm_write_to_log_table($processed_order_id, ['response' => $response_parsed,], 'ERROR: After parsing response confirmation is not CONFIRMED.');
                throw new Exception ('After parsing response confirmation is not CONFIRMED.');
            }

            $bm_response_payment_status = !empty($response_parsed['paymentStatus'])
                ? strtolower($response_parsed['paymentStatus'])
                : 'pending';

            if ($bm_response_payment_status === 'failure') {
                fn_2lm_bm_write_to_log_table($processed_order_id, ['response' => $response_parsed,], 'paymentStatus == failure');
                throw new Exception (__('2lm_bm_blik_wrong_code_error_msg'));
            }

            if (empty($processor_data['processor_params']['statuses'][$bm_response_payment_status])) {
//                throw new Exception ('Order status is not mapped for BM status: ' . $bm_response_payment_status);
            }

            $pp_response['order_status'] = $processor_data['processor_params']['statuses'][$bm_response_payment_status];
            $pp_response['reason_text'] = '';
            fn_change_order_status($processed_order_id, $pp_response['order_status']);

            $pp_response['is_deferred_payment'] = true;

        } catch (Exception $e) {
            fn_set_notification('E', __('2lm_bm') . ': ' . __('error'), $e->getMessage());
            fn_redirect('checkout.checkout');
        }

    } elseif (!empty($from_order_id)) {
        //------------------------------------------------ ZAMÓWIENIE ABONAMENTOWE -------------------------------------

        $response = fn_2lm_bm_start_subscription_order($form_data, $order_info['payment_method']['processor_params'], $processed_order_id, $from_order_id);

        if (!empty($response['redirecturl'])) {
            fn_2lm_bm_update_subscription_data($response);
            fn_redirect($response['redirecturl'], true);
        }

    } else {
        //------------------------------------------------ NORMALNE ZAMÓWIENIE -----------------------------------------

        $action_url = fn_2lm_bm_get_action_url($order_info['payment_method']['processor_params']['mode'], BLUEMEDIA_PAYMENT_ACTON_PAYMENT);
        fn_2lm_bm_write_to_log_table(
            $processed_order_id, ['url' => $action_url, 'data' => $form_data], '[PLACE_ORDER] new order: send form to the BM server'
        );

        fn_create_payment_form($action_url, $form_data, 'BlueMedia server', false);
        exit;
    }

}


//----------------------------------------------------------------------------------------------------------------------
// Funkcje pomocnicze

/**
 * Akcje wykonywane po powrocie do sklepu z serwisu BM.
 *
 * @param array $request_data
 */
function fn_2lm_bm_return_to_shop_action(array $request_data)
{
    $order_id = !empty($request_data['OrderID']) ? $request_data['OrderID'] : 0;
    fn_2lm_bm_write_to_log_table($order_id, $request_data, '[RETURN]');

    $order_info = fn_get_order_info($order_id);
    if (empty($order_info)) {
        $msg = ": Unknown error (payment service returned data with wrong order ID ({$order_id}))";
        if (empty($order_id)) {
            $msg = ': Unknown error (payment service returned a data with no order ID)';
        }
        fn_set_notification('E', __('error'), __('reason_text') . $msg);
        fn_redirect(fn_url('checkout.checkout'));
    }

    if ($order_info['status'] === STATUS_INCOMPLETED_ORDER && fn_check_payment_script('bm.php', $order_id) &&
        !empty($order_info['payment_method']['processor_params']['statuses']['start'])
    ) {
        fn_change_order_status($order_id, $order_info['payment_method']['processor_params']['statuses']['start']);
    }

    fn_order_placement_routines('route', $order_id);
}


/**
 * Insert subscription data to db.
 *
 * @param int $processed_order_id
 * @param int $from_order_id
 */
function fn_2lm_bm_insert_subscription_data($processed_order_id, $from_order_id)
{
    $init_data = [
        'user_id' => Tygh::$app['session']['auth']['user_id'],
        'order_id' => $processed_order_id,
        'bm_order_id' => $from_order_id,
        'client_hash' => '',
        'status' => 'pending',
        'timestamp' => TIME,
        'created' => date('Y-m-d H:i:s'),
        'updated' => date('Y-m-d H:i:s'),
        'type' => 'AUTO',
    ];
    db_query(
        'INSERT INTO ?:bluemedia_subscriptions ?e ON DUPLICATE KEY UPDATE updated = ?s', $init_data, date('Y-m-d H:i:s')
    );
}


/**
 * @param array $response_data
 */
function fn_2lm_bm_update_subscription_data(array $response_data)
{
    db_query(
        'UPDATE ?:bluemedia_subscriptions SET redirect_url = ?s, remote_id = ?s WHERE order_id = ?s',
        $response_data['redirecturl'], $response_data['remoteID'], $response_data['orderID']
    );
}


/**
 * Start subscription order.
 *
 * @param array $form_data
 * @param array $processor_params    Ustawienia metody płatności
 * @param int $processed_order_id ID zamówienia
 * @param int $from_order_id    ID zamówienia bazowego
 *
 * @return array
 */
function fn_2lm_bm_start_subscription_order(array $form_data, array $processor_params, $processed_order_id, $from_order_id)
{
    $form_data['GatewayID'] = BLUEMEDIA_GATEWAY_ID_RECURRING;
    unset($form_data['Hash']);

    fn_2lm_bm_generate_and_add_hash($processor_params, $form_data);
    $response_parsed = fn_2lm_bm_start_background_transaction($processor_params, $form_data, BLUEMEDIA_PAYMENT_ACTON_PAYMENT);

    fn_2lm_bm_insert_subscription_data($processed_order_id, $from_order_id);

    fn_2lm_bm_write_to_log_table(
        $processed_order_id,
        ['url' => BLUEMEDIA_PAYMENT_ACTON_PAYMENT, 'data' => $form_data, 'response' => $response_parsed],
        '[PLACE_ORDER - Recurring: INIT_WITH_PAYMENT] new order: send form to the BM server'
    );

    return $response_parsed;
}