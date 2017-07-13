<?php

namespace Hevelop\GeoIP\Block\System\Config;

use Hevelop\GeoIP\Helper\Data;
use Hevelop\GeoIP\Model\Info;
use Hevelop\GeoIP\Model\InfoFactory;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Status
 * @package Hevelop\GeoIP\Block\System\Config
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Status extends Field
{

    /**
     * DateTime
     *
     * @var DateTime
     */
    protected $_date;


    /**
     * @var InfoFactory
     */
    protected $geoIPInfoFactory;


    /**
     * Status constructor.
     * @param InfoFactory $geoIPInfoFactory
     * @param Context $context
     * @param DateTime $date
     * @param array $data
     */
    public function __construct(
        InfoFactory $geoIPInfoFactory,
        Context $context,
        DateTime $date,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->geoIPInfoFactory = $geoIPInfoFactory;
        $this->_date = $date;
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }


    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        /** @var $info Info */
        $info = $this->geoIPInfoFactory->create();
        if ($date = $info->getDatFileDownloadDate()) {
            $date = $this->_date->date(Data::DATE_FORMAT, $date);
        } else {
            $date = '-';
        }
        return '<div id="sync_update_date">' . $date . '</div>';
    }

}
