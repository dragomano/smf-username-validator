<?php

/**
 * Class-UsernameValidator.php
 *
 * @package Username Validator
 * @link https://github.com/dragomano/smf-username-validator
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2025 Bugo
 * @license https://opensource.org/licenses/MIT The MIT License
 *
 * @version 0.1
 */

namespace Bugo;

if (! defined('SMF'))
	die('No direct access...');

class UsernameValidator
{
	public function hooks(): void
	{
		add_integration_function('integrate_register_check', self::class . '::registerCheck#', false, __FILE__);
		add_integration_function('integrate_validate_username', self::class . '::validateUsername#', false, __FILE__);
		add_integration_function('integrate_general_mod_settings', __CLASS__ . '::generalModSettings#', false, __FILE__);
	}

	public function registerCheck(array $regOptions, array &$reg_errors): void
	{
		global $modSettings;

		if (empty($modSettings['uv_prevent_email_as_name']))
			return;

		if ($regOptions['username'] === $regOptions['email']) {
			loadLanguage('UsernameValidator/');

			$reg_errors[] = ['lang', 'uv_name_matches_email'];
		}
	}

	public function validateUsername(string $username, array &$errors): void
	{
		global $modSettings;

		if (empty($modSettings['uv_restricted_symbols']))
			return;

		if (strpbrk($username, (string) $modSettings['uv_restricted_symbols']) !== false) {
			$errors[] = ['lang', 'name_invalid_character'];
		}
	}

	public function generalModSettings(array &$config_vars): void
	{
		global $txt;

		loadLanguage('UsernameValidator/');

		if (isset($config_vars[0])) {
			$config_vars[] = ['title', 'uv_title'];
		}

		$config_vars[] = ['text', 'uv_restricted_symbols', 'subtext' => $txt['uv_restricted_symbols_desc']];
		$config_vars[] = ['check', 'uv_prevent_email_as_name'];
	}
}
