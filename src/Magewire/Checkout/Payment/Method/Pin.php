<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Magewire\Checkout\Payment\Method;

class Pin extends Ideal
{
    public const METHOD_CODE = 'paynl_payment_instore';
    protected const DEFAULT_OPTION_MESSAGE = 'Choose the pin terminal';

    protected const ERROR_MESSAGE = 'PIN terminal is required';
}
