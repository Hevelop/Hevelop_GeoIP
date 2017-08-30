<?php

namespace Hevelop\GeoIP\Plugin;

use Hevelop\GeoIP\Helper\Data;
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
    protected $geoipHelper;

    /**
     * AppFrontController constructor.
     * @param StoreManagerInterface $storeManager
     * @param ResultFactory $resultFactory
     * @param Http $response
     * @param Country $country
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResultFactory $resultFactory,
        Http $response,
        Country $country,
        Data $helperData,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_resultFactory = $resultFactory;
        $this->response = $response;
        $this->country = $country;
        $this->geoipHelper = $helperData;
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

        if (!$this->geoipHelper->geoLocationAllowed()) {
            return $proceed($request);
        }

        if ($_SERVER['REQUEST_URI'] === '/') {

            //var_dump($this->country->getCountry());

            //@todo geoip localizzation
            $storeLocated = $this->country->getStoreFromCountry($this->country->getCountry());
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