<?php

namespace TestM\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Omeka\Api\Manager as ApiManager;
use Omeka\Entity\Item;
//use TestM\Job\Insert;
//Job\Insert;

class ListController extends AbstractActionController {
    
    protected $config; //utilité ?
    protected $doctrine;
    
    public function __construct(array $config, $doctrineService) {
        //parent(); //probablement inutile
        $this->config = $config;
        $this->doctrine = $doctrineService;
    }
    
    /**
     * Insert an Item in DB.
     * @return type
     */
    public function addAction() {
        // Création de l'entité
        $item = new Item();
        $item->setIsPublic(true);
        $item->setCreated(new DateTime('now'));
        $item->setModified(new DateTime('now'));
        
        // On récupère l'EntityManager
//        $em = $this->getEvent()->getApplication()->getServiceManager()
//                ->get('doctrine.orm.entity_manager');
        //Contenu dans $this->doctrine

        // Étape 1 : On « persiste » l'entité
        $em->persist($item);

        // Étape 2 : On « flush » tout ce qui a été persisté avant
        $em->flush();

        // Reste de la méthode qu'on avait déjà écrit
//        if ($request->isMethod('POST')) {
//          $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
//          return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $item->getId())));
//        }

//        return $this->render('OCPlatformBundle:Advert:add.html.twig');
    }
    
    
    /**
     * Get an item using ApiManager().
     * Does not work because I don’t know how to construct ApiManager object.
     * @param int $idItem
     */
    private function affWithApiManager(int $idItem) {
        $apiManager = new ApiManager();
//        // Compose the request object
//        $request = new Request(Request::READ, 'item');
//        $request->setId(100);
//        // Execute the request

//        $response = $apiManager->execute($request);
//        // Get the representation
//        $item = $response->getContent();

        // The above could be written more concisely (recommended usage)
        $item = $apiManager->read('item', $idItem)->getContent();

        // Do something with the representation.
        echo $item->jsonSerialize();
    }
    public function affAction() {
        
        //Display an item using API
        //$this->aff(1);
        
        //Insert an item using API
        //$item=json_decode(file_get_contents(__DIR__ . '/../../../assets/item.json'), true);
        //$this->insert($item);
        
        //Display an item using EntityManager
        
        
        return new ViewModel([
            'content' => 'Placeholder page'
        ]);
    }
}
