<?php

namespace AlineImporter\Cleaner;

//use PhpOffice\PhpWord\IOFactory;
//use PhpOffice\PhpWord\PhpWord;

include_once __DIR__.'/Letter.php';

/**
 * Analyse syntaxique et nettoyage des lettres du volume 5.
 *
 * @author pols12
 */
class Volume5 {
	const LETTERS = __DIR__.'/../../../assets/letters.docx/';
	
	protected $letters=[];

	public function loop() {
		$files = array_diff(scandir(self::LETTERS), ['.','..']);
		
		foreach ($files as $file){
			$this->letters[] = new Letter(self::LETTERS.$file);
			break;
		}
	}
}
echo '<pre>';
$cleaner=new Volume5();
$cleaner->loop();