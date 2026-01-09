<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Atix\PaymentGateway\Model;

/**
 * Custom payment method model
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 100.0.2
 */
class PaymentGatewayModel extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CUSTOM_PAYMENT_CODE = 'atixpaymentgateway';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CUSTOM_PAYMENT_CODE;

    /**
     * Custom payment block paths
     *
     * @var string
     */
    protected $_formBlockType = \Atix\PaymentGateway\Block\Form\PaymentGateway::class;

    /**
     * Info instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return $this->getData('instructions');
    }
}