<?php

namespace Hevelop\GeoIP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
     * Data constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context);
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
     * @param $archive
     * @param $destination
     * @return bool
     * @throws LocalizedException
     */
    public function unTarGz($archive, $destination)
    {
        $allowedExtensions = ['tar', 'gz'];
        $archiveExt = pathinfo($archive, PATHINFO_EXTENSION);
        if (!in_array($archiveExt, $allowedExtensions)) {
            throw new LocalizedException(__("$archive extension not allowed!"));
        }

        if ($archiveExt == 'gz') {
            $decompressedArchive = substr($archive, 0, strlen($archive) - 3);
            if (file_exists($decompressedArchive)) {
                unlink($decompressedArchive);
            }
            $phar = new \PharData($archive);
            $phar->decompress();
            $archive = $decompressedArchive;
        }

        $archiveExt = pathinfo($archive, PATHINFO_EXTENSION);
        if ($archiveExt == 'tar') {
            $phar = new \PharData($archive);
            $phar->extractTo($destination, null, true);
        } else {
            throw new LocalizedException(__("$archive extension not allowed!"));
        }

        return file_exists($destination) && filesize($destination) > 0;
    }

    /**
     * @param $dir
     * @param $filePath
     * @return bool
     */
    public function extractFileFromDir($dir, $filePath)
    {
        $dir_iterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            if ($file->getFilename() === basename($filePath)) {
                rename($file->getPathname(), $filePath);
                return file_exists($filePath) && filesize($filePath);
            }
        }
        return false;
    }


    /**
     * @return string
     */
    public function getClientIps()
    {
        $ipaddress = '';

        if ($this->_request->getServer('HTTP_CLIENT_IP', false)) {
            $ipaddress = $this->_request->getServer('HTTP_CLIENT_IP');
        } else if ($this->_request->getServer('HTTP_X_FORWARDED_FOR', false)) {
            $ipaddress = $this->_request->getServer('HTTP_X_FORWARDED_FOR', false);
        } else if ($this->_request->getServer('HTTP_X_FORWARDED', false)) {
            $ipaddress = $this->_request->getServer('HTTP_X_FORWARDED', false);
        } else if ($this->_request->getServer('HTTP_FORWARDED_FOR', false)) {
            $ipaddress = $this->_request->getServer('HTTP_FORWARDED_FOR', false);
        } else if ($this->_request->getServer('HTTP_FORWARDED', false)) {
            $ipaddress = $this->_request->getServer('HTTP_FORWARDED', false);
        } else if ($this->_request->getServer('REMOTE_ADDR', false)) {
            $ipaddress = $this->_request->getServer('REMOTE_ADDR');
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