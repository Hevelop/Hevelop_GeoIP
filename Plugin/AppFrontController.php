<?php

namespace Hevelop\GeoIP\Plugin;

use Hevelop\GeoIP\Helper\Cookies;
use Hevelop\GeoIP\Model\Country;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
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
     *
     * @throws \InvalidArgumentException
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function aroundDispatch(
        FrontControllerInterface $subject,
        callable $proceed,
        RequestInterface $request
    )
    {
        // return $proceed($request);

        if (!$this->geoipCookieHelper->geoLocationAllowed()) {
            return $proceed($request);
        }

        if ($_SERVER['REQUEST_URI'] === '/') {

//            var_dump($this->country->getCountry());

            $geoipCookieValue = $this->geoipCookieHelper->getGeoipCookieCountryValue();
            $storeCountry = $this->country->getCountry();

            if($geoipCookieValue === null){
                $this->geoipCookieHelper->setGeoipCookieCountry();
            }elseif(
                is_string($geoipCookieValue)
                && strlen($geoipCookieValue) === 2
                && $geoipCookieValue !== $this->country
            ) {
                $storeCountry = $geoipCookieValue;
            }

            //@todo geoip localizzation
            $storeLocated = $this->country->getStoreFromCountry($storeCountry);

            //var_dump($storeLocated->getCode());
            //die();
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