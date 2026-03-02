<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\PaymentsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\PaymentCustomerResponseTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\PaymentsRestApi\PaymentsRestApiConfig;
use Spryker\Glue\PaymentsRestApi\Processor\Mapper\PaymentCustomerMapperInterface;
use Symfony\Component\HttpFoundation\Response;

class PaymentCustomerRestResponseBuilder implements PaymentCustomerRestResponseBuilderInterface
{
    public function __construct(
        protected RestResourceBuilderInterface $restResourceBuilder,
        protected PaymentCustomerMapperInterface $paymentCustomerMapper
    ) {
    }

    public function createPaymentCustomersRestResponse(
        PaymentCustomerResponseTransfer $paymentCustomerResponseTransfer
    ): RestResponseInterface {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        if ($paymentCustomerResponseTransfer->getIsSuccessful() === false) {
            return $this->addErrorToRestResponse($paymentCustomerResponseTransfer->getError(), $restResponse);
        }

        $restPaymentCustomerResponseAttributesTransfer = $this->paymentCustomerMapper
            ->mapPaymentCustomerResponseTransferToRestPaymentCustomersResponseAttributesTransfer(
                $paymentCustomerResponseTransfer,
            );

        $restResource = $this->restResourceBuilder->createRestResource(
            PaymentsRestApiConfig::RESOURCE_TYPE_PAYMENT_CUSTOMERS,
            null,
            $restPaymentCustomerResponseAttributesTransfer,
        );

        return $restResponse->addResource($restResource);
    }

    protected function addErrorToRestResponse(
        string $error,
        RestResponseInterface $restResponse
    ): RestResponseInterface {
        $restErrorMessageTransfer = (new RestErrorMessageTransfer())
            ->setDetail($error)
            ->setStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        return $restResponse->addError($restErrorMessageTransfer);
    }
}
