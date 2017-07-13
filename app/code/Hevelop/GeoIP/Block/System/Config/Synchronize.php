<?php

namespace Hevelop\GeoIP\Block\System\Config;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\MediaStorage\Model\File\Storage;
use Magento\MediaStorage\Model\File\Storage\Flag;

/**
 * Class Synchronize
 * @package Hevelop\GeoIP\Block\System\Config
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Synchronize extends Field
{

    /**
     * @var UrlInterface
     */
    protected $backendUrlInterface;

    /**
     * @var Storage
     */
    protected $mediaStorageFileStorage;


    /**
     * Synchronize constructor.
     * @param UrlInterface $backendUrlInterface
     * @param Storage $mediaStorageFileStorage
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        UrlInterface $backendUrlInterface,
        Storage $mediaStorageFileStorage,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->backendUrlInterface = $backendUrlInterface;
        $this->mediaStorageFileStorage = $mediaStorageFileStorage;
        $this->setTemplate('Hevelop_GeoIP::hevelop/geoip/system/config/synchronize.phtml');
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
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }


    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxSyncUrl()
    {
        return $this->backendUrlInterface->getUrl('adminhtml/geoip/synchronize');
    }


    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxStatusUpdateUrl()
    {
        return $this->backendUrlInterface->getUrl('adminhtml/geoip/status');
    }


    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {

        /** @var Button $button */
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'collect_button',
                'label' => __('Synchronize'),
                'onclick' => 'javascript:synchronize(); return false;',
            ]
        );

        return $button->toHtml();
    }


    /**
     * Retrieve last sync params settings
     *
     * Return array format:
     * array (
     *  => storage_type     int,
     *  => connection_name  string
     * )
     *
     * @return array
     */
    public function getSyncStorageParams()
    {
        $flag = $this->mediaStorageFileStorage->getSyncFlag();
        $flagData = $flag->getFlagData();

        if ($flag->getState() == Flag::STATE_NOTIFIED
            && is_array($flagData)
            && isset($flagData['destination_storage_type']) && $flagData['destination_storage_type'] != ''
            && isset($flagData['destination_connection_name'])
        ) {
            $storageType = $flagData['destination_storage_type'];
            $connectionName = $flagData['destination_connection_name'];
        } else {
            $storageType = Storage::STORAGE_MEDIA_FILE_SYSTEM;
            $connectionName = '';
        }

        return [
            'storage_type' => $storageType,
            'connection_name' => $connectionName
        ];

    }

}
