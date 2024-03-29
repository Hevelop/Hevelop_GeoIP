<?php

namespace Hevelop\GeoIP\Helper;

use Hevelop\GeoIP\Helper\Data as DataHelper;
use Hevelop\GeoIP\Model\Country;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Archive\Gz;
use Magento\Framework\Archive\Tar;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Cookies
 * @package Hevelop\GeoIP\Helper
 * @category Magento_Module
 * @author   Matteo Manfrin <matteo@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Cookies extends DataHelper
{

    const GEOIP_COOKIE_NAME = 'hevelop_geoip_data';

    const COUNTRY_CODE_COOKIE_PARAM = 'country_code';
    const LANGUAGE_CODE_COOKIE_PARAM = 'language_code';
    const CURRENCY_CODE_COOKIE_PARAM = 'currency_code';

    // cookie duration time 1 week
    const GEOIP_COOKIE_DURATION = 604800;

    const GEOIP_COOKIE_PATH = '/';

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CurrencyInterface
     */
    protected $_currency;

    /**
     * Cookies constructor.
     * @param Context $context
     * @param Gz $gzArchive
     * @param Tar $tarArchive
     * @param Country $country
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $currency
     * @param array $data
     */
    public function __construct(
        Context $context,
        Gz $gzArchive,
        Tar $tarArchive,
        Country $country,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        StoreManagerInterface $storeManager,
        CurrencyInterface $currency,
        array $data = []
    ) {
        parent::__construct($context, $gzArchive, $tarArchive, $data);
        $this->country = $country;
        $this->_cookieManager = $cookieManager;
        $this->_storeManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_storeManager = $storeManager;
        $this->_currency = $currency;
    }

    /**
     * @return string
     */
    public function getGeoipCookieName()
    {
        return self::GEOIP_COOKIE_NAME;
    }

    /**
     * @return int
     */
    public function getGeoipCookieDuration()
    {
        return $this->scopeConfig->getValue('hevelop_geoip/general/geoip_cookie_lifetime', ScopeInterface::SCOPE_STORE) ?: self::GEOIP_COOKIE_DURATION;
    }

    /**
     * @return int
     */
    public function getGeoipCookieDomain()
    {
        $this->_storeManager->getStore(0)->getBaseUrl();
    }

    /**
     * @return array
     */
    protected function getDefaultCookieData()
    {
        $defaultData = [];
        $storeLocated = $this->country->getStoreFromCountry($this->country->getCountry());

        $localeCode = $this->scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORE,
            $storeLocated->getCode()
        );
        $currencyCode = $this->scopeConfig->getValue(
            'currency/options/default',
            ScopeInterface::SCOPE_STORE,
            $storeLocated->getCode()
        );

        $defaultData[self::COUNTRY_CODE_COOKIE_PARAM] = $this->country->getCountry();
        $defaultData[self::LANGUAGE_CODE_COOKIE_PARAM] = $localeCode;
        $defaultData[self::CURRENCY_CODE_COOKIE_PARAM] = $currencyCode;

        return $defaultData;
    }

    /**
     * @return Country
     */
    public function getCountryModel()
    {
        return $this->country;
    }

    public function getGeoipCookieValue()
    {

        if ($this->_cookieManager->getCookie($this->getGeoipCookieName()) === null) {
            $this->setGeoipCookie();
            return $this->getDefaultCookieData();
        }

        //        var_dump(\Zend_Json::decode($this->_cookieManager->getCookie($this->getGeoipCookieName())));

        return \Zend_Json::decode($this->_cookieManager->getCookie($this->getGeoipCookieName()));
    }

    /**
     * @return null|string
     */
    public function getGeoipCookieCountryValue()
    {
        $cookieValue = $this->getGeoipCookieValue();
        return $cookieValue[self::COUNTRY_CODE_COOKIE_PARAM];
    }

    /**
     * @param null $country
     */
    public function setGeoipCookieCountry($country = null)
    {
        if ($country === null) {
            $country = $this->country->getCountry();
        }

        $geoipCookie = $this->getGeoipCookieValue();
        $geoipCookie[self::COUNTRY_CODE_COOKIE_PARAM] = $country;

        $this->setGeoipCookie($geoipCookie);
    }

    /**
     * @param array $data
     */
    public function setGeoipCookie($data = [])
    {
        if (!is_array($data)) {
            return;
        } elseif (empty($data)) {
            $data = $this->getDefaultCookieData();
        }
        $cookieData = \Zend_Json::encode($data);

        $metadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($this->getGeoipCookieDuration())
            ->setPath(self::GEOIP_COOKIE_PATH)
            ->setDomain($this->getGeoipCookieDomain());

        $geoipCookieName = $this->getGeoipCookieName();

        $this->_cookieManager->setPublicCookie(
            $geoipCookieName,
            $cookieData,
            $metadata
        );
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreCurrencies($store)
    {
        $currencies = explode(
            ',',
            $this->scopeConfig->getValue(
                'currency/options/allow',
                ScopeInterface::SCOPE_WEBSITE,
                $store->getWebsite()->getCode()
            )
        );
        $storeCurrencyCode = $this->scopeConfig->getValue(
            'currency/options/default',
            ScopeInterface::SCOPE_WEBSITE,
            $store->getWebsite()->getCode()
        );
        $storeCurrencyName = $this->_currency->getCurrency($storeCurrencyCode)->getName();

        $currenciesList = [];
        $defaultCurrencyArray = [$storeCurrencyCode => $storeCurrencyName];
        foreach ($currencies as $currency_code) {
            if ($currency_code !== $storeCurrencyCode && !array_key_exists($currency_code, $currenciesList)) {
                $currenciesList[$currency_code] = $this->_currency->getCurrency($currency_code)->getName();
            }
        }
        $currenciesList = $defaultCurrencyArray + $currenciesList;
        return $currenciesList;
    }
}
