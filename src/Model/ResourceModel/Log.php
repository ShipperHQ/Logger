<?php
/**
 * WebShopApps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * WebShopApps Logger
 *
 * @category WebShopApps
 * @package WebShopApps_Logger
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.WebShopApps.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author WebShopApps Team sales@webshopapps.com
 *
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebShopApps\Logger\Model\ResourceModel;


class Log extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('wsalogger_log', 'notification_id');
    }

    public function loadLatestNotice(\WebShopApps\Logger\Model\Log $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->order($this->getIdFieldName() . ' desc')
            ->where('is_read <> 1')
            ->where('is_remove <> 1')
            ->limit(1);
        $data = $connection->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }

    public function getNoticeStatus(\WebShopApps\Logger\Model\Log $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), array(
                'severity'     => 'severity',
                'count_notice' => 'COUNT(' . $this->getIdFieldName() . ')'))
            ->group('severity')
            ->where('is_remove=?', 0)
            ->where('is_read=?', 0);
        $return = array();
        $rowSet = $connection->fetchAll($select);
        foreach ($rowSet as $row) {
            $return[$row['severity']] = $row['count_notice'];
        }
        return $return;
    }

    public function parse(\WebShopApps\Logger\Model\Log $object, array $data)
    {
        $connection = $this->getConnection();
        foreach ($data as $item) {
            $connection->insert($this->getMainTable(), $item);
        }
    }

    public function truncate()
    {
        $connection = $this->getConnection();
        try {
            $connection->truncateTable($this->getMainTable());
        }catch(Exception $e){
            //TODO
          //  Mage::logException("WebShopApps Logger Exception");
          //  Mage::logException($e);
        }
    }
}