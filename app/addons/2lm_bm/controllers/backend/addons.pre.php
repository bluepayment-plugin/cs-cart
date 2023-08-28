<?php

use Tygh\Addons\SchemesManager;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

/**
 * @var string $mode
 * @var array  $auth
 */

if ($mode === 'refresh') {
    if (!empty($_REQUEST['addon'])) {
        $addon_id = $_REQUEST['addon'];

        if ($addon_id === '2lm_bm') {
            $new_addon_names = [
                'pl' => '2LM: Płatności online Autopay',
                'en' => '2LM: Autopay online payment',
                'cs' => '2LM: Platební systém Autopay',
            ];
            $new_addon_descrs = [
                'pl' => 'Obsługa bramki płatności online Autopay. Copyright &copy;&nbsp;<strong><a href="https://www.2lm.pl/" target="_blank">2LM Sp. z o.o.</a></strong>',
                'en' => 'Autopay Payment Gateway. Copyright &copy;&nbsp;<strong><a href="https://www.2lm.pl/" target="_blank">2LM Sp. z o.o.</a></strong>',
                'cs' => 'Podpora platební brány Autopay. Copyright &copy;&nbsp;<strong><a href="https://www.2lm.pl/" target="_blank">2LM Sp. z o.o.</a></strong>',
            ];

            $addon_scheme = SchemesManager::getScheme($addon_id);
            foreach ($addon_scheme->getLanguages() as $lang_code => $lang_data) {
                $addon_name = db_get_field(
                    'SELECT name FROM ?:addon_descriptions WHERE addon = ?s AND lang_code = ?s',
                    $addon_id,
                    $lang_code
                );
                if ($addon_name !== $new_addon_names[$lang_code]) {
                    db_query(
                        'UPDATE ?:addon_descriptions SET name = ?s, description = ?s WHERE addon = ?s AND lang_code = ?s',
                        $new_addon_names[$lang_code],
                        $new_addon_descrs[$lang_code],
                        $addon_id,
                        $lang_code
                    );
                }
            }
        }
    }

}