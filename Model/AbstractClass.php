<?php

namespace Hevelop\GeoIP\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Session\Generic;
use Magento\Store\Model\ScopeInterface;
use Hevelop\GeoIP\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class AbstractClass
 * @package Hevelop\GeoIP\Model
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class AbstractClass
{

    protected $localDir, $localFile, $localArchive, $remoteArchive;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Data
     */
    protected $geoIPHelper;

    /**
     * @var Generic
     */
    protected $generic;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * DateTime
     *
     * @var DateTime
     */
    protected $_date;

    /**
     * AbstractClass constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $geoIPHelper
     * @param Generic $generic
     * @param DirectoryList $directoryList
     * @param TimezoneInterface $_localeDate
     * @param DateTime $date
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Data $geoIPHelper,
        Generic $generic,
        DirectoryList $directoryList,
        TimezoneInterface $_localeDate,
        DateTime $date,
        array $data = []
    )
    {
        $this->directoryList = $directoryList;
        $this->scopeConfig = $scopeConfig;
        $this->geoIPHelper = $geoIPHelper;
        $this->generic = $generic;
        $this->_localeDate = $_localeDate;
        $this->_date = $date;
        $this->localDir = 'geoip';
        $this->localFile = $this->getAbsoluteDirectoryPath() . '/' . $this->localDir . '/GeoIP.dat';
        $this->localArchive = $this->getAbsoluteDirectoryPath() . '/' . $this->localDir . '/GeoIP.dat.gz';
        $this->remoteArchive = 'http://www.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz';
    }


    /**
     * @return string
     */
    public function getArchivePath()
    {
        return $this->localArchive;
    }


    /**
     * @return string
     */
    public function getRelativeDirectoryPath()
    {
        return $this->scopeConfig->getValue('hevelop_geoip/general/geoip_directory', ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return string
     */
    public function getAbsoluteDirectoryPath()
    {
        return $this->directoryList->getPath($this->getRelativeDirectoryPath());
    }


    /**
     * @return string
     */
    public function checkFilePermissions()
    {
        /** @var $helper Hevelop_GeoIP_Helper_Data */
        $helper = $this->geoIPHelper;

        $relativeDirPath = $this->getRelativeDirectoryPath();

        $dir = $this->getAbsoluteDirectoryPath() . '/' . $this->localDir;
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                return sprintf($helper->__('%s exists but it is file, not dir.'), "$relativeDirPath/{$this->localDir}");
            } elseif ((!file_exists($this->localFile) || !file_exists($this->localArchive)) && !is_writable($dir)) {
                return sprintf($helper->__('%s exists but files are not and directory is not writable.'), "$relativeDirPath/{$this->localDir}");
            } elseif (file_exists($this->localFile) && !is_writable($this->localFile)) {
                return sprintf($helper->__('%s is not writable.'), "$relativeDirPath/{$this->localDir}" . '/GeoIP.dat');
            } elseif (file_exists($this->localArchive) && !is_writable($this->localArchive)) {
                return sprintf($helper->__('%s is not writable.'), "$relativeDirPath/{$this->localDir}" . '/GeoIP.dat.gz');
            }
        } elseif (!@mkdir($dir)) {
            return sprintf($helper->__('Can\'t create %s directory.'), "$relativeDirPath/{$this->localDir}");
        }

        return '';
    }


    /**
     * Method update.
     */
    public function update()
    {
        /** @var $helper Hevelop_GeoIP_Helper_Data */
        $helper = $this->geoIPHelper;

        $ret = array('status' => 'error');

        if ($permissions_error = $this->checkFilePermissions()) {
            $ret['message'] = $permissions_error;
        } else {
            $remote_file_size = $helper->getSize($this->remoteArchive);
            if ($remote_file_size < 100000) {
                $ret['message'] = $helper->__('You are banned from downloading the file. Please try again in several hours.');
            } else {
                /** @var $_session Mage_Core_Model_Session */
                $_session = $this->generic;
                $_session->setData('_geoip_file_size', $remote_file_size);

                $src = fopen($this->remoteArchive, 'r');
                $target = fopen($this->localArchive, 'w');
                stream_copy_to_stream($src, $target);
                fclose($target);

                if (filesize($this->localArchive)) {
                    if ($helper->unGZip($this->localArchive, $this->localFile)) {
                        $ret['status'] = 'success';
                        $ret['date'] = $this->_date->date(Data::DATE_FORMAT);
                    } else {
                        $ret['message'] = $helper->__('UnGzipping failed');
                    }
                } else {
                    $ret['message'] = $helper->__('Download failed.');
                }
            }
        }

        echo json_encode($ret);
    }

}