<?php
return [
    \Piwik\DeviceDetector\DeviceDetectorFactory::class
        => DI\object(\Piwik\Plugins\DeviceDetectorCache\DeviceDetectorCacheFactory::class)
];