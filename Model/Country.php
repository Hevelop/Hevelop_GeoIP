<?php

namespace Hevelop\GeoIP\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\Generic;
use Hevelop\GeoIP\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Store\Model\StoreManagerInterface;
use GeoIp2\Database\Reader as GeoIpReader;
use Hevelop\GeoIP\Helper\Config as GeoIPConfigHelper;

/**
 * Class Country
 * @package Hevelop\GeoIP\Model
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Country extends AbstractClass
{
    /**
     * @var bool|mixed|null
     */
    protected $country = null;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var GeoIpReader
     */
    protected $geoIpReader;

    /**
     * Country constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param GeoIPConfigHelper $geoIPConfigHelper
     * @param Data $geoIPHelper
     * @param Generic $generic
     * @param DirectoryList $directoryList
     * @param TimezoneInterface $_localeDate
     * @param DateTime $date
     * @param RemoteAddress $remoteAddress
     * @param StoreManagerInterface $storeManager
     * @param array $data
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GeoIPConfigHelper $geoIPConfigHelper,
        Data $geoIPHelper,
        Generic $generic,
        DirectoryList $directoryList,
        TimezoneInterface $_localeDate,
        DateTime $date,
        RemoteAddress $remoteAddress,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $geoIPHelper, $geoIPConfigHelper, $generic, $directoryList, $_localeDate, $date);
        $this->remoteAddress = $remoteAddress;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param $ip
     * @return string|null
     * @throws \GeoIp2\Exception\AddressNotFoundException
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function getCountryByIp($ip)
    {
        if (file_exists($this->localFile)) {
            if (!$this->geoIpReader) {
                $this->geoIpReader = new GeoIpReader($this->localFile);
            }
            $record = $this->geoIpReader->country($ip);
            if ($record instanceof \GeoIp2\Model\Country && $record->country instanceof \GeoIp2\Record\Country) {
                return $record->country->isoCode;
            }
        }
        return null;
    }

    /**
     * @return bool|mixed|null
     */
    public function getCountry()
    {
        if ($this->country === null) {
            $country = null;

            $ips = $this->remoteAddress->getRemoteAddress();
            $ips = str_replace(' ', '', $ips);
            $ips = explode(',', $ips);
            foreach ($ips as $k => $ip) {
                $valid = filter_var(
                    $ip,
                    FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                );
                if (!$valid) {
                    unset($ips[$k]);
                }
            }
            if (is_array($ips) && count($ips) > 0) {
                $ip = $ips[0];
                try {
                    $country = $this->getCountryByIp($ip);
                } catch (\Exception $e) {
                    // todo log exception
                }
            }
            $this->country = $country === null ? $this->geoIPConfigHelper->getDefaultCountry() : $country;
        }
        return $this->country;
    }

    /**
     * Determine correct store based on geolocated country.
     *
     * @param $country
     * @return \Magento\Store\Api\Data\StoreInterface|null
     */
    public function getStoreFromCountry($country)
    {
        $stores = $this->_storeManager->getStores(false, true);
        foreach ($stores as $store) {
            $storeCountries = $this->geoIPConfigHelper->getAllowCountries($store->getWebsiteId());
            if (in_array($country, $storeCountries, true)) {
                return $store;
            }
        }
        return $this->_storeManager->getDefaultStoreView();
    }
}
