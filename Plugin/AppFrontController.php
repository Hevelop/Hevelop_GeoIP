<?php

namespace Hevelop\GeoIP\Plugin;

use Hevelop\GeoIP\Helper\Cookies;
use Hevelop\GeoIP\Model\Country;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\ResultFactory;

class AppFrontController
{

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * @var Http
     */
    protected $response;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var Data
     */
    protected $geoipCookieHelper;

    /**
     * AppFrontController constructor.
     * @param StoreManagerInterface $storeManager
     * @param ResultFactory $resultFactory
     * @param Http $response
     * @param Country $country
     * @param Cookies $helperData
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResultFactory $resultFactory,
        Http $response,
        Country $country,
        Cookies $helperData,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_resultFactory = $resultFactory;
        $this->response = $response;
        $this->country = $country;
        $this->geoipCookieHelper = $helperData;
    }

    /**
     * @param FrontControllerInterface $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function aroundDispatch(
        FrontControllerInterface $subject,
        callable $proceed,
        RequestInterface $request
    )
    {
        if (!$this->geoipCookieHelper->geoLocationAllowed()) {
            return $proceed($request);
        }

        if ($_SERVER['REQUEST_URI'] === '/') {
            $geoIpCookie = $this->geoipCookieHelper->getGeoipCookieValue();
            $storeCountry = $this->country->getCountry();

            if (isset($geoIpCookie[Cookies::COUNTRY_CODE_COOKIE_PARAM])
                && strlen((string)$geoIpCookie[Cookies::COUNTRY_CODE_COOKIE_PARAM]) === 2
                && (string)($geoIpCookie[Cookies::COUNTRY_CODE_COOKIE_PARAM]) !== (string)$storeCountry
            ) {
                $storeCountry = $geoIpCookie[Cookies::COUNTRY_CODE_COOKIE_PARAM];
            } else {
                $this->geoipCookieHelper->setGeoipCookieCountry();
            }

            $storeLocated = $this->country->getStoreFromCountry($storeCountry);
            $availableStoreCurrencies = $this->geoipCookieHelper->getStoreCurrencies($storeLocated);

            if (isset($geoIpCookie[Cookies::CURRENCY_CODE_COOKIE_PARAM])
                && strlen((string)$geoIpCookie[Cookies::CURRENCY_CODE_COOKIE_PARAM]) === 3
                && isset($availableStoreCurrencies[(string)$geoIpCookie[Cookies::CURRENCY_CODE_COOKIE_PARAM]])
            ) {
                $storeLocated->setCurrentCurrencyCode((string)$geoIpCookie[Cookies::CURRENCY_CODE_COOKIE_PARAM]);
            }

            $resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($storeLocated->getBaseUrl());
            $resultRedirect->renderResult($this->response);
            /**
             * Prevent fatal error on \Magento\Framework\App\PageCache\Kernel:73
             */
            $this->response->setNoCacheHeaders();
            return $resultRedirect;
        }

        return $proceed($request);
    }

}
