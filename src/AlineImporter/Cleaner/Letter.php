<?php

namespace AlineImporter\Cleaner;

include_once __DIR__.'/class.Diff.php';

/**
 * Représentation des données d’une lettre.
 *
 * @author pols12
 */
class Letter {
	private $errors;
	
	private $fileName;
	
	private $date;
	private $titre;
	private $type;
	private $nbPages;
	private $copyright;
	private $copyrightAddress;
	private $incipit;
	private $fields;
	
	public function __construct($fileName) {
		$this->fileName = $fileName;
		$this->updateData();
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	/**
	 * Donne une représentation CSV des propriétés.
	 * @return string Une ligne de CSV
	 */
	public function __toString() {
		$handle = fopen('php://memory', 'w');
		$data = [$this->fileName,$this->date,$this->titre,$this->type,$this->nbPages,
				$this->copyright,$this->copyrightAddress,$this->incipit];
		fputcsv($handle, $data);
		
		fseek($handle, 0);
		return stream_get_contents($handle);
	}
	
	private function updateData() {
		$zip = new \ZipArchive();
		$zip->open(Volume5::LETTERS.$this->fileName);
		
		$xml = new \SimpleXMLElement($zip->getFromName('word/document.xml'));
		
		$paragraphes=$xml->xpath('//w:body/w:p');
		
		//Titre de la lettre
		$this->parseTitle($paragraphes);
		
		//Date de la lettre
		$this->parseDate($paragraphes);
		
		//Type, copyright, nbPages
		$this->parseCopyright($paragraphes);
		
		//Incipit
		$this->parseIncipit($paragraphes);
		
		//Champs
		$this->readFields($xml);
		if($this->parseFields())
			$this->parseIndexFieldValues();
		
//		echo $this->titre.PHP_EOL;
//		echo $this->date.PHP_EOL;
//		echo $this->type.PHP_EOL;
//		echo $this->nbPages.PHP_EOL;
//		echo $this->copyright.PHP_EOL;
//		echo $this->copyrightAddress.PHP_EOL;
//		echo $this->incipit.PHP_EOL;
//		print_r($this->fields);
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
	
	/**
	 * Donne le contenu sans balisage d’un paragraphe.
	 * @param \SimpleXMLElement $p Paragraphe dans lequel lire le texte.
	 * @return string Contenu textuel du paragraphe.
	 */
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
			$this->date = trim($this->readText($pWithDate));
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

	private function parseIncipit(array $paragraphes) {
		$datePKey = array_search($this->findStyle($paragraphes, "DateLettre"), $paragraphes);
		//On cherche le premier paragraphe non vide.
		$i=-1;
		foreach ($paragraphes as $p) {
			$i++;
			if($i <= $datePKey)
				continue;
			$text=$this->readText($p);
			if(!empty($text))
				break;
		}
		$fullIncipit = substr($text, 0, 156);
		$this->incipit = substr($fullIncipit, 0, strrpos($fullIncipit, ' '));
/*		$diff=\Diff::compare("Rien de bien neuf", "Rien de neuf", true);
		$score=0;
		foreach ($diff as $letter) {
			switch($letter[1]) {
				case \Diff::UNMODIFIED:
					$score++;
					break;
				case \Diff::DELETED:
				case \Diff::INSERTED:
					$score--;
			}
		} */
	}

	private function readFields(\SimpleXMLElement $xml) {
		$this->fields=[];
		$fieldBegins = $xml->xpath('//w:fldChar[@w:fldCharType="begin"]');
		foreach ($fieldBegins as $fieldBegin) {
			$nextRs = $fieldBegin->xpath('../following-sibling::w:r');
			$field='';
			try {
				foreach ($nextRs as $r) {
					if( isset($r->xpath('w:instrText')[0]) )
						$field.=$r->xpath('w:instrText')[0];
					elseif( isset($r->xpath('w:fldChar[@w:fldCharType="end"]')[0]) )
						break;
					elseif( isset($r->xpath('w:fldChar[@w:fldCharType="separate"]')[0])
							|| isset($r->xpath('w:t')[0]) )
						continue;
					else
						throw new \Exception("Ni contenu ni fin de champ rencontré. Balise : {$r->asXML()}\n. Fichier : {$this->fileName}");
				}
				$this->fields[]=$field;
			} catch(\Exception $e) {
				$this->errors[]=$e->getMessage();
			}
		}
	}

	private function parseFields() {
		$nbEditedFields=0;
		foreach ($this->fields as $field) {
			if(substr($field, 0, 3) !== 'xe ')
				continue;
			$firstQuotePos = mb_strpos($field, '"');
			$secondQuotePos = mb_strpos($field, '"', $firstQuotePos+1);
			$fieldValue = mb_substr($field, $firstQuotePos+1, ($secondQuotePos-$firstQuotePos-1));
			
			if(FALSE === mb_strpos($field, '\\f')) { // si c’est l’index par défaut
				$indexName='nominum';
			} else {
				$lastQuotePos = mb_strrpos($field, '"');
				$secondLastQuotePos = mb_strrpos($field, '"', -(mb_strlen($field)-$lastQuotePos+1));
				$indexId = mb_substr($field, $secondLastQuotePos+1, ($lastQuotePos-$secondLastQuotePos-1));
				switch ($indexId) {
					case 'pk':
						$indexName = 'thématique';
						break;
					case 'subjects':
						$indexName = 'rerum';
						break;
					case 'œuvres':
						$indexName = 'œuvres';
						break;
					default:
						throw new \Exception("Marque d’index inconnue ($indexId). Fichier : {$this->fileName}");
				}
			}
			$this->indexFields[]=['index' => $indexName, 'value' => $fieldValue];
			$nbEditedFields++;
		}
		return $nbEditedFields;
	}

	private function parseIndexFieldValues() {
		foreach ($this->indexFields as &$field) {
			switch($field['index']) {
				case 'nominum':
					$this->parseNominumValue($field);
					break;
				case 'thématique':
					$this->parseThemeValue($field);
					break;
				case 'rerum':
					$this->parseThemeValue($field);
					break;
				case 'œuvres':
					$this->parseOeuvresValue($field);
			}
		}
		//On élimine les doublons
		$this->fields=array_map( "unserialize",
				array_unique(array_map("serialize", $this->fields)) );
	}
	
	/**
	 * Assigne au champ donné une valeur sous la forme "Prénom Nom".
	 * @param array $field Élément de $this->fields dont la clé 'value' est la
	 * valeur du champ dans Word.
	 */
	private function parseNominumValue(&$field) {
		//On ne prend que ce qu’il y a avant la première parenthèse ouvrante
		$value=trim( explode('(', $field['value'])[0] );
		
		$identity=explode(',', $value);
		$lastName=trim($identity[0]);
		$firstName = isset($identity[1]) ? $identity[1] : '';
		
		$field['value'] = trim("$firstName $lastName");
	}
	
	/**
	 * Assigne au champ donné une valeur sous forme d’une liste de thème, du
	 * plus général au plus précis : [thème, sous-thème] généralement.
	 * @param type $field Élément de $this->fields dont la clé 'value' est la
	 * valeur du champ dans Word.
	 */
	private function parseThemeValue(&$field) {
		//On supprime le ", " final.
		$value=mb_substr($field['value'], 0, -2);
		
		$field['value']=explode(':',$value);
	}
	
	private function parseOeuvresValue(&$field) {
		//On supprime le ", " final.
		$value=mb_substr($field['value'], 0, -2);
		
		//On sépare l’artiste et son œuvre.
		$duet=explode(':',$value);
		
		$identity=explode(',', $duet[0]);
		$lastName=trim($identity[0]);
		$firstName = isset($identity[1]) ? $identity[1] : '';
		
		$field['value'] = ['artist' => "$firstName $lastName", 'work' => $duet[1]];
	}
}
