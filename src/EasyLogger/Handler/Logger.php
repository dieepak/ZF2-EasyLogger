<?php
/**
 * Manage logging messages warning, criti, info, debug etc
 *
 * @author deepakgupta
 * @package EasyLogger
 */
namespace EasyLogger\Handler;

use EasyLogger\Handler\AbstractServiceManager as StaticServiceManager;
use EasyLogger\Helper\File;
use EasyLogger\Helper\Utility;
use Zend\Log\Logger as ZendLogger;
use Zend\Log\Writer\Stream;

/**
 * Manage logging messages warning, criti, info, debug etc
 *
 * @author deepakgupta
 */
class Logger
{

    /**
     *
     * @var int
     */
    public static $defaultMode = 0775;

    public static $profiling = false;

    public static $profiler = array();

    private static $priorities = array(
        ZendLogger::DEBUG,
        ZendLogger::INFO,
        ZendLogger::WARN,
        ZendLogger::CRIT,
        ZendLogger::ALERT,
        ZendLogger::ERR,
    );

    /**
     * Log Debuging
     *
     * @param string $logMessage            
     * @param string $loggerType            
     * @return void
     */
    public static function logMessage($logMessage, $loggerType = 'DEBUG', $timeTaken = 0)
    {
        try {
                        $sm = StaticServiceManager::getDefaultServiceManager();
            $configuration = $sm->get('config');
            
            //Default archive file size in Bytes
            $archiveSize = 100000000;
            
            if(!empty($configuration['LOGGER']['ARCHIVE-SIZE'])){
                $archiveSize = $configuration['LOGGER']['ARCHIVE-SIZE']*1000000;
            }
            
            
            if (! empty($configuration['LOGGER']['PATH']) && ! empty($configuration['LOGGER']['FILENAME'])) {
                $path = $configuration['LOGGER']['PATH'];
                $file = $configuration['LOGGER']['FILENAME'];
            } else {
                $path = "./logs";
                $file = "logs_{date}.txt";
            }
            
            $filename = str_replace("{date}", date('m_d_Y'), $file);
            
            $priority = 0;
            
            switch ($loggerType) {
                
                case "CRIT":
                    $priority = ZendLogger::CRIT;
                    break;
                case "ERROR":
                    $priority = ZendLogger::ERR;
                    break;
                case "WARN":
                    $priority = ZendLogger::WARN;
                    break;
                case "INFO":
                    $priority = ZendLogger::INFO;
                    break;
                case "ALERT":
                    $priority = ZendLogger::ALERT;
                    break;
                default:
                    $priority = ZendLogger::DEBUG;
                    break;
            }
            
            if (! empty($configuration['LOGGER'][$loggerType]) && $configuration['LOGGER'][$loggerType] == true) {
                
                $logFile = $path . "/{$filename}";
                
                if (! is_file($logFile)) {
                    if (is_writable(dirname($logFile))) {
                        File::put($logFile, "");
                    }
                }
                
                if (is_writable($logFile)) {
                    
                    // check for big logfile, empty it if it's bigger than about 100M
                    if (filesize($logFile) > $archiveSize) {
                        rename($logFile, $logFile . "-archive-".time());
                        File::put($logFile, "");
                    }
                    
                    if (in_array($priority, self::$priorities)) {
                        
                        if (is_object($logMessage) || is_array($logMessage)) {
                            // special formatting for exception
                            if ($logMessage instanceof \Exception) {
                                $message = "[Exception] with message: " . $logMessage->getMessage() . "\n" . "In file: " . $logMessage->getFile() . " on line " . $logMessage->getLine() . "\n" . $logMessage->getTraceAsString();
                            } else {
                                $message = print_r($logMessage, true);
                            }
                        } else {
                            $message = $logMessage;
                        }
                        
                        // add the memory consumption
                        $memory = Utility::formatBytes(memory_get_usage(), 0);
                        $memory = str_pad($memory, 6, " ", STR_PAD_LEFT);
                        
                        if ($timeTaken >= 0) {
                            $memory = "{$timeTaken}ms |" . $memory;
                        }
                        
                        $message = $memory . " | " . $message;
                        
                        $log = new ZendLogger();
                        $writer = new Stream($logFile);
                        $log->addWriter($writer);
                        $log->log($priority, $message);
                    }
                }
            }
        } catch (\Exception $e) {}
    }

    /**
     * Start Profiling 
     * @param Object $object //Class object or $this
     * @param string $method //Method Name or __METHOD__
     */
    public static function profilingStart($object, $method)
    {
        self::$profiler[$method]= microtime(true);
        //method name to generate log
        $logMessage = "{$method} Start.";
        self::logMessage($logMessage, 'INFO');
    }
    
    /**
     * End Profiling
     * @param Object $object //Class object or $this
     * @param string $method //Method Name or __METHOD__
     */
    public static function profilingEnd($object, $method)
    {
        if(!empty(self::$profiler[$method])){
            
            $startTs = self::$profiler[$method];
            
            $timeConsumed = round(microtime(true) - $startTs, 3) * 1000;
            
            $logMessage = "{$method} End.";
            self::logMessage($logMessage, 'INFO', $timeConsumed);
        }
        
    }
    
}

?>