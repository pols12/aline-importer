<?php

namespace AlineImporter\Cleaner;

include_once 'simplehtmldom/simple_html_dom.php';

/**
 * Nettoie les pages HTML présente sur la version Aline du site.
 *
 * @author pols12
 */
class Aline {
	/** @var string */
	private $source;
	
	/** @var simple_html_dom_node Cœur de la page HTML */
	private $content;
	
	/** @var simple_html_dom */
	private $obj;
	
	/**
	 * Crée un objet DOM nettoyable.
	 * @param string $url URL de la page HTML que l’on veut nettoyer.
	 */
	public function __construct($url) {
		$this->source=$url;
		$this->obj= file_get_html($url);
	}
	
	/**
	 * Donne le contenu nettoyé.
	 * @return string HTML du contenu
	 */
	public function getContent() {
		if(!isset($this->content))
			$this->clean();
		return $this->content;
	}
	
	/**
	 * Nettoie le contenu et l’assigne à $content.
	 * On ne garde que la balise &lt;article&gt;, on inclue les images et on 
	 * masque le timestamp.
	 */
	private function clean() {
		//On récupère uniquement le cœur de la page
		$this->content = $this->obj->find('article')[0];
		
		//On inclue les images externes dans le HTML
		foreach ($this->content->find('img') as &$img) {
			$img->src = $this->parseImg(dirname($this->source).'/'.$img->src);
		}
		
		//On masque l’indication de timestamp
		$timestampDiv = (string) $this->content->find('div.ltx_para',-1);
		$this->content->find('div.ltx_para',-1)->outertext
				= "<!-- $timestampDiv -->";
	}
	
	/**
	 * Encode une image en base64 et retourne la data-URI à ajouter comme valeur
	 * de l’attribut src d’une balise &lt;img&gt;.
	 * @param string $url URL de l’image à encoder.
	 * @return string data-URI de l’image.
	 */
	private function parseImg($url) {
		// Read image path, convert to base64 encoding
		$imageData = base64_encode(file_get_contents($url));
		
		$mime='image/'.pathinfo($url, PATHINFO_EXTENSION);
		
		// Format the image SRC:  data:{mime};base64,{data}
		$src = "data:$mime;base64,$imageData";

		return $src;
	}
}