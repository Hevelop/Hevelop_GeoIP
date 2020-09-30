<?php

namespace Hevelop\GeoIP\Controller\Adminhtml\Geoip;

use Hevelop\GeoIP\Model\InfoFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class Synchronize
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
    ) {
        parent::__construct($context);
        $this->geoIPInfoFactory = $geoIPInfoFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Hevelop\GeoIP\Model\Info $info */
        $info = $this->geoIPInfoFactory->create();
        $info->update();
    }
}
