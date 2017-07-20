<?php
namespace AlineImporter\Form;

use Zend\Form\Form;

/**
 * Description of TableForm
 *
 * @author pols12
 */
class ImportConfigForm extends Form {
	public function __construct($name = null)
    {
        // We will ignore the name provided to the constructor
        parent::__construct('table');
		
        $this->add([
            'name' => 'table',
            'type' => 'text',
            'options' => [
                'label' => 'Table',
            ],
        ]);
        $this->add([
            'name' => 'startOffset',
            'type' => 'text',
            'options' => [
                'label' => 'Commencer à la ligne ',
            ],
        ]);
        $this->add([
            'name' => 'endOffset',
            'type' => 'text',
            'options' => [
                'label' => 'Jusqu’à la ligne ',
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id'    => 'submitbutton',
            ],
        ]);
    }
}
