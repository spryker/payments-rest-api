<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\PaymentsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\PaymentCustomerResponseTransfer;
use Generated\Shared\Transfer\RestAddressAttributesTransfer;
use Generated\Shared\Transfer\RestPaymentCustomersResponseAttributesTransfer;

class PaymentCustomerMapper implements PaymentCustomerMapperInterface
{
    public function mapPaymentCustomerResponseTransferToRestPaymentCustomersResponseAttributesTransfer(
        PaymentCustomerResponseTransfer $paymentCustomerResponseTransfer
    ): RestPaymentCustomersResponseAttributesTransfer {
        $restPaymentCustomersResponseAttributesTransfer = (new RestPaymentCustomersResponseAttributesTransfer())
            ->fromArray($paymentCustomerResponseTransfer->toArray(), true);

        $customerTransfer = $paymentCustomerResponseTransfer->getCustomerOrFail();

        $restCustomerResponseAttributesTransfer = $restPaymentCustomersResponseAttributesTransfer->getCustomerOrFail();
        $restCustomerResponseAttributesTransfer->fromArray($customerTransfer->toArray(), true);

        if ($customerTransfer->getBillingAddress()->offsetExists(0)) {
            $restCustomerResponseAttributesTransfer->setBillingAddress($this->mapAddressTransferToRestAddressAttributesTransfer(
                $customerTransfer->getBillingAddress()->offsetGet(0),
            ));
        }

        if ($customerTransfer->getShippingAddress()->offsetExists(0)) {
            $restCustomerResponseAttributesTransfer->setShippingAddress($this->mapAddressTransferToRestAddressAttributesTransfer(
                $customerTransfer->getShippingAddress()->offsetGet(0),
            ));
        }

        return $restPaymentCustomersResponseAttributesTransfer;
    }

    protected function mapAddressTransferToRestAddressAttributesTransfer(
        AddressTransfer $addressTransfer
    ): RestAddressAttributesTransfer {
        return (new RestAddressAttributesTransfer())
            ->fromArray($addressTransfer->toArray(), true);
    }
}
