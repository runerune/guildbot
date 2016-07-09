<?php

namespace None;

class Curl {
	protected $curl_handle;
	protected $auth_cookie;

	public static $curl_ua = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36';

	public function __construct() {
		session_start();
		$this->curl_handle = curl_init();

		curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl_handle, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl_handle, CURLOPT_USERAGENT, self::$curl_ua);
		curl_setopt($this->curl_handle, CURLOPT_HEADER, 1);
	}

	public function __destruct() {
		curl_close($this->curl_handle);
	}

	/**
	 * Executes a POST request
	 * @param string $url
	 * @param array $data postdata to pass along with request
	 * @param boolean $json if true, uses application/json content type instead of multipart/form-data
	 * @return string response body
	 */
	public function post($url, $data=null, $json=false) {
		$options = [
			'Referer: https://account.arena.net/', // has to be set, 400 bad request otherwise
		];

		// set a cookie if we have any
		// it's required to pass sms/email code auth
		if(isset($_SESSION['anet_cookie'])) $options[] = 'Cookie: s='.$_SESSION['anet_cookie'];

		curl_setopt($this->curl_handle, CURLOPT_POST, 1);
		curl_setopt($this->curl_handle, CURLOPT_URL, $url);

		if($data) {
			if($json) {
				curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, json_encode($data));
				$options[] = 'Content-Type: application/json; charset=UTF-8';
			} else {
				curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, http_build_query($data));
			}
		}

		curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, $options);

		return $this->handle(curl_exec($this->curl_handle));
	}

	/**
	 * Executes a GET request
	 * @param string $url
	 * @param array $data GET query parameters, will be concatenated to ?foo=bar&baz=baf...
	 * @return string response body
	 */
	public function get($url, $data=null) {
		$options = [];

		if(isset($_SESSION['anet_cookie'])) $options[] = 'Cookie: s='.$_SESSION['anet_cookie'];

		curl_setopt($this->curl_handle, CURLOPT_POST, 0);
		curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, $options);

		if($data) {
			curl_setopt($this->curl_handle, CURLOPT_URL, $url.'?'.http_build_query($data));
		} else {
			curl_setopt($this->curl_handle, CURLOPT_URL, $url);
		}

		return $this->handle(curl_exec($this->curl_handle));
	}

	/**
	 * Handles curl response and sets cookies, performs error checks
	 * @param string $response from curl
	 * @throws \Exception
	 * @return string response body
	 */
	protected function handle($response) {
		$curl_error = curl_errno($this->curl_handle);
		if($curl_error) throw new \Exception('Client error '.$curl_error);

		preg_match('/Set\-Cookie: s=([a-z0-9\-]+);/im', $response, $cookie_match);
		if($cookie_match) {
			$_SESSION['anet_cookie'] = $cookie_match[1];
		}

		$response_status = curl_getinfo($this->curl_handle, CURLINFO_HTTP_CODE);
		if($response_status !== 200) throw new \Exception('Invalid response status: '.$response_status);
		if(!$response) throw new \Exception('Response is empty');

		if(preg_match('/login\/wait/i', $response)) {
			if(!$_SESSION['anet_cookie']) throw new \Exception('Authorization required but no cookie found, aborting');
			throw new \Exception('Authorization required, use authorize.php?code=12345 with email/sms code');
		}

		return $response;
	}

}