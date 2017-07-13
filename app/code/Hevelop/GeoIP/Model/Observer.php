<?php

namespace Hevelop\GeoIP\Model;

/**
 * Class Observer
 * @package Hevelop\GeoIP\Model
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Observer
{

    /**
     * @var CountryFactory
     */
    protected $geoIPCountryFactory;


    /**
     * Observer constructor.
     * @param CountryFactory $geoIPCountryFactory
     */
    public function __construct(
        CountryFactory $geoIPCountryFactory
    )
    {
        $this->geoIPCountryFactory = $geoIPCountryFactory;
    }


    /**
     * @param $observer
     */
    public function controllerFrontInitBefore($observer)
    {
        /** @var Country $country */
        $country = $this->geoIPCountryFactory->create();
    }

}