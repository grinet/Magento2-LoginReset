<?php
declare(strict_types=1);

namespace Grinet\LoginReset\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_GENERAL_IS_ACTIVE = 'grinet_loginreset/general_settings/is_active';
    private const XML_PATH_LOG_LOG_TO_FILE = 'grinet_loginreset/log_settings/log_to_file';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if module is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GENERAL_IS_ACTIVE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if file logging is enabled
     *
     * @return bool
     */
    public function isLogToFile(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LOG_LOG_TO_FILE, ScopeInterface::SCOPE_STORE);
    }
}
