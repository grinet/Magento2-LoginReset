<?php
declare(strict_types=1);

namespace Grinet\LoginReset\Model;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\Serializer\Json;
use Grinet\LoginReset\Helper\Config;
use Psr\Log\LoggerInterface;

class CartStorageService
{
    /**
     * @var LogFactory
     */
    private LogFactory $logFactory;

    /**
     * @var CustomerCart
     */
    private CustomerCart $cart;

    /**
     * @var RemoteAddress
     */
    private RemoteAddress $remoteAddress;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LogFactory $logFactory
     * @param CustomerCart $cart
     * @param RemoteAddress $remoteAddress
     * @param Json $json
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        LogFactory $logFactory,
        CustomerCart $cart,
        RemoteAddress $remoteAddress,
        Json $json,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->logFactory = $logFactory;
        $this->cart = $cart;
        $this->remoteAddress = $remoteAddress;
        $this->json = $json;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Save current cart to DB storage and return a lookup token
     *
     * @return string
     */
    public function save(): string
    {
        $quote = $this->cart->getQuote();
        $items = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $buyRequest = $item->getBuyRequest();
            $items[] = [
                'product_id' => (int)$item->getProductId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'qty' => (float)$item->getQty(),
                'buy_request' => $buyRequest->toArray(),
            ];
        }

        $token = $this->generateToken();

        $logData = [
            'token' => $token,
            'remote_ip' => $this->remoteAddress->getRemoteAddress(),
            'cart_content' => $this->json->serialize($items),
            'coupon' => $quote->getCouponCode(),
            'cart_grand_total' => $quote->getGrandTotal(),
        ];

        /** @var Log $log */
        $log = $this->logFactory->create();
        $log->setData($logData);
        $log->save();

        if ($this->config->isLogToFile()) {
            $this->logger->info('Grinet_LoginReset: Cart saved before reset.', $logData);
        }

        return $token;
    }

    /**
     * Load stored cart data by token
     *
     * @param string $token
     * @return array|null
     */
    public function load(string $token): ?array
    {
        /** @var Log $log */
        $log = $this->logFactory->create();
        $log->getResource()->load($log, $token, 'token');

        if (!$log->getId()) {
            return null;
        }

        $cartContent = $log->getData('cart_content');
        return [
            'items' => $this->json->unserialize($cartContent ?: '[]'),
            'coupon' => $log->getData('coupon'),
            'cart_grand_total' => $log->getData('cart_grand_total'),
            'remote_ip' => $log->getData('remote_ip'),
        ];
    }

    /**
     * Delete stored cart data by token
     *
     * @param string $token
     * @return void
     */
    public function delete(string $token): void
    {
        /** @var Log $log */
        $log = $this->logFactory->create();
        $log->getResource()->load($log, $token, 'token');

        if ($log->getId()) {
            $log->getResource()->delete($log);
        }
    }

    /**
     * Generate a unique random token
     *
     * @return string
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
