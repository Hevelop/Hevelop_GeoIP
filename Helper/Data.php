<?php

namespace Hevelop\GeoIP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;

/**
 * Class Data
 * @package Hevelop\GeoIP\Helper
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Data extends AbstractHelper
{

    const DATE_FORMAT = 'h:i:s d/M/Y';

    /**
     * Request object
     *
     * @var RequestInterface
     */
    protected $request;


    /**
     * Data constructor.
     * @param RequestInterface $httpRequest
     * @param array $data
     */
    public function __construct(
        RequestInterface $httpRequest,
        array $data = []
    )
    {
        $this->request = $httpRequest;
    }

    /**
     * Get size of remote file
     *
     * @param $file
     * @return mixed
     */
    public function getSize($file)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    }


    /**
     * Extracts single gzipped file. If archive will contain more then one file you will got a mess.
     *
     * @param $archive
     * @param $destination
     * @return int
     */
    public function unGZip($archive, $destination)
    {
        $buffer_size = 4096; // read 4kb at a time
        $archive = gzopen($archive, 'rb');
        $dat = fopen($destination, 'wb');
        while (!gzeof($archive)) {
            fwrite($dat, gzread($archive, $buffer_size));
        }
        fclose($dat);
        gzclose($archive);
        return filesize($destination);
    }


    /**
     * @return string
     */
    public function getClientIps()
    {
        $ipaddress = '';

        if ($this->request->getServer('HTTP_CLIENT_IP', false)) {
            $ipaddress = $this->request->getServer('HTTP_CLIENT_IP');
        } else if ($this->request->getServer('HTTP_X_FORWARDED_FOR', false)) {
            $ipaddress = $this->request->getServer('HTTP_X_FORWARDED_FOR', false);
        } else if ($this->request->getServer('HTTP_X_FORWARDED', false)) {
            $ipaddress = $this->request->getServer('HTTP_X_FORWARDED', false);
        } else if ($this->request->getServer('HTTP_FORWARDED_FOR', false)) {
            $ipaddress = $this->request->getServer('HTTP_FORWARDED_FOR', false);
        } else if ($this->request->getServer('HTTP_FORWARDED', false)) {
            $ipaddress = $this->request->getServer('HTTP_FORWARDED', false);
        } else if ($this->request->getServer('REMOTE_ADDR', false)) {
            $ipaddress = $this->request->getServer('REMOTE_ADDR');
        }

        $ipaddress = str_replace(' ', '', $ipaddress);
        $ipaddress = explode(',', $ipaddress);
        return $ipaddress;
    }


    /**
     * @return bool
     */
    public function geoLocationAllowed()
    {
        $active = $this->isGeolocationActive();
        $userAgentAllowed = $this->isUserAgentAllowed();
        return $active && $userAgentAllowed;
    }


    /**
     * @return bool
     */
    public function isGeolocationActive()
    {
        // TODO: implement logic
        return true;
    }


    /**
     * @return bool
     */
    public function isUserAgentAllowed()
    {
        // TODO: implement logic
        return true;
    }

}