<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($mode === 'do_refund') {

        $order_id = $_REQUEST['refund']['order_id'];
        $order_info = fn_get_order_info($order_id, false, true, true, true);

        if (!empty($order_info) && !empty($_REQUEST['refund']['amount'])) {
            $refund_amount = fn_2lm_bm_format_amount($_REQUEST['refund']['amount']);

            if ($refund_amount > 0 && $refund_amount <= $order_info['total']) {
                $transaction_data = [
                    'RemoteID' => $_REQUEST['refund']['remote_id'],
                    'Amount' => fn_2lm_bm_format_amount($_REQUEST['refund']['amount']),
                ];

                $processor_params = fn_2lm_bm_get_processor_params($order_info['payment_id']);
                if (empty($processor_params)) {
                    fn_set_notification('E', __('2lm_bm'), __('2lm_bm_refund_not_possible_try_again_later'));
                    return [CONTROLLER_STATUS_OK, 'orders.details&order_id=' . $order_id];
                }
                $response_parsed = fn_2lm_bm_do_refund($processor_params, $transaction_data);

                $refund_log_message = '';
                if (array_key_exists('remoteOutID', $response_parsed)) {
                    $hash = fn_2lm_bm_generate_hash($processor_params, $response_parsed);
                    if ($response_parsed['hash'] === $hash) {
                        $refund_data = [
                            'user_id' => Tygh::$app['session']['auth']['user_id'],
                            'order_id' => $order_id,
                            'amount' => $refund_amount,
                            'remote_id' => $_REQUEST['refund']['remote_id'],
                            'remote_out_id' => $response_parsed['remoteOutID'],
                            'timestamp' => date('Y-m-d H:i:s'),
                        ];
                        fn_2lm_bm_save_order_refund_data($refund_data);

                        $refund_log_message = '[REFUND] OK';
                        fn_set_notification('N', __('2lm_bm'), __('2lm_bm_refund_has_been_done'));

                    } else {
                        $refund_log_message = '[REFUND] WRONG HASH';
                        fn_set_notification('E', __('2lm_bm'), __('2lm_bm_refund_wrong_hash'));
                    }
                } else {
                    $refund_log_message = '[REFUND] ERROR: ' . $response_parsed['description'];
                    fn_set_notification('E', __('2lm_bm'), __('2lm_bm_refund_not_possible_try_again_later'));
                }

//                fn_2lm_bm_write_to_log_table($order_id, [$response_parsed, 'raw_data' => $response_xml_data], $refund_log_message);
                fn_2lm_bm_write_to_log_table($order_id, $response_parsed, $refund_log_message);
            }
        } else {
            fn_set_notification('E', __('2lm_bm'), __('2lm_bm_refund_wrong_order_or_amount_data'));

            fn_2lm_bm_write_to_log_table($order_id, $_REQUEST, '[REFUND] ERROR: wrong order_id or empty amount value');
        }

        return [CONTROLLER_STATUS_OK, 'orders.details&order_id=' . $order_id];
    }
}

if ($mode === 'refund') {

    if (!empty($_REQUEST['amount']) && !empty($_REQUEST['remote_id']) && !empty($_REQUEST['order_id'])) {
        $payment_id = fn_2lm_bm_get_payment_id_from_order($_REQUEST['order_id']);
        $processor_params = fn_2lm_bm_get_processor_params($payment_id);
        $balanceResponse = fn_2lm_bm_get_balance($processor_params);
        if (array_key_exists('balance', $balanceResponse)) {
            if ($balanceResponse['balance'] < $_REQUEST['amount']) {
                fn_set_notification('W', __('2lm_bm'), __('2lm_bm_your_balance_is_not_enough'));

                $refund_balance_info = [
                    'balance' => $balanceResponse['balance'],
                    'message' => __('2lm_bm_your_balance_is_not_enough'),
                ];
                Registry::get('view')->assign('refund_balance_info', $refund_balance_info);
            }
        } elseif (array_key_exists('statusCode', $balanceResponse)) {
            fn_2lm_bm_write_to_log_table($_REQUEST['order_id'], $_REQUEST, '[REFUND] response: ' . $balanceResponse['description']);
        }

        $refund_order_info['order_id'] = $_REQUEST['order_id'];
        $refund_order_info['remote_id'] = $_REQUEST['remote_id'];
        $refund_order_info['amount'] = $_REQUEST['amount'];

        $order_info = fn_get_order_short_info($_REQUEST['order_id']);
        $refunds_sum = fn_2lm_bm_get_order_refunds_sum($_REQUEST['order_id']);
        $diff = $order_info['total'] - $refunds_sum;
        if ($refund_order_info['amount'] <= 0) {
            $refund_order_info['amount'] = 0.01;
        } elseif ($refund_order_info['amount'] > $diff) {
            $refund_order_info['amount'] = fn_2lm_bm_format_amount($diff);
        }

        Registry::get('view')->assign('refund_order_info', $refund_order_info);

    } else {
        fn_set_notification('E', __('2lm_bm'), __('2lm_bm_wrong_input_data'));
    }

} elseif ($mode === 'subscription') {
    // Zamówienie cykliczne

    $order_id = $_REQUEST['order_id'];

    if ($action === 'disactivate') {
        // Deaktywacja zamówienia abonamentowego

        $payment_id = fn_2lm_bm_get_payment_id_from_order($order_id);
        $processor_params = fn_2lm_bm_get_processor_params($payment_id);
        $response = fn_2lm_bm_subscription_deactivate($processor_params, $_REQUEST['order_id']);
        if (!empty($response)) {
            fn_set_notification('N', __('notice'), __('2lm_bm_msg_auto_deactivation_request_send'));
        } else {
            fn_set_notification('E', __('error'), __('2lm_bm_unexpected_error_occurred'));
        }

        return [CONTROLLER_STATUS_OK, 'orders.details&order_id=' . $order_id];

    } elseif ($action === 'get_payment') {
        // Pobierz kolejną należność

        if (Registry::get('addons.2lm_bm.order_subscription_enabled') === 'Y') {
            $form_data = fn_2lm_bm_prepare_form_data_for_get_payment_action($order_id);
            $form_data['ClientHash'] = db_get_field(
                'SELECT client_hash FROM ?:bluemedia_subscriptions WHERE order_id = ?i',
                $_REQUEST['order_id']
            );
            $payment_id = fn_2lm_bm_get_payment_id_from_order($order_id);
            $processor_params = fn_2lm_bm_get_processor_params($payment_id);
            fn_2lm_bm_generate_and_add_hash($processor_params, $form_data);
            $response_parsed = fn_2lm_bm_start_background_transaction($processor_params, $form_data, BLUEMEDIA_PAYMENT_ACTON_PAYMENT);
            if ($response_parsed['confirmation'] === 'NOTCONFIRMED') {
                fn_set_notification('E', __('error'), $response_parsed['reason']);
            }
            $void = true;
        }
    }

    return [CONTROLLER_STATUS_OK, 'orders.details&order_id=' . $order_id];
}
