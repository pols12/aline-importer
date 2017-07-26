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
	
	private $date;
	private $titre;
	private $type;
	private $nbPages;
	private $copyright;
	private $copyrightAddress;
	
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
		$zip = new \ZipArchive();
		$zip->open(self::LETTERS.$this->fileName);
		
		$xml = new \SimpleXMLElement($zip->getFromName('word/document.xml'));
		
		$paragraphes=$xml->xpath('//w:body/w:p');
		
		//Titre de la lettre
		$this->parseTitle($paragraphes);
		
		//Date de la lettre
		$this->parseDate($paragraphes);
		
		//Type, copyright, nbPages
		$this->parseCopyright($paragraphes);
		
		
//		echo $this->titre.PHP_EOL;
//		echo $this->date.PHP_EOL;
//		echo $this->type.PHP_EOL;
//		echo $this->nbPages.PHP_EOL;
//		echo $this->copyright.PHP_EOL;
//		echo $this->copyrightAddress.PHP_EOL;
	}
	
	private function readStyle(\SimpleXMLElement $p) {
		$styleNodes=$p->xpath('w:pPr[1]/w:pStyle');
		if(isset($styleNodes[0]))
			return $styleNodes[0]->attributes('w', true)->{'val'};
		return false;
	}
	
	/**
	 * Trouve un paragraphe avec un style donné parmi une liste de paragraphes.
	 * @param array $ps Liste de paragraphes dans laquelle faire la recherche.
	 * @param string $searched Le style du paragraphe cherché.
	 * @return \SimpleXMLElement Le paragraphe cherché.
	 * @throws \Exception Aucun paragraphe avec le style voulu n’a été trouvé.
	 */
	private function findStyle(array $ps, $searched){
		foreach ($ps as $p) {
			$style = $this->readStyle($p);
			if($style == $searched)
				return $p;
		}
		throw new \Exception("Le style $searched n’a pas été trouvé. Fichier : {$this->fileName}");
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

	private function parseCopyright($paragraphes) {
		try {
			$pCopyright = $this->findStyle($paragraphes, "Copyright");
			$pCopyrightText = $this->readText($pCopyright);
		} catch(\Exception $e) {
			$this->errors[]=$e->getMessage();
		}
		
		if(isset($pCopyrightText)) {
			$data = explode(', ', $pCopyrightText);
			
			try {
				switch (count($data)) {
					case 3:
						$this->copyrightAddress = $data[2];
					case 2:
						$this->type = $data[0];
						$nbPages_copyright = explode("p. ",$data[1]);
						$this->nbPages = trim($nbPages_copyright[0]);
						$this->copyright = trim($nbPages_copyright[1]);
						break;
					default:
						throw new \Exception ("Le copyright n’a pas le bon nombre de virgules. Fichier : {$this->fileName}");
				}
			} catch(\Exception $e) {
				$this->errors[]=$e->getMessage();
			}
		}
	}

	private function parseDate($paragraphes) {
		try {
			$pWithDate = $this->findStyle($paragraphes, "DateLettre");
			$this->date = $this->readText($pWithDate);
		} catch(\Exception $e) {
			$this->errors[]=$e->getMessage();
		}
	}

	private function parseTitle($paragraphes) {
		try {
			$styleTitre = $this->readStyle($paragraphes[0]);
			if($styleTitre == "Titre3") {
				$this->titre = $this->readText($paragraphes[0]);
			} else
				throw new \Exception("Le titre n’a pas été trouvé. Fichier : {$this->fileName}");
		} catch(\Exception $e) {
			$this->errors[]=$e->getMessage();
		}
	}

}
echo '<pre>';
$cleaner=new Volume5();
$cleaner->loop();