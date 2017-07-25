<?php

namespace AlineImporter\Cleaner;

//use PhpOffice\PhpWord\IOFactory;
//use PhpOffice\PhpWord\PhpWord;

include_once __DIR__.'/../../../vendor/autoload.php';

/**
 * Analyse syntaxique et nettoyage des lettres du volume 5.
 *
 * @author pols12
 */
class Volume5 {
	const LETTERS = __DIR__.'/../../../assets/letters.docx/';

	protected $fileName;
	/** @var PhpWord */
//	protected $phpWord;
	
	public function loop() {
		$files = array_diff(scandir(self::LETTERS), ['.','..']);
		
		foreach ($files as $file){
			$this->fileName = $file;
			$this->updateData();
			break;
		}
	}
	
	private function updateData() {
//		$this->phpWord = IOFactory::load($this->fileName);
		$zip = new \ZipArchive();
		$zip->open(self::LETTERS.$this->fileName);
		
		$xml = new \SimpleXMLElement($zip->getFromName('word/document.xml'));
		
		$p=$xml->xpath('//w:body/w:p[1]')[0];
		
		//Titre de la lettre
		
		$styleTitre = $p->xpath('w:pPr[1]/w:pStyle')[0]
				->attributes('w', true)->{'val'};
		if($styleTitre == "Titre3") {
			$titre='';
			foreach ($p->xpath('w:r') as $r) {
				if(isset($r->xpath('w:t')[0]))
					$titre.=$r->xpath('w:t')[0];
			}
			echo $titre.PHP_EOL;
		} else
			throw new Exception("Le titre n’a pas été trouvé. Fichier : {$this->fileName}");
		
		
		
	}
	
	/**
	 * Utilise l’API SEARCH pour rechercher l’item qui correspond aux données
	 * passées en paramètre.
	 * @return int ID de l’item.
	 */
	private function findItem(array $data) {
		
	}
}
echo '<pre>';
$cleaner=new Volume5();
$cleaner->loop();