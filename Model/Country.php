<?php

namespace Hevelop\GeoIP\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Session\Generic;
use Hevelop\GeoIP\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var string
     */
    protected $defaultCountry;

    /**
     * @var bool|mixed|null
     */
    protected $country = null;

    /**
     * @var array
     */
    protected $allowed_countries = [];

    /**
     * @var Wrapper
     */
    protected $geoIPWrapper;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;


    /**
     * Country constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Wrapper $geoIPWrapper
     * @param Data $geoIPHelper
     * @param Generic $generic
     * @param DirectoryList $directoryList
     * @param TimezoneInterface $_localeDate
     * @param DateTime $date
     * @param RemoteAddress $remoteAddress
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Wrapper $geoIPWrapper,
        Data $geoIPHelper,
        Generic $generic,
        DirectoryList $directoryList,
        TimezoneInterface $_localeDate,
        DateTime $date,
        RemoteAddress $remoteAddress,
        StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        parent::__construct($scopeConfig, $geoIPHelper, $generic, $directoryList, $_localeDate, $date);

        $this->geoIPWrapper = $geoIPWrapper;
        $this->remoteAddress = $remoteAddress;
        $this->_storeManager = $storeManager;

        $ips = $this->remoteAddress->getRemoteAddress();

//        var_dump($ips);
//        die;

        //$ips = '185.128.151.129, 10.0.2.251';
//        $ips = '104.192.143.2';

//        print_r($ips);

        $ips = str_replace(' ', '', $ips);
        $ips = explode(',', $ips);

        foreach ($ips as $k => $ip) {
            $valid = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            if (!$valid) {
                unset($ips[$k]);
            }
        }

        if (is_array($ips) && count($ips) > 0) {
            $ip = $ips[0];

            // BITBUCKET (US)
            //$ip = '104.192.143.2';

            $this->country = $this->getCountryByIp($ip);

            $allowCountries = explode(',', (string)$this->scopeConfig->getValue('general/country/allow', ScopeInterface::SCOPE_STORE));
            $this->addAllowedCountry($allowCountries);
        }

        $this->defaultCountry = (string)$this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);

        if($this->country === null){
            $this->country = $this->defaultCountry;
        }


    }


    /**
     * @param $ip
     * @return bool|mixed|null
     */
    public function getCountryByIp($ip)
    {
        /** @var $wrapper Hevelop_GeoIP_Model_Wrapper */
        $wrapper = $this->geoIPWrapper;
        if ($wrapper->geoip_open($this->localFile, 0)) {
            $country = $wrapper->geoip_country_code_by_addr($ip);
            $wrapper->geoip_close();

            return $country;
        }

        return null;
    }


    /**
     * @return bool|mixed|null
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * @param string $country
     * @return bool
     */
    public function isCountryAllowed($country = '')
    {
        $country = $country ?: $this->country;
        if (count($this->allowed_countries) && $country) {
            return in_array($country, $this->allowed_countries, true);
        }

        return true;
    }


    /**
     * @param string $country
     * @return bool
     */
    public function isDefaultCountry($country = '')
    {
        $country = $country ?: $this->country;
        if (!empty($this->defaultCountry) && $country) {
            return ($this->defaultCountry === $country);
        }

        return false;
    }


    /**
     * @param $countries
     * @return $this
     */
    public function addAllowedCountry($countries)
    {
        $countries = is_array($countries) ? $countries : array($countries);
        $this->allowed_countries = array_merge($this->allowed_countries, $countries);

        return $this;
    }


    /**
     * Determine correct store based on geolocated country.
     *
     * @var string $country
     * @return \Magento\Store\Api\Data\StoreInterface|null
     */
    public function getStoreFromCountry($country)
    {

        /** @var \Magento\Store\Model\ResourceModel\Store\Collection $stores */
        $stores = $this->_storeManager->getStores(false, true);

        /** @var \Magento\Store\Model\Store $store */
        foreach ($stores as $store) {
            $storeCountries = explode(',', (string)$this->scopeConfig->getValue('general/country/allow', ScopeInterface::SCOPE_STORE, $store->getId()));
            if (in_array($country, $storeCountries, true)) {
                return $store;
            }

        }

        return $this->_storeManager->getDefaultStoreView();
    }

}
