<?php
return array(
    'LOGGER' => array(
        'CRIT' => true,
        'ERROR' => true,
        'WARN' => true,
        'INFO' => true,
        'DEBUG' => true,
        'ALERT' => true,
        'PATH' => './logs',
        'FILENAME' => 'logs_{date}.log',
        'ARCHIVE-SIZE' => 100, //Archive file if bigger than about 100Mb
    )
);