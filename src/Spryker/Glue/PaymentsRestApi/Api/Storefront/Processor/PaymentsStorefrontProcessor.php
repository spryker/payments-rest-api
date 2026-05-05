<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\PaymentsRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\PaymentsStorefrontResource;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\PreOrderPaymentRequestTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Payment\PaymentClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentsStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string MESSAGE_PAYMENT_INITIALIZATION_FAILED = 'Payment initialization failed.';

    public function __construct(
        protected PaymentClientInterface $paymentClient,
    ) {
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function processPost(mixed $data): PaymentsStorefrontResource
    {
        $preOrderPaymentRequestTransfer = (new PreOrderPaymentRequestTransfer())->fromArray(
            [
                'payment' => $data->payment ?? [],
                'quote' => $data->quote ?? [],
            ],
            true,
        );

        $paymentTransfer = (new PaymentTransfer())
            ->setPaymentProviderName($data->payment['paymentProviderName'] ?? null)
            ->setPaymentMethodName($data->payment['paymentMethodName'] ?? null)
            ->setAmount($data->payment['amount'] ?? null);

        $preOrderPaymentRequestTransfer->setPayment($paymentTransfer);
        $preOrderPaymentRequestTransfer->getQuoteOrFail()->setPayment($paymentTransfer);

        $preOrderPaymentResponseTransfer = $this->paymentClient->initializePreOrderPayment($preOrderPaymentRequestTransfer);

        if (!$preOrderPaymentResponseTransfer->getIsSuccessful()) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $preOrderPaymentResponseTransfer->getError() ?? static::MESSAGE_PAYMENT_INITIALIZATION_FAILED,
            );
        }

        $data->isSuccessful = $preOrderPaymentResponseTransfer->getIsSuccessful();
        $data->preOrderPaymentData = $preOrderPaymentResponseTransfer->getPreOrderPaymentData();

        return $data;
    }
}
