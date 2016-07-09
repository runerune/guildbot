<?php

namespace None;

class Proxy {
	public static $login_url = 'https://account.arena.net/login.json';
	public static $twofactor_url = 'https://account.arena.net/login/wait/email.json';
	public static $leaderboard_url = 'https://leaderboards.guildwars2.com/en/eu/achievements/guild/%s?pjax=1';

	public $guild_name;

	public function __construct(Curl $leaderboard_handle) {
		$this->leaderboard_handle = $leaderboard_handle;
	}

	/**
	 * Set guild name to use when downlaoding leaderboards. User whose credentials are provided
	 * has to be a member of this guild.
	 * @param string $name
	 */
	public function setGuildName($name) {
		$this->guild_name = rawurlencode ($name);
	}

	/**
	 * Logs in to GW2 website
	 * @param string $login
	 * @param string $password
	 */
	public function login($login, $password) {
		$result = $this->leaderboard_handle->post(self::$login_url, [
			'_formName' => 'login',
			'email' => $login,
			'password' => $password,
			'redirect_uri' => 'https://leaderboards.guildwars2.com/login?valid=true&jsSession=true&source=%2Fen%2Feu%2Fachievements',
		], true);
	}

	/**
	 * Performs two-factor authentication using supplied code
	 * @param string $code
	 */
	public function authorize($code) {
		$result = $this->leaderboard_handle->post(self::$twofactor_url, [
			'_formName' => 'waitEmail',
			'otp' => (string)$code,
			'whitelist' => 'on',
		], true);
	}

	/**
	 * Downloads the leaderboard
	 * @return string leaderboard contents
	 */
	public function get() {
		// anet calls this "pjax" but it's just  normal html
		$filled_url = sprintf(self::$leaderboard_url, $this->guild_name);
		return $this->leaderboard_handle->get($filled_url);
	}

}