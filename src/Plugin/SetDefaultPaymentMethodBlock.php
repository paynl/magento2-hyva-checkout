<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Plugin;

use Hyva\Checkout\ViewModel\Checkout\Payment\MethodList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\MethodInterface as PaymentMethodInterface;
use Paynl\HyvaCheckout\Model\PaymentMethodCheck;

class SetDefaultPaymentMethodBlock
{
    private const FALLBACK_CODE = 'paynl_payment_default';
    private PaymentMethodCheck $paymentMethodCheck;

    /**
     * @param PaymentMethodCheck $paymentMethodCheck
     */
    public function __construct(PaymentMethodCheck $paymentMethodCheck)
    {
        $this->paymentMethodCheck = $paymentMethodCheck;
    }

    /**
     * @param MethodList $subject
     * @param AbstractBlock|false $result
     * @param Template $block
     * @param PaymentMethodInterface $method
     * @return AbstractBlock|false
     * @throws LocalizedException
     */
    public function afterGetMethodBlock(MethodList $subject, $result, Template $block, PaymentMethodInterface $method)
    {
        return $result;
        if ($result === false && $this->paymentMethodCheck->isPaynlMethod($method->getCode())) {
            $fallback = $block->getChildBlock(self::FALLBACK_CODE);
            if ($fallback) {
                $child = $fallback->getLayout()->createBlock(
                    get_class($fallback),
                    str_replace(self::FALLBACK_CODE, $method->getCode(), $fallback->getNameInLayout()),
                    [
                        'data' => [
                            'magewire' => clone $fallback->getData('magewire'),
                            'template' => $fallback->getTemplate()
                        ]
                    ]
                );

                $child->setData('method', $method);
                $block->setChild($method->getCode(), $child);

                return $child;
            }
        }

        return $result;
    }
}
