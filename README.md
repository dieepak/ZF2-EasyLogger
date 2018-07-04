# ZF2-EasyLogger
EasyLogger module for ZF2 project to maintain the log on the fly minimum configuration.

# Description:

EasyLogger is used to log anything in file system. Profiling for Action method is being managed in "onPreDispatch" & "onPostDispatch" event, "dispatch.error" event will log critical error message e.g. 404 error. Developers are free to make changes on basis of project requirement.
AbstractServiceManager is a part of EasyLogger, By using AbstractServiceManager class ZF2 service manager will be available throughout Application. So there is no need to plug service manager into model class in Module.php.

## Enable module: config/application.config.php


    <?php
        return array(
        'modules' => array( 'Application', 'EasyLogger'
          ), ........
        )

## Logger Configuration: 
Set required logger type **true** or **false**, logger file name & location in **module/EasyLogger/config/module.config.php**

    <?php
    return array(
          'LOGGER' => array(
          'CRIT' => true, 'ERROR' => true, 'WARN' => true,
          'INFO' => true,
          'DEBUG' => true,
           'ALERT' => true,
           'PATH' => './logs',
            'FILENAME' => 'logs_{date}.log',
            'ARCHIVE-SIZE' => 100, //Archive file if bigger than about 100Mb )
        );

## Store information to Logger: 
User EasyLogger namespace to log message from controller, model etc. profilingStart & profilingEnd will be manage total time consumed(milliseconds) by action or method.

      <?php
      use EasyLogger\Handler\Logger;
     
      class IndexController extends AbstractActionController {
            public function indexAction() {
            // Logger::profilingStart($this,__METHOD__); //Do whatever is required to take action
            // Logger::profilingEnd($this,__METHOD__); }
             .............. 
        }

## Use AbstractServiceManager(if required):

        <?php
        use EasyLogger\Handler\AbstractServiceManager as StaticServiceManager;
           
            class IndexController extends AbstractActionController {

                  public function indexAction() {
                        //Use Static Abstract Service Manager
                        $sm = StaticServiceManager::getDefaultServiceManager();
                       //Get Application all configuration $config = $sm->get('config');
                    }
                   ..........................
            }

## Note: 
Logger directory must have full write permission to write logs.
###### Output:

       2015-04-09T10:20:08+00:00 INFO (6): 0ms | 2 MB | Application\Controller\Index Start. 2015-04-09T10:20:08+00:00 INFO (6): 0ms | 2 MB | Array
        ( 
           [Method] => GET
           [URI] => http://localhost-zf2-skeleton/application/index?test
           [Version] => 1.1
           [IP] => 127.0.0.1 [Parameteres] => {"test":""}
        )
       2015-04-09T10:21:00+00:00 INFO (6): 3ms | 2 MB | Application\Controller\Index End.

