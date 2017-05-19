<?php
namespace TestM\Job;

use Omeka\Job\AbstractJob;


/**
 * Description of Insert
 *
 * @author pols12
 */
class Insert extends AbstractJob {
    protected $api;
    
    protected $addedCount;
    
    protected $logger;
    
    public function perform() {
        $this->logger = $this->getServiceLocator()->get('Omeka\Logger');
        $this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $config = $this->getServiceLocator()->get('Config');
        
        
    }
    

}
