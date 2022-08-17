<?php

if (!defined('BOOTSTRAP')) {
	die('Access denied');
}

/**
 * Check if the addon can be enabled.
 * 
 * @param string $new_status
 * @param string $old_status
 * @param bool $on_install
 *
 * @return bool
 */
function fn_settings_actions_addons_2lm_bm(&$new_status, $old_status, $on_install) {
	if ($new_status === 'A') {
		$errors = fn_2lm_bm_check_php_environment();
		if (!empty($errors)) {
			$message = __('2lm_bm_error_cannot_enable_addon') . "<br />\n- ";
			fn_set_notification('E', __('error'), $message . implode("<br />\n- ", $errors));

			$new_status = 'D';
		}
	}

	return true;
}
