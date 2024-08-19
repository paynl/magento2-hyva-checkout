<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\ScopeInterface;
use Paynl\Payment\Model\Config;

class PaymentIcon extends AbstractHelper
{
    protected ?array $paymentMethodList = null;
    protected Config $paynlConfig;
    protected PaymentHelper $paymentHelper;

    /**
     * @param Context $context
     * @param Config $paynlConfig
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        Context $context,
        Config $paynlConfig,
        PaymentHelper $paymentHelper
    ) {
        parent::__construct($context);
        $this->paynlConfig = $paynlConfig;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @param string $methodCode
     * @return mixed
     */
    public function getIconUrl(string $methodCode)
    {
        $brandId = $this->scopeConfig->getValue('payment/' . $methodCode . '/brand_id', ScopeInterface::SCOPE_STORE);
        if (empty($brandId)) {
            $brandId = $this->paynlConfig->brands[$methodCode] ?? null;
        }

        return sprintf('Paynl_Payment::logos/%s.png', $brandId);
    }

    /**
     * @param string $methodCode
     * @return mixed|string
     */
    public function getIconAltText(string $methodCode)
    {
        if ($this->paymentMethodList === null) {
            $this->paymentMethodList = $this->paymentHelper->getPaymentMethodList();
        }

        return $this->paymentMethodList[$methodCode] ?? $methodCode;
    }
}
