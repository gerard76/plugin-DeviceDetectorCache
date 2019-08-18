<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\DeviceDetector\DeviceDetectorFactory;
use Piwik\Filesystem;
use Piwik\Plugins\DeviceDetectorCache\DeviceDetectorCacheEntry;
use Piwik\Plugins\DeviceDetectorCache\DeviceDetectorCacheFactory;

class DeviceDetectorCacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $filesToTearDown = array();

    public function setUp()
    {
        DeviceDetectorFactory::clearInstancesCache();
    }

    public function tearDown()
    {
        foreach ($this->filesToTearDown as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testGetInstanceFromCache()
    {
        $userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0";
        $expected = array(
            'bot' => null,
            'brand' => 'Cooper',
            'client' => array(
                'type' => 'browser',
                'name' => 'Microsoft Edge'
            ),
            'device' => 1,
            'model' => 'iPhone',
            'os' => array(
                'name' => 'Linux'
            )
        );

        $this->writeFile($expected, $userAgent);

        $factory = new DeviceDetectorCacheFactory();
        $deviceDetection = $factory->makeInstance($userAgent);
        $this->assertInstanceOf("\Piwik\Plugins\DeviceDetectorCache\DeviceDetectorCacheEntry", $deviceDetection);
        $this->assertEquals(null, $deviceDetection->getBot());
        $this->assertEquals('Cooper', $deviceDetection->getBrand());
        $this->assertEquals($expected['client'], $deviceDetection->getClient());
        $this->assertEquals(1, $deviceDetection->getDevice());
        $this->assertEquals('iPhone', $deviceDetection->getModel());
        $this->assertEquals($expected['os'], $deviceDetection->getOs());
    }

    public function testGetInstanceFromDeviceDetector()
    {
        $userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0";
        $expected = array(
            'client' => array(
                'type' => 'browser',
                'name' => 'Firefox',
                'short_name' => 'FF',
                'version' => '33.0',
                'engine' => 'Gecko',
                'engine_version' => ''
            ),
            'os' => array(
                'name' => 'Mac',
                'short_name' => 'MAC',
                'version' => '10.10',
                'platform' => ''
            )
        );

        $factory = new DeviceDetectorCacheFactory();
        $deviceDetection = $factory->makeInstance($userAgent);
        $this->assertInstanceOf("\DeviceDetector\DeviceDetector", $deviceDetection);
        $this->assertEquals(null, $deviceDetection->getBot());
        $this->assertEquals('AP', $deviceDetection->getBrand());
        $this->assertEquals($expected['client'], $deviceDetection->getClient());
        $this->assertEquals(0, $deviceDetection->getDevice());
        $this->assertEquals('', $deviceDetection->getModel());
        $this->assertEquals($expected['os'], $deviceDetection->getOs());
    }

    private function writeFile($expected, $userAgent)
    {
        $hashedUserAgent = md5($userAgent);
        $dirToWrite = DeviceDetectorCacheEntry::getCachePath($userAgent, true);
        $content = "<?php return " . var_export($expected, true) . ";";
        $outputFile = $dirToWrite . '/' . $hashedUserAgent . '.php';
        $this->filesToTearDown[] = $outputFile;
        file_put_contents($outputFile, $content, LOCK_EX);
    }
}