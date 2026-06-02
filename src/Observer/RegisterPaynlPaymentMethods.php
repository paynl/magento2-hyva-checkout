<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\Config as PaymentConfig;
use Paynl\HyvaCheckout\Helper\PaymentIcon;
use Paynl\HyvaCheckout\Magewire\Checkout\Payment\Method\GenericPaynlMethodFactory;

class RegisterPaynlPaymentMethods implements ObserverInterface
{
    private const PARENT_BLOCK = 'checkout.payment.methods';
    private const METHOD_PREFIX = 'paynl_payment_';
    private const GENERIC_TEMPLATE = 'Paynl_HyvaCheckout::component/payment/method/generic_paynl_method.phtml';
    private const HYVA_CHECKOUT_HANDLE = 'hyva_checkout';

    private PaymentConfig $paymentConfig;
    private PaymentIcon $paymentIcon;
    private GenericPaynlMethodFactory $genericPaynlMethodFactory;

    /**
     * @param PaymentConfig $paymentConfig
     * @param PaymentIcon $paymentIcon
     * @param GenericPaynlMethodFactory $genericPaynlMethodFactory
     */
    public function __construct(
        PaymentConfig $paymentConfig,
        PaymentIcon $paymentIcon,
        GenericPaynlMethodFactory $genericPaynlMethodFactory
    ) {
        $this->paymentConfig = $paymentConfig;
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

        foreach (array_keys($this->paymentConfig->getMethodsInfo()) as $code) {
            if (!is_string($code) || strpos($code, self::METHOD_PREFIX) !== 0) {
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
