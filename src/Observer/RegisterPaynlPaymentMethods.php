<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Paynl\HyvaCheckout\Helper\PaymentIcon;
use Paynl\HyvaCheckout\Magewire\Checkout\Payment\Method\GenericPaynlMethodFactory;

class RegisterPaynlPaymentMethods implements ObserverInterface
{
    private const PARENT_BLOCK = 'checkout.payment.methods';
    private const METHOD_PREFIX = 'paynl_payment_';
    private const GENERIC_TEMPLATE = 'Paynl_HyvaCheckout::component/payment/method/generic_paynl_method.phtml';
    private const HYVA_CHECKOUT_HANDLE = 'hyva_checkout';

    private PaymentMethodListInterface $paymentMethodList;
    private StoreManagerInterface $storeManager;
    private PaymentIcon $paymentIcon;
    private GenericPaynlMethodFactory $genericPaynlMethodFactory;

    /**
     * @param PaymentMethodListInterface $paymentMethodList
     * @param StoreManagerInterface $storeManager
     * @param PaymentIcon $paymentIcon
     * @param GenericPaynlMethodFactory $genericPaynlMethodFactory
     */
    public function __construct(
        PaymentMethodListInterface $paymentMethodList,
        StoreManagerInterface $storeManager,
        PaymentIcon $paymentIcon,
        GenericPaynlMethodFactory $genericPaynlMethodFactory
    ) {
        $this->paymentMethodList = $paymentMethodList;
        $this->storeManager = $storeManager;
        $this->paymentIcon = $paymentIcon;
        $this->genericPaynlMethodFactory = $genericPaynlMethodFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $observer->getData('layout');
        if (!$layout) {
            return;
        }

        if (!in_array(self::HYVA_CHECKOUT_HANDLE, $layout->getUpdate()->getHandles(), true)) {
            return;
        }

        $parent = $layout->getBlock(self::PARENT_BLOCK);
        if (!$parent) {
            return;
        }

        $existingChildren = array_flip($parent->getChildNames());
        $storeId = (int) $this->storeManager->getStore()->getId();

        foreach ($this->paymentMethodList->getActiveList($storeId) as $paymentMethod) {
            $code = $paymentMethod->getCode();
            if (strpos($code, self::METHOD_PREFIX) !== 0) {
                continue;
            }
            $blockName = 'checkout.payment.method.' . $code;
            if (isset($existingChildren[$blockName]) || isset($existingChildren[$code])) {
                continue;
            }

            $block = $layout->createBlock(Template::class, $blockName, [
                'data' => [
                    'magewire' => $this->genericPaynlMethodFactory->create(),
                    'method_code' => $code,
                    'metadata' => $this->buildMetadata($code),
                ],
            ]);
            $block->setTemplate(self::GENERIC_TEMPLATE);

            $parent->setChild($code, $block);
        }
    }

    /**
     * @param string $code
     * @return array[]
     */
    private function buildMetadata(string $code): array
    {
        return [
            'icon' => [
                'src' => $this->paymentIcon->getIconUrl($code),
                'attributes' => [
                    'width' => '35px',
                    'loading' => 'lazy',
                    'alt' => $this->paymentIcon->getIconAltText($code),
                ],
            ],
        ];
    }
}
