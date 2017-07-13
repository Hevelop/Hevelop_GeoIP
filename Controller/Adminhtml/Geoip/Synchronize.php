<?php

namespace Hevelop\GeoIP\Controller\Adminhtml\Geoip;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Hevelop\GeoIP\Model\InfoFactory;

/**
 * Class Synchronize
 * @package Hevelop\GeoIP\Controller\Adminhtml\Geoip
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Synchronize extends Action
{

    /**
     * @var InfoFactory
     */
    protected $geoIPInfoFactory;


    /**
     * Geoip constructor.
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
        parent::__construct($context);
        $this->geoIPInfoFactory = $geoIPInfoFactory;
    }


    /**
     * Method execute.
     */
    public function execute()
    {
        /** @var $info Hevelop_GeoIP_Model_Info */
        $info = $this->geoIPInfoFactory->create();
        $info->update();
    }

}