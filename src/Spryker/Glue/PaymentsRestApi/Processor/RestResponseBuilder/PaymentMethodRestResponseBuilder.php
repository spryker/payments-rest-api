<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\PaymentsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\PreOrderPaymentResponseTransfer;
use Generated\Shared\Transfer\RestCheckoutDataTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\PaymentsRestApi\PaymentsRestApiConfig;
use Spryker\Glue\PaymentsRestApi\Processor\Mapper\PaymentMethodMapperInterface;
use Symfony\Component\HttpFoundation\Response;

class PaymentMethodRestResponseBuilder implements PaymentMethodRestResponseBuilderInterface
{
    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Glue\PaymentsRestApi\Processor\Mapper\PaymentMethodMapperInterface
     */
    protected $paymentMethodMapper;

    public function __construct(
        RestResourceBuilderInterface $restResourceBuilder,
        PaymentMethodMapperInterface $paymentMethodMapper
    ) {
        $this->restResourceBuilder = $restResourceBuilder;
        $this->paymentMethodMapper = $paymentMethodMapper;
    }

    /**
     * @param \Generated\Shared\Transfer\RestCheckoutDataTransfer $restCheckoutDataTransfer
     *
     * @return array<\Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface>
     */
    public function createRestPaymentMethodsResources(RestCheckoutDataTransfer $restCheckoutDataTransfer): array
    {
        $restResources = [];

        $restPaymentMethodsAttributesTransfers = $this->paymentMethodMapper
            ->mapRestCheckoutDataTransferToRestPaymentMethodsAttributesTransfers($restCheckoutDataTransfer);

        foreach ($restPaymentMethodsAttributesTransfers as $idPaymentMethod => $restPaymentMethodsAttributesTransfer) {
            $restResources[] = $this->restResourceBuilder->createRestResource(
                PaymentsRestApiConfig::RESOURCE_PAYMENT_METHODS,
                (string)$idPaymentMethod,
                $restPaymentMethodsAttributesTransfer,
            );
        }

        return $restResources;
    }

    public function createPaymentCancellationsRestResponse(
        PreOrderPaymentResponseTransfer $preOrderPaymentResponseTransfer
    ): RestResponseInterface {
        $restResponse = $this->restResourceBuilder->createRestResponse();
        if ($preOrderPaymentResponseTransfer->getIsSuccessful() === false) {
            return $this->addErrorToRestResponse($preOrderPaymentResponseTransfer->getError(), $restResponse);
        }

        $restResource = $this->restResourceBuilder->createRestResource(
            PaymentsRestApiConfig::RESOURCE_TYPE_PAYMENT_CANCELLATIONS,
            null,
            $preOrderPaymentResponseTransfer,
        );

        return $restResponse->addResource($restResource);
    }

    public function createPaymentsRestResponse(
        PreOrderPaymentResponseTransfer $preOrderPaymentResponseTransfer
    ): RestResponseInterface {
        $restResponse = $this->restResourceBuilder->createRestResponse();
        if ($preOrderPaymentResponseTransfer->getIsSuccessful() === false) {
            return $this->addErrorToRestResponse($preOrderPaymentResponseTransfer->getError(), $restResponse);
        }

        $restResource = $this->restResourceBuilder->createRestResource(
            PaymentsRestApiConfig::RESOURCE_TYPE_PAYMENTS,
            null,
            $preOrderPaymentResponseTransfer,
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
