<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\PaymentsRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\PaymentCancellationsStorefrontResource;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\PreOrderPaymentRequestTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Payment\PaymentClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentCancellationsStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string MESSAGE_PAYMENT_CANCELLATION_FAILED = 'Payment cancellation failed.';

    public function __construct(
        protected PaymentClientInterface $paymentClient,
    ) {
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function processPost(mixed $data): PaymentCancellationsStorefrontResource
    {
        $preOrderPaymentRequestTransfer = (new PreOrderPaymentRequestTransfer())->fromArray(
            [
                'payment' => $data->payment ?? [],
                'preOrderPaymentData' => $data->preOrderPaymentData ?? [],
            ],
            true,
        );

        $paymentTransfer = (new PaymentTransfer())
            ->setPaymentProviderName($data->payment['paymentProviderName'] ?? null)
            ->setPaymentMethodName($data->payment['paymentMethodName'] ?? null);

        $preOrderPaymentRequestTransfer->setPayment($paymentTransfer);

        $preOrderPaymentResponseTransfer = $this->paymentClient->cancelPreOrderPayment($preOrderPaymentRequestTransfer);

        if (!$preOrderPaymentResponseTransfer->getIsSuccessful()) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $preOrderPaymentResponseTransfer->getError() ?? static::MESSAGE_PAYMENT_CANCELLATION_FAILED,
            );
        }

        $data->isSuccessful = $preOrderPaymentResponseTransfer->getIsSuccessful();

        return $data;
    }
}
