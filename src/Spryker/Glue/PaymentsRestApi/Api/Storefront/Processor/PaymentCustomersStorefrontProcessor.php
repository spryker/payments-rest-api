<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\PaymentsRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\PaymentCustomersStorefrontResource;
use Generated\Shared\Transfer\PaymentCustomerRequestTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\PaymentApp\PaymentAppClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class PaymentCustomersStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string MESSAGE_PAYMENT_CUSTOMER_REQUEST_FAILED = 'Payment customer request failed.';

    protected const string MESSAGE_PAYMENT_METHOD_NOT_FOUND = 'Payment method not found';

    public function __construct(
        protected PaymentAppClientInterface $paymentAppClient,
    ) {
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function processPost(mixed $data): PaymentCustomersStorefrontResource
    {
        $paymentCustomerRequestTransfer = (new PaymentCustomerRequestTransfer())->fromArray(
            [
                'customer' => $data->customer ?? [],
                'payment' => $data->payment ?? [],
                'customerPaymentServiceProviderData' => $data->customerPaymentServiceProviderData ?? [],
            ],
            true,
        );

        try {
            $paymentCustomerResponseTransfer = $this->paymentAppClient->getCustomer($paymentCustomerRequestTransfer);
        } catch (Throwable $exception) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, static::MESSAGE_PAYMENT_METHOD_NOT_FOUND, $exception);
        }

        if (!$paymentCustomerResponseTransfer->getIsSuccessful()) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $paymentCustomerResponseTransfer->getError() ?? static::MESSAGE_PAYMENT_CUSTOMER_REQUEST_FAILED,
            );
        }

        $data->isSuccessful = $paymentCustomerResponseTransfer->getIsSuccessful();
        $data->customer = $paymentCustomerResponseTransfer->getCustomer()?->toArray() ?? [];

        return $data;
    }
}
