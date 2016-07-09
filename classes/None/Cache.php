<?php

namespace None;

class Cache {
	protected $ttl;
	protected $cache_dir;

	public function __construct($cache_dir, $ttl) {
		$this->ttl = (int)$ttl;
		$this->cache_dir = $cache_dir;
	}

	/**
	 * Puts any string in cache. May be binary string.
	 * @param string $name
	 * @param string $data
	 */
	public function store($name, $data) {
		file_put_contents($this->cache_dir.DIRECTORY_SEPARATOR.$name, $data);
	}

	/**
	 * Checks if cache entry is not outdated
	 * @param string $name
	 * @return boolean false if outdated
	 */
	public function check($name) {
		$filename = $this->cache_dir.DIRECTORY_SEPARATOR.$name;
		if(!file_exists($filename)) return false;

		$modification_date = filemtime($filename);
		return ($modification_date+$this->ttl > time());
	}

	/**
	 * Gets a cached entry
	 * @param string $name
	 * @return string contents of cached entry
	 */
	public function get($name) {
		return file_get_contents($this->cache_dir.DIRECTORY_SEPARATOR.$name);
	}
}