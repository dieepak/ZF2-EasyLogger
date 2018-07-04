<?php
namespace EasyLogger\Helper;

class File
{
    /**
     * @var int
     */
    public static $defaultMode = 0775;
    
    
    /**
     * @param $mode
     */
    public static function setDefaultMode($mode) {
        self::$defaultMode = $mode;
    }
    
    /**
     * @return int
     */
    public static function getDefaultMode() {
        return self::$defaultMode;
    }
    
    /**
     * @param $path
     * @param $data
     * @return int
     */
    public static function put ($path, $data) {
    
        if(!is_dir(dirname($path))) {
            self::mkdir(dirname($path));
        }
    
        $return = file_put_contents($path, $data);
        @chmod($path, self::$defaultMode);
        return $return;
    }
    
    /**
     * @param $path
     * @param null $mode
     * @param bool $recursive
     * @return bool
     */
    public static function mkdir($path, $mode = null, $recursive = true) {
    
        if(!$mode) {
            $mode = self::$defaultMode;
        }
    
        $return = @mkdir($path, 0777, $recursive);
        @chmod($path, $mode);
        return $return;
    }
}

?>