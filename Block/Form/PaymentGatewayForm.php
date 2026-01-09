<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Atix\PaymentGateway\Block\Form;

/**
 * Block for Custom payment method form
 */
class PaymentGatewayForm extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * Custom payment template
     *
     * @var string
     */
    protected $_template = 'Atix_PaymentGateway::form/PaymentGatewayForm.phtml';
}
