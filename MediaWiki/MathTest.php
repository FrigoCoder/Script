<?php
require_once "Math.php";

class MathTest extends PHPUnit_Framework_TestCase {

	public function testCleanUp () {
		$temp = $this->renderer->getTemporaryDirectory();
		$file = "$temp/hello.txt";
		file_put_contents($file, "Hello World!");
		$this->renderer->cleanUp();
		if( is_file($temp) && is_file($file) ){
			$this->fail("Temporary directory and file is not deleted");
		}
		if( is_file($temp) ){
			$this->fail("Temporary directory not deleted");
		}
	}

	public function testCreateDirectory () {
		$dir = "test/" . uniqid("", true);
		$this->renderer->createDirectory($dir);
		if( !is_dir($dir) ){
			$this->fail("Directory was not created");
		}
		rmdir($dir);
		rmdir("test");
	}

	public function testGetAlternativeText () {
		$this->formula = "&\"'<>\r\n";
		$this->renderer = new MathRenderer($this->formula);
		$this->assertEquals("&amp;&quot;&#039;&lt;&gt;&#13;&#10;", $this->renderer->getAlternativeText());
	}

	public function testGetDocument () {
		$this->assertContains($this->formula, $this->renderer->getDocument());
	}

	public function testGetDvipngCommand () {
		global $wgDvipngCommand;
		$this->assertEquals($wgDvipngCommand, $this->renderer->getDvipngCommand());
	}

	public function testGetDvipngFile () {
		$temp = $this->renderer->getTemporaryDirectory();
		$this->assertEquals("$temp/doc.dvi", $this->renderer->getDvipngFile());
	}

	public function testGetHash () {
		$this->assertEquals(md5($this->formula), $this->renderer->getHash());
	}

	public function testGetHtmlCode () {
		$image = $this->renderer->getImageUrl();
		$alt = $this->renderer->getAlternativeText();
		$this->assertEquals("<img src=\"$image\" title=\"$alt\" alt=\"$alt\" class=\"tex\"/>", $this->renderer->getHtmlCode());
	}

	public function testGetImageDirectory () {
		global $wgMathDirectory;
		$this->assertFalse(is_dir($wgMathDirectory));
		$this->assertEquals($wgMathDirectory, $this->renderer->getImageDirectory());
		$this->assertTrue(is_dir($wgMathDirectory));
		rmdir($wgMathDirectory);
	}

	public function testgetImageFile () {
		$dir = $this->renderer->getImageDirectory();
		$hash = $this->renderer->getHash();
		$this->assertEquals("$dir/math-$hash.png", $this->renderer->getImageFile());
	}

	public function testGetImagePath () {
		global $wgMathPath;
		$this->assertEquals($wgMathPath, $this->renderer->getImagePath());
	}

	public function testGetImageUrl () {
		$path = $this->renderer->getImagePath();
		$hash = $this->renderer->getHash();
		$this->assertEquals("$path/math-$hash.png", $this->renderer->getImageUrl());
	}

	public function testGetLatexCommand () {
		global $wgLatexCommand;
		$this->assertEquals($wgLatexCommand, $this->renderer->getLatexCommand());
	}

	public function testGetLatexFile () {
		$temp = $this->renderer->getTemporaryDirectory();
		$this->assertEquals("$temp/doc.tex", $this->renderer->getLatexFile());
	}

	public function testGetTemporaryDirectory () {
	}

	public function testRender () {
	}

	public function testGenerateImageFile () {
	}

	public function testRun () {
		$expected = "Hello World!";
		$command = "echo $expected";
		$output = $this->renderer->run($command);
		$this->assertEquals(array($expected), $output);
		$this->runGoodCase("exit 0", 0);
		$this->runGoodCase("exit 1", 1);
		$this->runBadCase("exit 1", 0);
		$this->runBadCase("exit 2", 0);
		$this->runBadCase("exit 2", 1);
	}

	public function runGoodCase ($command, $tolerance) {
		$this->renderer->run($command, $tolerance);
	}

	public function runBadCase ($command, $tolerance) {
		try{
			$this->renderer->run($command, $tolerance);
			$this->fail("Exception not thrown for running bad case");
		} catch( MathRendererException $e ){
		}
	}

	public function testRunDvipng () {
	}

	public function testRunLatex () {
	}

	public function testSanitizeForMouseOver () {
		$input = "&\"'<>\n\r";
		$this->assertEquals("&amp;&quot;&#039;&lt;&gt;&#10;&#13;", $this->renderer->sanitizeForMouseOver($input));
		$input = array("&", "\"", "'", "<", ">", "\n", "\r");
		$this->assertEquals("&amp;&#10;&quot;&#10;&#039;&#10;&lt;&#10;&gt;&#10;&#10;&#10;&#13;", $this->renderer->sanitizeForMouseOver($input));
	}

	public function testSanitizeForPre () {
		$multi = array("1", "2", "3");
		$expected = "1\n2\n3";
		$this->assertEquals($expected, $this->renderer->sanitizeForPre($multi));
		$multi = array("&", "\"", "'", "<", ">", "\n");
		$expected = "&amp;\n&quot;\n&#039;\n&lt;\n&gt;\n\n";
		$this->assertEquals($expected, $this->renderer->sanitizeForPre($multi));
	}

	public function testValidateLength () {
		$this->renderer->validateLength();
		while( strlen($this->formula) <= 65536 ){
			$this->formula .= $this->formula;
		}
		$this->renderer = new MathRenderer($this->formula);
		try{
			$this->renderer->validateLength();
			$this->fail("Exception not thrown for long formula");
		} catch( MathRendererException $e ){
		}
	}

	public function testValidateTags () {
		$this->renderer->validateTags();
		$this->validateTagsBlacklisted("INCLUDE");
		$this->validateTagsBlacklisted('\expandafter');
	}

	protected function validateTagsBlacklisted ($formula) {
		$this->formula = $formula;
		$this->renderer = new MathRenderer($this->formula);
		try{
			$this->renderer->validateTags();
			$this->fail("Exception not thrown for blacklisted tag");
		} catch( MathRendererException $e ){
		}
	}

	protected $formula;
	protected $renderer;

	protected function setUp () {
		print getcwd();
		$this->formula = '\sum_{i=1}^{\infty} \frac{i}{(2i-1)!} = \frac{e}{2}';
		$this->renderer = new MathRenderer($this->formula);
		global $wgDvipngCommand;
		global $wgLaTexCommand;
		global $wgMathDirectory;
		global $wgMathPath;
		global $wgTmpDirectory;
		$wgDvipngCommand = "echo dvipng";
		$wgLatexCommand = "echo latex";
		$wgMathDirectory = "mathDir/" . uniqid("", true);
		$wgTmpDirectory = "tmpDir/" . uniqid("", true);
	}

	protected function tearDown () {
		$image = $this->renderer->getImageDirectory();
		if( is_dir($image) ){
			rmdir($image);
		}
		$temp = $this->renderer->getTemporaryDirectory();
		if( is_dir($temp) ){
			rmdir($temp);
		}
	}
}
?>
