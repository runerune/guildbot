<?php

namespace None;

class Parser {
	public static $regexp = '/after\-arrow\">\s+([\d\%]+)\s+.*?name text\">\s+(.*?)\s+<\/td.*?after\-arrow\">\s+(\d+)\s+.*?world text\">\s+(.*?)\s+</i';

	public $whoami;

	/**
	 * Sets username that will be ignored when parsing user list. Use this to avoid including
	 * the "bot" in the result.
	 * @param unknown $username
	 */
	public function setIgnoreUser($username) {
		$this->whoami = $username;
	}

	/**
	 * Parse contents of GW2 leaderboards to extract data about players
	 * @param string $raw_text html contents of leaderboards table
	 * @return array extracted data
	 */
	public function parse($raw_text) {
		$result = [];

		$raw_text = str_replace(["\r", "\n"], '', $raw_text);
		preg_match_all(self::$regexp, $raw_text, $players);

		unset($players[2][0]);
		// first row is always the user whose credentials are being utilized, so discard it

		foreach($players[2] as $key => $value) {
			// discard the "bot" again to avoid skewing results
			if($value == $this->whoami) continue;

			$result[] = [
				'name' => mb_convert_encoding($value, 'UTF-8', 'HTML-ENTITIES'),
				'server' => mb_convert_encoding($players[4][$key], 'UTF-8', 'HTML-ENTITIES'),
				'ap' => $players[3][$key],
				'rank' => $players[1][$key],
			];
		}

		return $result;
	}

	/**
	 * Converts objects to JSON
	 * @param any $data
	 * @return string
	 */
	public function toJson($data) {
		return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
	}
}