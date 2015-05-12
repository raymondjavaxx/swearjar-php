<?php

require_once 'swearjar/Tester.php';
use \swearjar\Tester;

class TesterTest extends \PHPUnit_Framework_TestCase {

	public $tester;

	public function setUp() {
		$this->tester = new Tester;
	}

	public function testProfane() {
		$this->assertTrue($this->tester->profane('fuck you john doe'));
		$this->assertTrue($this->tester->profane('FUCK you john doe'));
		$this->assertFalse($this->tester->profane('i love you john doe'));
	}

	public function testScorecard() {
		$scorecard = $this->tester->scorecard('fuck you john doe');
		$expected = array('sexual' => 1);
		$this->assertEquals($expected, $scorecard);

		$scorecard = $this->tester->scorecard('fuck you john doe bitch');
		$expected = array('sexual' => 1, 'insult' => 1);
		$this->assertEquals($expected, $scorecard);
	}

	public function testCensor() {
		$text = $this->tester->censor('John Doe has a massive hard on he is gonna use to fuck everybody');
		$expected = 'John Doe has a massive **** ** he is gonna use to **** everybody';
		$this->assertEquals($expected, $text);

		$text = $this->tester->censor('John Doe has a massive hard on he is gonna use to fuck everybody in the ass', true);
		$expected = 'John Doe has a massive h*** ** he is gonna use to f*** everybody in the a**';
		$this->assertEquals($expected, $text);
	}

	public function testEdgeCases() {
		$result = $this->tester->censor("Assasin's Creed Ass");
		$expected = "Assasin's Creed ***";
		$this->assertEquals($expected, $result);
	}
}
