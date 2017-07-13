<?php

namespace Hevelop\GeoIP\Model;

/**
 * Class Info
 * @package Hevelop\GeoIP\Model
 * @category Magento_Module
 * @author   Simone Marcato <simone@hevelop.com>
 * @license  http://opensource.org/licenses/agpl-3.0  GNU Affero General Public License v3 (AGPL-3.0)
 * @link     https://hevelop.com/
 */
class Info extends AbstractClass
{

    /**
     * @return bool|int
     */
    public function getDatFileDownloadDate()
    {
        return file_exists($this->localFile) ? filemtime($this->localFile) : 0;
    }

}