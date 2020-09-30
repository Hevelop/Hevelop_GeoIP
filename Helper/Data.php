<?php

namespace Hevelop\GeoIP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Archive\Gz;
use Magento\Framework\Archive\Tar;
use Magento\Framework\Exception\LocalizedException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class Data
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Data extends AbstractHelper
{

    const DATE_FORMAT = 'h:i:s d/M/Y';
    /**
     * @var Gz
     */
    protected $gzArchive;
    /**
     * @var Tar
     */
    protected $tarArchive;

    /**
     * Data constructor.
     * @param Context $context
     * @param Gz $gzArchive
     * @param Tar $tarArchive
     * @param array $data
     */
    public function __construct(
        Context $context,
        Gz $gzArchive,
        Tar $tarArchive,
        array $data = []
    ) {
        parent::__construct($context);
        $this->gzArchive = $gzArchive;
        $this->tarArchive = $tarArchive;
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
        $tarDestination = substr($archive, 0, strlen($archive) - 3);
        if (file_exists($tarDestination)) {
            unlink($tarDestination);
        }
        $tarArchive = $this->gzArchive->unpack($archive, $tarDestination);
        $unpackedPath = $this->tarArchive->unpack($tarArchive, $destination . DIRECTORY_SEPARATOR);

        return file_exists($unpackedPath) && filesize($unpackedPath) > 0;
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
     * @return array
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
