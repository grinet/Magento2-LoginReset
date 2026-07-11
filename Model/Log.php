<?php
declare(strict_types=1);

namespace Grinet\LoginReset\Model;

use Magento\Framework\Model\AbstractModel;

class Log extends AbstractModel
{
    /**
     * Initialize resource model
     */
    protected function _construct(): void
    {
        $this->_init(\Grinet\LoginReset\Model\ResourceModel\Log::class);
    }
}
