<?php
namespace EasyLogger;

use EasyLogger\Handler\Logger;
use EasyLogger\Handler\AbstractServiceManager as StaticServiceManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Http\PhpEnvironment\RemoteAddress;

class Module implements ConfigProviderInterface, AutoloaderProviderInterface
{

    /**
     * Module Bootstrap
     *
     * @param MvcEvent $e
     *            // event
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $serviceManager = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();
        
        // set Default static service manager
        StaticServiceManager::setDefaultServiceManager($e->getApplication()->getServiceManager());
        
        $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error', function ($e) use($serviceManager)
        {
            $controller = $e->getParam('controller');
            
            // handle 404 error
            $response = $e->getResponse();
            
            // Manage 404 redirect action to home
            // if ($response->getStatusCode() == 404) {
            // // Do what ever you want to check the user's identity
            // $response = $e->getResponse();
            // $response->setHeaders($response->getHeaders()->addHeaderLine('Location', '/'));
            // $response->setStatusCode(200);
            // $response->sendHeaders();
            // exit();
            // }
            
            Logger::logMessage($response, 'CRIT');
        });
        
        $eventManager = $application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH, array(
            $this,
            'onPreDispatch'
        ), 100);
        
        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH, array(
            $this,
            'onPostDispatch'
        ), - 100);
    }

    /**
     *
     * @author deepakgupta
     * @param \Zend\Mvc\MvcEvent $evt
     *            // event
     * @return mixed
     */
    public function onPreDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $controller = $e->getRouteMatch()->getParam('controller');
        $request = $e->getRequest();
        
        //Get Request
        $remote = new RemoteAddress();
        $param = array(
            'Method' => $request->getMethod(),
            'URI' => $request->getUriString(),
            'Version' => $request->getVersion(),
            'IP' => $remote->getIpAddress()
        );
        
        if ($request->getMethod() == 'POST') {
            $param['Parameteres'] = json_encode($request->getPost());
        } else {
            $param['Parameteres'] = json_encode($request->getQuery());
        }
        
        Logger::profilingStart($this,$controller);
        
        Logger::logMessage($param, 'INFO');
    }

    /**
     *
     * @author deepakgupta
     * @param \Zend\Mvc\MvcEvent $evt
     *            // event
     * @return mixed
     */
    public function onPostDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $controller = $e->getRouteMatch()->getParam('controller');
        $request = $e->getRequest();
        $response = $e->getResponse();
        Logger::logMessage($response, 'INFO');
        
        Logger::profilingEnd($this,$controller);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }
}
