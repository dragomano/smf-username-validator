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
 * @version 0.2
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
		add_integration_function('integrate_profile_save', self::class . '::profileSave#', false, __FILE__);
		add_integration_function('integrate_general_mod_settings', self::class . '::generalModSettings#', false, __FILE__);
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

		$this->checkNameMinLength($username, $errors);
	}

	public function profileSave(array &$profile_vars, array &$post_errors): void
	{
		global $modSettings, $smcFunc, $txt;

		if (empty($modSettings['uv_min_name_length']))
			return;

		if ($smcFunc['strlen']($profile_vars['real_name']) < (int) $modSettings['uv_min_name_length']) {
			loadLanguage('UsernameValidator/');

			$txt['profile_error_uv_min_name_length_error'] = sprintf($txt['uv_min_name_length_error'], $modSettings['uv_min_name_length']);

			$post_errors[] = 'uv_min_name_length_error';
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
		$config_vars[] = ['int', 'uv_min_name_length', 'min' => 0];
	}

	private function checkNameMinLength(string $username, array &$errors): void
	{
		global $modSettings, $smcFunc;

		if (empty($modSettings['uv_min_name_length']))
			return;

		if ($smcFunc['strlen']($username) < (int) $modSettings['uv_min_name_length']) {
			loadLanguage('UsernameValidator/');

			$errors[] = ['lang', 'uv_min_name_length_error', false, [$modSettings['uv_min_name_length']]];
		}
	}
}
