<?php

namespace Hevelop\GeoIP\Model;

/**
 * Class Cron
 * @package Hevelop\GeoIP\Model
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Cron
{

    /**
     * @var InfoFactory
     */
    protected $geoIPInfoFactory;

    /**
     * Cron constructor.
     * @param \Hevelop\GeoIP\Model\InfoFactory $geoIPInfoFactory
     */
    public function __construct(
        InfoFactory $geoIPInfoFactory
    ) {
        $this->geoIPInfoFactory = $geoIPInfoFactory;
    }

    /**
     * Method run.
     */
    public function run()
    {
        /** @var $info Info */
        $info = $this->geoIPInfoFactory->create();
        $info->update();
    }

}
