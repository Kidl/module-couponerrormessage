<?php

namespace Kidl\CouponErrorMessage\Plugin;

use Kidl\CouponErrorMessage\Helper\Data as ConfigData;
use Kidl\CouponErrorMessage\Helper\Validator as CouponValidator;
use Magento\Framework\Exception\LocalizedException;

class CheckoutCouponApply
{
    /**
     * Module Helper
     *
     * @var \Kidl\CouponErrorMessage\Helper\Validator
     */

    protected $_couponValidator;
    /**
     * Module helper
     *
     * @var \Kidl\CouponErrorMessage\Helper\Data
     */
    protected $_configData;

    public function __construct(
        CouponValidator $couponValidator,
        ConfigData $configData
    ) {
        $this->_couponValidator = $couponValidator;
        $this->_configData = $configData;
    }

    /**
     * This function runs around the set function for coupon api, once the core function throws
     * exception this function will catch and update the error message.
     *
     * @param \Magento\Quote\Model\CouponManagement $subject
     * @param callable $proceed
     * @param int $cartId
     * @param string $couponCode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundSet(\Magento\Quote\Model\CouponManagement $subject, callable $proceed, $cartId, $couponCode)
    {
        $result = false;

        try {
            $result = $proceed($cartId, $couponCode);
        } catch (\Exception $e) {
            if ($this->_configData->isEnabled()) {
                $msg = $this->_couponValidator->validate($couponCode);
                if (!empty($msg)) {
                    throw new LocalizedException(__($msg));
                }
            }
            throw $e;
        }

        return $result;
    }
}
