<?php

namespace Hevelop\GeoIP\Controller\Adminhtml\Geoip;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\Generic;
use Hevelop\GeoIP\Model\InfoFactory;

/**
 * Class Status
 * @package Hevelop\GeoIP\Controller\Adminhtml\Geoip
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Status extends Action
{

    /**
     * @var Generic
     */
    protected $generic;

    /**
     * @var InfoFactory
     */
    protected $geoIPInfoFactory;


    /**
     * Geoip constructor.
     * @param Generic $generic
     * @param InfoFactory $geoIPInfoFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Generic $generic,
        InfoFactory $geoIPInfoFactory,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->generic = $generic;
        $this->geoIPInfoFactory = $geoIPInfoFactory;
    }


    /**
     * Method execute.
     */
    public function execute()
    {
        /** @var $_session Mage_Core_Model_Session */
        $_session = $this->generic;
        /** @var $info Hevelop_GeoIP_Model_Info */
        $info = $this->geoIPInfoFactory->create();

        if(file_exists($info->getArchivePath())){
            $_realSize = filesize($info->getArchivePath());
            $_totalSize = $_session->getData('_geoip_file_size');
            echo $_totalSize ? $_realSize / $_totalSize * 100 : 0;
        }else{
            echo 'No file found.';
        }
    }

}