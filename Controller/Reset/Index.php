<?php
declare(strict_types=1);

namespace Grinet\LoginReset\Controller\Reset;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Grinet\LoginReset\Helper\Config;
use Grinet\LoginReset\Model\CartStorageService;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    private const COOKIE_NAME = 'cart_before_reset';
    private const COOKIE_DURATION = 600;

    /**
     * @var CustomerCart
     */
    private CustomerCart $cart;

    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadataFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CartStorageService
     */
    private CartStorageService $cartStorage;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Context $context
     * @param CustomerCart $cart
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param LoggerInterface $logger
     * @param CartStorageService $cartStorage
     * @param Config $config
     */
    public function __construct(
        Context $context,
        CustomerCart $cart,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        LoggerInterface $logger,
        CartStorageService $cartStorage,
        Config $config
    ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->logger = $logger;
        $this->cartStorage = $cartStorage;
        $this->config = $config;
    }

    /**
     * Save current cart items to cookie, destroy the session and redirect to cart restore
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if (!$this->config->isActive()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $token = '';
        try {
            $token = $this->cartStorage->save();
        } catch (\Exception $e) {
            $this->logger->error('Grinet_LoginReset: Failed to save cart before reset: ' . $e->getMessage());
        }

        if (!empty($token)) {
            try {
                /** @var PublicCookieMetadata $metadata */
                $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setPath('/')
                    ->setDuration(self::COOKIE_DURATION);
                $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $token, $metadata);
            } catch (\Exception $e) {
                $this->logger->error('Grinet_LoginReset: Failed to set reset token cookie: ' . $e->getMessage());
            }
        }

        try {
            $this->customerSession->destroy();
            $this->checkoutSession->destroy();
        } catch (\Exception $e) {
            $this->logger->error('Grinet_LoginReset: Failed to destroy session during reset: ' . $e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setUrl('/login_reset/restore_cart');
    }
}
