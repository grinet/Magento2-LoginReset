<?php
declare(strict_types=1);

namespace Grinet\LoginReset\Block;

use Grinet\LoginReset\Helper\Config;
use Magento\Framework\View\Element\Template;

class LoginHelp extends Template
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * Render block only when module is active
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if (!$this->config->isActive()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get reset URL
     *
     * @return string
     */
    public function getResetUrl(): string
    {
        return $this->getUrl('login_reset/reset');
    }
}
