<?php

namespace swearjar;

use \Symfony\Component\Yaml\Yaml;

/**
 * Profanity tester class.
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
    function __construct($file = null) {
        if ($file === null) {
            $file = __DIR__ . '/config/en.yml';
        }

        $this->loadFile($file);
    }

    /**
     * Loads a YAML file containing rules for matching profanity.
     *
     * @param string $path  YAML ruleset file path
     * @return void
     */
    function loadFile($path) {
        $this->_matchers = Yaml::parse(file_get_contents($path));
    }

    /**
     * Scans `$text` looking for profanity. The callback is invoked on
     * each instance of profanity.
     *
     * The signature of `$callback` is:
     * 
     *    function ($word, $index, $types) { ... }
     * 
     * Where `$word` is the possible profane word, `$index` is the offset of the word
     * in the text, and `$types` is a list of tags for the word.
     * 
     * @param string $text
     * @param Closure $callback
     * @return void
     */
    function scan($text, \Closure $callback) {
        preg_match_all('/\w+/u', $text, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            list($word, $index) = $match;

            $key = mb_strtolower($word);

            if (array_key_exists($key, $this->_matchers['simple'])) {
                if ($callback->__invoke($word, $index, $this->_matchers['simple'][$key]) === false) {
                    return;
                }
            }
        }

        foreach ($this->_matchers['regex'] as $regex => $types) {
            preg_match_all('/' . $regex . '/i', $text, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[0] as $match) {
                list($word, $index) = $match;
                if ($callback->__invoke($word, $index, $types) === false) {
                    return;
                }
            }
        }
    }

    /**
     * Returns true if `$text` contains profanity
     *
     * @param string $text
     * @return boolean
     */
    function profane($text) {
        $profane = false;

        $this->scan($text, function($word, $index, $types) use (&$profane) {
            $profane = true;
            return false;
        });

        return $profane;
    }

    /**
     * Analyzes `$text` and generates a report of the type of profanity found.
     *
     * @param string $text
     * @return array
     */
    function scorecard($text) {
        $scorecard = array();

        $this->scan($text, function($word, $index, $types) use (&$scorecard) {
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
     * Get the swear words in a string
     * @param string $text The string to test
     * @return array An array of swear words (an entry may appear more than once)
     */
    function getSwearWords($text) {
        $output = array();
        
        $this->scan($text, function($word) use (&$output) {
            $output[] = $word;
            return true;
        });

        return $output;
    }
    
    /**
     * Get the swear words in a string, keyed by the type of profanity is
     * @param string $text The string to test
     * @return array An associative array of swear words by type, e.g.<pre>
     *  [
     *      type => [
     *          swearword
     *      ]
     *  ]</pre><br/>. An entry may appear more than once
     */
    function getSwearWordsByType($text) {
        $output = array();

        $this->scan($text, function($word, $index, $types) use (&$output) {
            foreach ($types as $type) {
                $output[$type][] = $word;
            }
            return true;
        });

        return $output;
    }

    /**
     * Scans `$text` and censors profanity.
     *
     * @param string $text
     * @param boolean $hint (optional)
     * @return string
     */
    function censor($text, $hint = false) {
        $censored = $text;

        $offset = $hint ? 1 : 0;

        $this->scan($text, function($word, $index, $types) use (&$censored, $offset) {
            $censoredWord = preg_replace('/\S/', '*', $word);
            $censored = mb_substr($censored, 0, $index + $offset) . mb_substr($censoredWord, $offset) . mb_substr($censored, $index + mb_strlen($word));
            return true;
        });

        return $censored;
    }

}
