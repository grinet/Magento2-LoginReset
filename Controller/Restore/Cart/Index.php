<?php
declare(strict_types=1);

namespace Grinet\LoginReset\Controller\Restore\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Grinet\LoginReset\Model\CartStorageService;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    private const COOKIE_NAME = 'cart_before_reset';

    /**
     * @var CustomerCart
     */
    private CustomerCart $cart;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

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
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @var CartStorageService
     */
    private CartStorageService $cartStorage;

    /**
     * @param Context $context
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param CartStorageService $cartStorage
     */
    public function __construct(
        Context $context,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        CartStorageService $cartStorage
    ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->cartStorage = $cartStorage;
    }

    /**
     * Restore cart items from cookie and redirect to login
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $token = $this->cookieManager->getCookie(self::COOKIE_NAME) ?? '';

        try {
            if (!empty($token)) {
                $data = $this->cartStorage->load($token);
                if (is_array($data)) {
                    foreach ($data['items'] as $item) {
                        $this->restoreItem($item);
                    }
                    $this->cart->save();

                    if (!empty($data['coupon'])) {
                        $this->applyCoupon($data['coupon']);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Grinet_LoginReset: Failed to restore cart after reset: ' . $e->getMessage());
        } finally {
            $this->deleteCookie();
        }

        return $this->resultRedirectFactory->create()->setPath('customer/account/login');
    }

    /**
     * Apply saved coupon to restored cart
     *
     * @param string $couponCode
     * @return void
     */
    private function applyCoupon(string $couponCode): void
    {
        try {
            $cartQuote = $this->cart->getQuote();
            $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            $cartQuote->setCouponCode($couponCode)->collectTotals();
            $this->quoteRepository->save($cartQuote);
        } catch (\Exception $e) {
            $this->logger->error('Grinet_LoginReset: Failed to apply coupon during restore: ' . $couponCode . ' - ' . $e->getMessage());
        }
    }

    /**
     * Restore a single cart item
     *
     * @param array $item
     * @return void
     */
    private function restoreItem(array $item): void
    {
        $productId = (int)($item['product_id'] ?? 0);
        $buyRequest = $item['buy_request'] ?? [];

        if (!$productId) {
            return;
        }

        try {
            $product = $this->productRepository->getById($productId);
            $request = new DataObject($buyRequest);
            $this->cart->addProduct($product, $request);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning('Grinet_LoginReset: Product not found during restore: ' . $productId);
        } catch (\Exception $e) {
            $this->logger->error(
                'Grinet_LoginReset: Failed to add product to cart during restore: ' . $productId . ' - ' . $e->getMessage()
            );
        }
    }

    /**
     * Delete the temporary cookie
     *
     * @return void
     */
    private function deleteCookie(): void
    {
        try {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata()->setPath('/');
            $this->cookieManager->deleteCookie(self::COOKIE_NAME, $metadata);
        } catch (\Exception $e) {
            $this->logger->error('Grinet_LoginReset: Failed to delete cookie: ' . $e->getMessage());
        }
    }
}
