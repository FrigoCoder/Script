<?php

/**
 * LaTeX Rendering Class for MediaWiki
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------
 * LaTeX Rendering Class Copyright (C)
 * Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 * Steve Mayer
 * Frigo <frigocoder@gmail.com>
 **/
class MathRenderer {

	public static function renderMath ($formula) {
		try{
			$renderer = new MathRenderer($formula);
			return $renderer->render();
		} catch( MathRendererException $e ){
			return $e->getMessage();
		}
	}

	public function MathRenderer ($formula) {
		$this->formula = $formula;
		$this->uniq = uniqid("", true);
	}

	protected $document;
	protected $formula;
	protected $hash;
	protected $imageDirectory;
	protected $imageFile;
	protected $imagePath;
	protected $imageUrl;
	protected $temporaryDirectory;
	protected $uniq;

	public function cleanUp () {
		$temp = $this->getTemporaryDirectory();
		$this->deleteDirectory($temp);
	}

	public function createDirectory ($directory) {
		if( !is_dir($directory) ){
			mkdir($directory, 0777, true);
		}
	}

	public function deleteDirectory ($directory) {
		if( !is_dir($directory) ){
			return;
		}
		foreach( glob("$directory/*.*") as $file ){
			unlink($file);
		}
		rmdir($directory);
	}

	public function getAlternativeText () {
		return $this->sanitizeForMouseOver($this->formula);
	}

	public function getDocument () {
		if( !isset($this->document) ){
			$formula = $this->formula;
			$this->validateLength();
			$this->validateTags();
			$this->document = "";
			$this->document .= "\\documentclass[10pt,a4paper,fleqn]{article}\n";
			$this->document .= "\\usepackage[utf8]{inputenc}\n";
			$this->document .= "\\usepackage{amsmath}\n";
			$this->document .= "\\usepackage{amsfonts}\n";
			$this->document .= "\\usepackage{amssymb}\n";
			$this->document .= "\\pagestyle{empty}\n";
			$this->document .= "\\begin{document}\n";
			$this->document .= "\\everymath{\displaystyle}\n";
			$this->document .= "\$$formula\$\n";
			$this->document .= "\\end{document}\n";
		}
		return $this->document;
	}

	public function getDvipngCommand () {
		global $wgDvipngCommand;
		return $wgDvipngCommand;
	}

	public function getDvipngFile () {
		$temp = $this->getTemporaryDirectory();
		return "$temp/doc.dvi";
	}

	public function getHash () {
		if( !isset($this->hash) ){
			$this->hash = md5($this->formula);
		}
		return $this->hash;
	}

	public function getHtmlCode () {
		$image = $this->getImageUrl();
		$alt = $this->getAlternativeText();
		return "<img src=\"$image\" title=\"$alt\" alt=\"$alt\" class=\"tex\"/>";
	}

	public function getImageDirectory () {
		if( !isset($this->imageDirectory) ){
			global $wgMathDirectory;
			$this->imageDirectory = $wgMathDirectory;
			$this->createDirectory($this->imageDirectory);
		}
		return $this->imageDirectory;
	}

	public function getImageFile () {
		if( !isset($this->imageFile) ){
			$dir = $this->getImageDirectory();
			$hash = $this->getHash();
			$this->imageFile = "$dir/math-$hash.png";
		}
		return $this->imageFile;
	}

	public function getImagePath () {
		if( !isset($this->imagePath) ){
			global $wgMathPath;
			$this->imagePath = $wgMathPath;
		}
		return $this->imagePath;
	}

	public function getImageUrl () {
		if( !isset($this->imageUrl) ){
			$path = $this->getImagePath();
			$hash = $this->getHash();
			$this->imageUrl = "$path/math-$hash.png";
		}
		return $this->imageUrl;
	}

	public function getLatexCommand () {
		global $wgLaTexCommand;
		return $wgLaTexCommand;
	}

	public function getLatexFile () {
		$temp = $this->getTemporaryDirectory();
		return "$temp/doc.tex";
	}

	public function getTemporaryDirectory () {
		if( !isset($this->temporaryDirectory) ){
			global $wgTmpDirectory;
			$dir = $wgTmpDirectory;
			$hash = $this->getHash();
			$uniq = $this->uniq;
			$this->temporaryDirectory = "$dir/$hash.$uniq";
			$this->createDirectory($this->temporaryDirectory);
		}
		return $this->temporaryDirectory;
	}

	public function render () {
		$this->generateImageFile();
		return $this->getHtmlCode();
	}

	public function generateImageFile () {
		if( is_file($this->getImageFile()) ){
			return;
		}
		file_put_contents($this->getLatexFile(), $this->getDocument());
		$this->runLatex();
		$this->runDvipng();
		$this->cleanUp();
	}

	public function run ($command, $tolerance = 0) {
		$output = "";
		$result = 0;
		exec($command, $output, $result);
		if( $result > $tolerance ){
			$command = $this->sanitizeForPre($command);
			$output = $this->sanitizeForPre($output);
			$error = "";
			$error .= "<strong class=\"error\">Command returned with error code $result</strong>\n";
			$error .= "<textarea>$this->document</textarea>\n";
			$error .= "<textarea>$output</textarea>\n";
			$error .= "<textarea>$command</textarea>\n";
			throw new MathRendererException($error);
		}
		return $output;
	}

	public function runDvipng () {
		$density = 160;
		$gamma = 1.0;
		$dvipng = $this->getDvipngCommand();
		$dvi = $this->getDvipngFile();
		$image = $this->getImageFile();
		return $this->run("\"$dvipng\" \"$dvi\" -o \"$image\" -bg Transparent -D $density --freetype --gamma $gamma -T tight --truecolor -v -z 9 2>&1");
	}

	public function runLatex () {
		$latex = $this->getLatexCommand();
		$temp = $this->getTemporaryDirectory();
		$tex = $this->getLatexFile();
		return $this->run("\"$latex\" --interaction=nonstopmode --aux-directory=\"$temp\" --output-directory=\"$temp\" \"$tex\" 2>&1", 0);
	}

	public function sanitizeForMouseOver ($input) {
		$text = is_array($input) ? implode("\n", $input) : $input;
		$text = htmlentities($text, ENT_QUOTES);
		$text = str_replace("\n", "&#10;", $text);
		$text = str_replace("\r", "&#13;", $text);
		return $text;
	}

	public function sanitizeForPre ($input) {
		$text = is_array($input) ? implode("\n", $input) : $input;
		$text = htmlentities($text, ENT_QUOTES);
		return $text;
	}

	public function validateLength () {
		$maxLength = 65536;
		if( strlen($this->formula) > $maxLength ){
			throw new MathRendererException("<strong class=\"error\">Formula too long</strong>");
		}
	}

	public function validateTags () {
		$blackList = array("include", "def", "command", "loop", "repeat", "open", "toks", "output", "input", "catcode", "name", "^^", "\\every", "\\errhelp", "\\errorstopmode", "\\scrollmode", "\\nonstopmode", "\\batchmode", "\\read", "\\write", "csname", "\\newhelp", "\\uppercase", "\\lowercase", "\\relax", "\\aftergroup", "\\afterassignment", "\\expandafter", "\\noexpand", "\\special");
		foreach( $blackList as $tag ){
			if( stristr($this->formula, $tag) ){
				throw new MathRendererException("<strong class=\"error\">Blacklisted tag: <code>$tag</code></strong>");
			}
		}
	}
}

class MathRendererException extends Exception {
}
?>
