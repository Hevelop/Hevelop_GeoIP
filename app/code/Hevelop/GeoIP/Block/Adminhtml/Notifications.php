<?php

namespace Hevelop\GeoIP\Block\Adminhtml;

use Hevelop\GeoIP\Model\InfoFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class Notifications
 * @package Hevelop\GeoIP\Block\Adminhtml
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Notifications extends Template
{

    /**
     * @var InfoFactory
     */
    protected $geoIPInfoFactory;


    /**
     * Notifications constructor.
     * @param InfoFactory $geoIPInfoFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        InfoFactory $geoIPInfoFactory,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->geoIPInfoFactory = $geoIPInfoFactory;
    }


    /**
     * @return mixed
     */
    public function checkFilePermissions()
    {
        /** @var $info Hevelop_GeoIP_Model_Info */
        $info = $this->geoIPInfoFactory->create();
        return $info->checkFilePermissions();
    }

}
