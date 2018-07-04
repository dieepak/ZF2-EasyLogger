<?php
namespace EasyLogger\Handler;

use Zend\Mvc\ApplicationInterface;
/**
* Get ZF2 Static Service Manager every to access ZF2 service locator
*
* @author deepakgupta
*/
abstract class AbstractServiceManager
{

    protected $serviceManager = null;
    
    protected static $defaultServiceManager = null;
    
    /**
     * Set the default Service Manager
     *
     * @param ApplicationInterface $serviceManager
     */
    public static function setDefaultServiceManager($serviceManager)
    {
        self::$defaultServiceManager = $serviceManager;
    }
    
    /**
     * Get the default default Service Manager
     *
     * @return Adapter
     * @throws ConfigException If no default database adapter is set
     */
    public static function getDefaultServiceManager()
    {
        if (self::$defaultServiceManager === null) {
            throw new \Exception('No default service manager configured');
        }
    
        return self::$defaultServiceManager;
    }
    
    /**
     * Set the Service Manager
     *
     * @param ApplicationInterface $serviceManager
     * @return
     *
     */
    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    
        return $this;
    }
    
    /**
     * Get Set the Service Manager
     *
     *
     * @return ApplicationInterface
     */
    public function getserviceManager()
    {
        if ($this->serviceManager === null) {
            $this->setServiceManager(self::getDefaultServiceManager());
        }
    
        return $this->serviceManager;
    }
}

?>