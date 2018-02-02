<?php
/**
 *  @category Magento2EE_Project
 *  @project Magento 2 EE
 *  @author   Matteo Manfrin <matteo@hevelop.com>
 *  @copyright Copyright (c) 2017 Hevelop  (https://hevelop.com)
 */

namespace Hevelop\Geoip\Controller\Index;

/**
 * Class ChangeCountry
 * @package Hevelop\GeoIP\Controller
 * @category Magento_Module
 * @author   Matteo Manfrin <matteo@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Response\Http;
use Hevelop\Geoip\Helper\Cookies;

class ChangeCountry extends Action
{

    /**
     * @var Http
     */
    protected $responseHttp;

    /**
     * @var Cookies
     */
    protected $geoipCookiesHelper;

    /**
     * ChangeCountry constructor.
     * @param Context $context
     * @param Http $responseHttp
     * @param Cookies $geoipCookiesHelper
     */
    public function __construct(
        Context $context,
        Http $responseHttp,
        Cookies $geoipCookiesHelper
    )
    {
        $this->responseHttp = $responseHttp;
        $this->geoipCookiesHelper = $geoipCookiesHelper;

        parent::__construct($context);
    }

    public function execute()
    {
        $newCountry = $this->getRequest()->getParam('country_code', null);
        return $this->changeCurrentCountry($newCountry);
    }

    /**
     * @return string
     */
    protected function getCurrentCountry()
    {
        return $this->geoipCookiesHelper->getGeoipCookieCountryValue();
    }

    /**
     * @param $newCountry
     * @return bool|\Magento\Framework\Controller\ResultInterface
     */
    protected function changeCurrentCountry($newCountry){
        if($this->getCurrentCountry() === $newCountry){
            return false;
        }
        $this->geoipCookiesHelper->setGeoipCookieCountry($newCountry);

        $storeLocated = $this->geoipCookiesHelper->getCountryModel()->getStoreFromCountry($newCountry);

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($storeLocated->getBaseUrl());
        $resultRedirect->renderResult($this->responseHttp);
        /**
         * Prevent fatal error on \Magento\Framework\App\PageCache\Kernel:73
         */
        $this->responseHttp->setNoCacheHeaders();
        return $resultRedirect;
    }

}
