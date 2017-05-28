<?php

namespace TestM\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Api\Representation\ResourceClassRepresentation;
use Omeka\Api\Representation\ResourceTemplateRepresentation;
use Omeka\Entity\Item;
use Omeka\Entity\Property;
use Omeka\Entity\Value;
//use TestM\Job\Insert;
//Job\Insert;

class ListController extends AbstractActionController {
    
    protected $config; //utilité ?
    /* @var $api \Omeka\Api\Manager */
	protected $api;
	/* @var $adapterManager \Omeka\Api\Adapter\Manager */
    protected $adapterManager;
    
    public function __construct(array $config, $api, $adapterManager) {
        $this->config = $config;
		$this->api = $api;
		$this->adapterManager = $adapterManager;
    }
	
	public function postFile() {
		$data = [];
		$data['monFichier'] = new \CURLFile(__DIR__ . '/test.txt','text/plain','nomDuFichierSurLeServeur');
		
		//adding keys
		$url="http://localhost/omeka-s/testm";
		
		$options=[
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data']
		];
		
		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		$result=curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	/**
     * Add a media to an existing item uploading a file using ApiManager.
     * @param int $itemId Id of item which will have the media attached.
     */
    private function uploadWithApiManager($itemId) {
		$fileIndex=0;

		$data=[
			"o:ingester" => "upload",
			"file_index" => $fileIndex,
			"o:item" => ["o:id" => $itemId]
		];
		$fileData = [
			'file'=>[
				$fileIndex => $_FILES['monFichier'],
			],
		];
		return $this->api->create('media', $data, $fileData)->getContent();
	}
	
    /**
     * Insert an Item in DB.
     * @return type
     */
    public function addAction() {
        // Création de l'entité
        $item = new Item();
        $item->setIsPublic(true);
        $item->setCreated(new \DateTime('now'));
        $item->setModified(new \DateTime('now'));
		
		/* @var $resourceClass \Omeka\Entity\ResourceClass */
		$resourceClass=$this->adapterManager->get('resource_classes')
				->findEntity(['localName'=>'Location']);
		$item->setResourceClass($resourceClass);
		
		/* @var $resourceTemplate \Omeka\Entity\ResourceTemplate */
		$resourceTemplate=$this->adapterManager->get('resource_templates')
				->findEntity(['label'=>'Lieu d’archives']);
		$item->setResourceTemplate($resourceTemplate);
		
		/* @var $properties \Doctrine\Common\Collections\ArrayCollection */
		$properties = $item->getValues(); //RDF triples
		
		$archiveProperties=[
			'dcterms'=>["title"],
			'locn'=>["address"],
			'foaf'=>['homepage'],
		];
		$archiveAdresseProperties=[
			'locn'=>['fullAddress','postName','adminUnitL1'],
		];
		
		//For each vocabulary	
			//get the vocabulary
			/* @var $vocabularyAdapter \Omeka\Api\Adapter\VocabularyAdapter */
			$vocabularyAdapter = $this->adapterManager->get('vocabularies');
			/* @var $vocabulary \Omeka\Entity\Vocabulary */
			$vocabulary=$vocabularyAdapter->findEntity(['prefix'=>'bibo']);

			//For each property
				//get the property from the vocabulary or maybe we must create one
	//			$property = new Property();
	//			$property->setVocabulary($vocabulary);
			
				/* @var $terms \Doctrine\Common\Collections\ArrayCollection */
				$terms=$vocabulary->getProperties();
				
				//Voir comment on peut récupérer la propriété voulue, est-ce
				// que les termes sont les clés dans cette ArrayCollection ?
				
//				/* @var $terms array[PropertyRepresentation] */
//				$terms=$vocabularyAdapter->getRepresentation($vocabulary)->properties();
				
				//Create a value and set value properties
				$value= new Value();
				$value->setValue("ma valeur"); //or other setter

				//Link property to the value
				$value->setProperty($property);

				//Add the property-linked value to the item
				$properties->add($value);
		
		//On crée une representation de l’item
		$itemAdapter = $this->adapterManager->get('items');
		/* @var $itemRpz ItemRepresentation */
		$itemRpz = $itemAdapter->getRepresentation($item);
		echo json_encode($itemRpz);
		
		
		/* @var $em \Doctrine\ORM\EntityManager */
//		$em;
        // On récupère l'EntityManager
//        $em = $this->getEvent()->getApplication()->getServiceManager()
//                ->get('doctrine.orm.entity_manager');

        // Étape 1 : On « persiste » l'entité
//        $em->persist($item);

        // Étape 2 : On « flush » tout ce qui a été persisté avant
//        $em->flush();

        // Reste de la méthode qu'on avait déjà écrit
//        if ($request->isMethod('POST')) {
//          $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
//          return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $item->getId())));
//        }

//        return $this->render('OCPlatformBundle:Advert:add.html.twig');
    }
    

	/**
     * Insert an item using ApiManager.
     */
    private function addWithApiManager() {
		$fileIndex=0;
		$data=[
			"o:resource_class"=>["o:id"=>40],
			"title"=> [[
				"type"=> "literal",
				"property_id"=> 1,
				"@value"=> "Nouvel Insert"
			]],
			"desc"=> [[
				"type"=> "literal",
				"property_id"=> 4,
				"@value"=> "Un super les livre sur les savants et les écrivains"
			]],
			"createur"=> [[
				"type"=> "literal",
				"property_id"=> 2,
				"@value"=> "C’est bien lui, c’est Henri !"
			],[
				"type"=> "resource",
				"property_id"=> 5,
				"value_resource_id"=> 6,
			],[
				"type"=> "resource",
				"property_id"=> 2,
				"value_resource_id"=> 6,
			],[
				"type"=> "uri",
				"property_id"=> 3,
				"@id"=> "https:\/\/google.com",
				"o:label"=> "Moteur de recherche Google"
			]],
		];
		
		if(isset($_FILES['monFichier'])) {
			$mediaData=[
				"o:media" =>[[
					"o:ingester" => "upload",
					"file_index" => $fileIndex,
				]],
			];
			$data = array_merge($data,$mediaData);
			$fileData=[
				'file'=>[
					$fileIndex => $_FILES['monFichier'],
				],
			];
		}
		else $fileData = [];
		
		return $this->api->create('items', $data, $fileData)->getContent();
	}
	/**
     * Get an item using ApiManager.
     * @param int $idItem
     */
    private function affWithApiManager(int $idItem) {
//        // Compose the request object
//        $request = new Request(Request::READ, 'item');
//        $request->setId(100);
//        // Execute the request

//        $response = $apiManager->execute($request);
//        // Get the representation
//        $item = $response->getContent();
		
        // The above could be written more concisely (recommended usage)
		/* @var $item ItemRepresentation */
		$item = $this->api->read('items', $idItem)->getContent();
		
		return json_encode($item);
    }
    public function affAction() {
        
        //Insert an item using API
        //$item=json_decode(file_get_contents(__DIR__ . '/../../../assets/item.json'), true);
        //$this->insert($item);
        
        //Display an item using EntityManager
//		$content = $this->affWithApiManager(8);
		
		//Insert an item using ApiManager
//		$content=json_encode($this->addWithApiManager());
		
		//Upload a file as Media using ApiManager
//		if(isset($_FILES['monFichier']))
//			$content = json_encode($this->uploadWithApiManager(38));
//		else
//			$content = $this->postFile();
		
		//Create an item and attach a newly uploaded media in the same time
		if(isset($_FILES['monFichier']))
			$content = json_encode($this->addWithApiManager());
		else
			$content = $this->postFile();
		
		return new ViewModel([
			'content' => $content
		]);
    }
}
