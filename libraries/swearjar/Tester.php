<?php

namespace swearjar;

use \Symfony\Component\Yaml\Yaml;

/**
 * undocumented class
 *
 * @package swearjar
 */
class Tester {

	protected $_matchers = array();

	/**
	 * Constructor
	 *
	 * @param string $file 
	 */
	public function __construct($file = null) {
		if ($file === null) {
			$file = __DIR__ . '/config/en.yml';
		}

		$this->loadFile($file);
	}

	/**
	 * undocumented function
	 *
	 * @param string $path 
	 * @return void
	 */
	public function loadFile($path) {
		$this->_matchers = Yaml::parse($path);
	}

	/**
	 * undocumented function
	 *
	 * @param string $text 
	 * @param Closure $callback 
	 * @return void
	 */
	public function scan($text, \Closure $callback ) {
		preg_match_all('/\b[a-zA-Z-]+\b/', $text, $matches);
		foreach ($matches[0] as $word) {
			$types = isset($this->_matchers['simple'][strtolower($word)])
				? $this->_matchers['simple'][strtolower($word)]
				: false;

			if ($types) {
				if ($callback->__invoke($word, $types) === false) {
					return;
				}
			}
		}

		foreach ($this->_matchers['regex'] as $regex => $types) {
			preg_match_all('/' . $regex . '/i', $text, $matches);
			foreach ($matches[0] as $word) {
				if ($callback->__invoke($word, $types) === false) {
					return;
				}
			}
		}
	}

	/**
	 * Returns true if $text contains profanity
	 *
	 * @param string $text 
	 * @return boolean
	 */
	public function profane($text) {
		$profane = false;

		$this->scan($text, function($word, $types) use (&$profane) {
			$profane = true;
			return false;
		});

		return $profane;
	}

	/**
	 * undocumented function
	 *
	 * @param string $text 
	 * @return array
	 */
	public function scorecard($text) {
		$scorecard = array();

		$this->scan($text, function($word, $types) use (&$scorecard) {
			foreach ($types as $type) {
				if (isset($scorecard[$type])) {
					$scorecard[$type] += 1;
				} else {
					$scorecard[$type] = 1;
				}
			}

			return true;
		});

		return $scorecard;
	}

	/**
	 * undocumented function
	 *
	 * @param string $text 
	 * @return string
	 */
	public function censor($text) {
		$censored = $text;

		$this->scan($text, function($word, $types) use (&$censored) {
			$censoredWord = preg_replace('/\S/', '*', $word);
			$censored = str_ireplace($word, $censoredWord, $censored);
			return true;
		});

		return $censored;
	}
}
