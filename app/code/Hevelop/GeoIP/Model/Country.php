<?php

namespace Hevelop\GeoIP\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Session\Generic;
use Hevelop\GeoIP\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

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
    protected $country;

    /**
     * @var array
     */
    protected $allowed_countries = [];

    /**
     * @var Wrapper
     */
    protected $geoIPWrapper;


    /**
     * Country constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Wrapper $geoIPWrapper
     * @param Data $geoIPHelper
     * @param Generic $generic
     * @param DirectoryList $directoryList
     * @param TimezoneInterface $_localeDate
     * @param DateTime $date
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
        array $data = []
    )
    {
        parent::__construct($scopeConfig, $geoIPHelper, $generic, $directoryList, $_localeDate, $date);

        $this->geoIPWrapper = $geoIPWrapper;
        $this->country = $this->getCountryByIp(Mage::helper('core/http')->getRemoteAddr());

        $allowCountries = explode(',', (string)$this->scopeConfig->getValue('general/country/allow', ScopeInterface::SCOPE_STORE));
        $this->defaultCountry = (string)$this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
        $this->addAllowedCountry($allowCountries);
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

}
