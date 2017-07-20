<?php

namespace AlineImporter\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use AlineImporter\Job\Import;
use AlineImporter\Job\ImportTask; //Quand PHP-CLI n’est pas dispo

//Formulaire
use AlineImporter\Form\TableForm;
use Zend\InputFilter\InputFilterAwareInterface;
use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;

class ImportController extends AbstractActionController
		implements InputFilterAwareInterface {
    
	/* @var $pdo \PDO */
    protected $pdo;
    /* @var $api \Omeka\Api\Manager */
	protected $api;
	/* @var $adapterManager \Omeka\Api\Adapter\Manager */
    protected $adapterManager;
	/** @var $logger \Zend\Log\Logger */
	protected $logger;
	
	private $inputFilter;

	public function __construct(\PDO $pdo, $api, $adapterManager, $logger) {
		$this->pdo = $pdo;
		$this->api = $api;
		$this->adapterManager = $adapterManager;
		$this->logger = $logger;
    }
	
	public function importAction() {
		//Affichage du formulaire demandant la table
		
		$form = new TableForm();
		$form->get('submit')->setValue('Import');
		
		$request = $this->getRequest();
		
		if (! $request->isPost()) {
			return ['form' => $form];
		}
		
		//Récupération des données postées par le formulaire
		
		$form->setInputFilter($this->getInputFilter());
		$form->setData($request->getPost());
		if (! $form->isValid()) {
			return ['form' => $form];
		}
		
		//Lancement de l’importation
		
		$table=$form->getData()['table'];
		/* @var $job \Omeka\Entity\Job */
		$job = $this->jobDispatcher()->dispatch(Import::class, ['table'=>$table]);
		
		//Lancement de l’import sans utiliser de Job (lorsque php-cli n’est pas dispo)
//		$job = new ImportTask('archives',$this->api,$this->logger, $this->pdo);
//		$job->perform();
		
		$content = "Import de la table $table lancé.<br>"
				. "<a href='/omeka-s/admin/job/{$job->getId()}'>Voir l’état</a>" ;
		
		return new ViewModel([
			'content' => $content
		]);
    }

	public function getInputFilter() {
		if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'table',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new DomainException(sprintf(
			'%s does not allow injection of an alternate input filter',
			__CLASS__
		));
	}

}