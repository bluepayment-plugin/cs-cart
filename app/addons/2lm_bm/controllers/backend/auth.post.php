<?php
/**************************************************************************
 *                                                                        *
 *   (C) 2016 2LM Sp. z o.o.                                              *
 *                                                                        *
 * This is a commercial software product which is protected by copyright. *
 * You can install and use it only if you have purchased a valid license. *
 **************************************************************************
 * PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT LOCATED    *
 * AT THE FOLLOWING URL: http://shop.2lm.pl/license-agreement-en/         *
 **************************************************************************/

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}
if ($mode == 'login') {/*
    if (!empty($_SESSION['auth']['user_id'])) {
        $data = fn_2lm_bm_get_license_information();
        fn_2lm_bm_parse_license_information($data);
        if (!fn_2lm_bm_check_valid()) {
            fn_update_addon_status('2lm_bm', 'D');
        }
    }*/
}