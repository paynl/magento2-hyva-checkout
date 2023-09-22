<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Plugin;

use Hyva\Checkout\Model\MethodMetaDataInterface;
use Paynl\HyvaCheckout\Model\PaymentMethodCheck;
use Paynl\HyvaCheckout\Model\Renderer\PaymentIconRenderer;
use Paynl\Payment\Model\Config;

class HandlePaymentMethodIcons
{
    private Config $paynlConfig;
    private PaymentIconRenderer $paymentIconRenderer;
    private PaymentMethodCheck $paymentMethodCheck;

    /**
     * @param Config $paynlConfig
     * @param PaymentIconRenderer $paymentIconRenderer
     * @param PaymentMethodCheck $paymentMethodCheck
     */
    public function __construct(
        Config $paynlConfig,
        PaymentIconRenderer $paymentIconRenderer,
        PaymentMethodCheck $paymentMethodCheck
    ) {
        $this->paynlConfig = $paynlConfig;
        $this->paymentIconRenderer = $paymentIconRenderer;
        $this->paymentMethodCheck = $paymentMethodCheck;
    }
    /**
     * @param MethodMetaDataInterface $subject
     * @param bool $result
     * @return bool
     */
    public function afterCanRenderIcon(MethodMetaDataInterface $subject, bool $result): bool
    {
        $method = $subject->getMethod()->getCode();

        if ($this->paymentMethodCheck->isPaynlMethod($method)) {
            return true;
        }

        return $result;
    }

    /**
     * @param MethodMetaDataInterface $subject
     */
    public function beforeRenderIcon(MethodMetaDataInterface $subject)
    {
        $method = $subject->getMethod()->getCode();

        if (!$this->paymentMethodCheck->isPaynlMethod($method)) {
            return;
        }

        $iconPath = $this->paynlConfig->getIconUrl($method);

        if ($iconPath) {
            $subject->setData(MethodMetaDataInterface::ICON, [
                'src' => $iconPath,
                'alt' => $subject->getMethod()->getTitle()
            ]);
        }
    }

    /**
     * @param MethodMetaDataInterface $subject
     * @param string $result
     * @return string
     */
    public function afterRenderIcon(MethodMetaDataInterface $subject, string $result): string
    {
        if (!empty($result)) {
            return $result;
        }

        $icon = $subject->getData(MethodMetaDataInterface::ICON);

        if (array_key_exists('src', $icon)) {
            return $this->paymentIconRenderer->render($icon['src'], $icon['alt']);
        }

        return $result;
    }
}
