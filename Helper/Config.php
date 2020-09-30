<?php

namespace Hevelop\GeoIP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config extends AbstractHelper
{
    const XML_GEOIP_LICENSE_KEY = 'hevelop_geoip/general/geoip_license_key';
    const XML_GENERAL_COUNTRY_DEFAULT = 'general/country/default';
    const XML_GENERAL_COUNTRY_ALLOW = 'general/country/allow';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Config constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getLicenseKey()
    {
        return $this->scopeConfig->getValue(self::XML_GEOIP_LICENSE_KEY);
    }

    /**
     * @return string
     */
    public function getRemoteArchiveUrl()
    {
        return 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key='
            . $this->getLicenseKey() . '&suffix=tar.gz';
    }

    /**
     * @return string
     */
    public function getDefaultCountry()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_GENERAL_COUNTRY_DEFAULT,
            ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * @param null $websiteId
     * @return array
     */
    public function getAllowCountries($websiteId = null)
    {
        if ($websiteId === null) {
            try {
                $websiteId = $this->storeManager->getWebsite()->getId();
            } catch (\Exception $e) {
                $websiteId = 0;
            }
        }
        return explode(
            ',',
            (string)$this->scopeConfig->getValue(
                self::XML_GENERAL_COUNTRY_ALLOW,
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            )
        );
    }
}
