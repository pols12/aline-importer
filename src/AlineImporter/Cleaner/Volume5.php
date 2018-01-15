<?php

namespace AlineImporter\Cleaner;

include_once __DIR__.'/Letter.php';

/**
 * Analyse syntaxique et nettoyage des lettres du volume 5.
 *
 * @author pols12
 */
class Volume5 {
	const LETTERS = __DIR__.'/../../../assets/letters.docx/';
	
	protected $letters=[];

	public function handleAllLetters() {
		$files = array_diff(scandir(self::LETTERS), ['.','..']);
		
		foreach ($files as $file){
			$this->letters[] = new Letter($file);
		}
	}
	
	public function __toString() {
		return
			'"fichier","date","titre","type","nbPages","copyright",'
		. '"copyrightAddress","incipit","Personnes","Thèmes"'.PHP_EOL
			.implode("", $this->letters);
	}
}
echo '<pre>';
$cleaner=new Volume5();
$cleaner->handleAllLetters();
echo $cleaner;