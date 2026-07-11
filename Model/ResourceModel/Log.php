<?php
declare(strict_types=1);

namespace Grinet\LoginReset\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Log extends AbstractDb
{
    /**
     * Initialize resource model
     */
    protected function _construct(): void
    {
        $this->_init('grinet_login_reset', 'id');
    }
}
