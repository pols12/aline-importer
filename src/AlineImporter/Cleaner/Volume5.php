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
	
	private $date;
	private $titre;
	private $errors;

	public function loop() {
		$files = array_diff(scandir(self::LETTERS), ['.','..']);
		
		foreach ($files as $file){
			$this->fileName = $file;
			$this->updateData();
//			break;
		}
		print_r($this->errors);
	}
	
	private function updateData() {
//		$this->phpWord = IOFactory::load($this->fileName);
		$zip = new \ZipArchive();
		$zip->open(self::LETTERS.$this->fileName);
		
		$xml = new \SimpleXMLElement($zip->getFromName('word/document.xml'));
		
		$paragraphes=$xml->xpath('//w:body/w:p');
		
		//Titre de la lettre
		try {
			$styleTitre = $this->readStyle($paragraphes[0]);
			if($styleTitre == "Titre3") {
				$this->titre = $this->readText($paragraphes[0]);
			} else
				throw new \Exception("Le titre n’a pas été trouvé. Fichier : {$this->fileName}");
		} catch(\Exception $e) {
			$this->errors[]=$e->getMessage();
		}
		
		//Date de la lettre
		try {
			$pWithDate = $this->findStyle($paragraphes, "DateLettre");
			$this->date = $this->readText($pWithDate);
		} catch(\Exception $e) {
			$this->errors[]=$e->getMessage();
		}	
		
//		echo $this->titre.PHP_EOL;
//		echo $this->date.PHP_EOL;
	}
	
	private function readStyle(\SimpleXMLElement $p) {
		$styleNodes=$p->xpath('w:pPr[1]/w:pStyle');
		if(isset($styleNodes[0]))
			return $styleNodes[0]->attributes('w', true)->{'val'};
		return false;
	}
	
	private function findStyle(array $ps, $searched){
		foreach ($ps as $p) {
			$style = $this->readStyle($p);
			if($style == $searched)
				return $p;
		}
		throw new \Exception("La date n’a pas été trouvée. Fichier : {$this->fileName}");
	}
	
	private function readText(\SimpleXMLElement $p) {
		$text='';
		foreach ($p->xpath('w:r') as $r)
			if(isset($r->xpath('w:t')[0]))
				$text.=$r->xpath('w:t')[0];
		return $text;
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