<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Glue\PaymentsRestApi\StorefrontApi;

use ApiPlatform\Metadata\Post;
use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Api\Storefront\PaymentCancellationsStorefrontResource;
use Generated\Shared\Transfer\PreOrderPaymentResponseTransfer;
use Spryker\Client\Payment\PaymentClientInterface;
use Spryker\Glue\PaymentsRestApi\Api\Storefront\Processor\PaymentCancellationsStorefrontProcessor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group PaymentsRestApi
 * @group StorefrontApi
 * @group PaymentCancellationsStorefrontProcessorTest
 * Add your own group annotations below this line
 */
class PaymentCancellationsStorefrontProcessorTest extends Unit
{
    protected const string PAYMENT_PROVIDER_NAME = 'DummyPayment';

    protected const string PAYMENT_METHOD_NAME = 'Invoice';

    protected const string TRANSACTION_ID = 'tx_abc123';

    protected const string ERROR_MESSAGE = 'Provider could not cancel the payment.';

    protected const string DEFAULT_ERROR_MESSAGE = 'Payment cancellation failed.';

    public function testGivenClientReturnsSuccessWhenProcessPostThenReturnsResourceWithSuccessFlag(): void
    {
        // Arrange
        $preOrderPaymentResponseTransfer = (new PreOrderPaymentResponseTransfer())
            ->setIsSuccessful(true);

        $paymentClient = $this->createPaymentClientStub('cancelPreOrderPayment', $preOrderPaymentResponseTransfer);
        $processor = new PaymentCancellationsStorefrontProcessor($paymentClient);

        // Act
        $result = $processor->process($this->createResource(), $this->createPostOperation());

        // Assert
        $this->assertInstanceOf(PaymentCancellationsStorefrontResource::class, $result);
        $this->assertTrue($result->getIsSuccessful());
    }

    public function testGivenClientReturnsFailureWithErrorWhenProcessPostThenThrowsHttpExceptionWith422AndProvidedErrorMessage(): void
    {
        // Arrange
        $preOrderPaymentResponseTransfer = (new PreOrderPaymentResponseTransfer())
            ->setIsSuccessful(false)
            ->setError(static::ERROR_MESSAGE);

        $paymentClient = $this->createPaymentClientStub('cancelPreOrderPayment', $preOrderPaymentResponseTransfer);
        $processor = new PaymentCancellationsStorefrontProcessor($paymentClient);

        // Act + Assert
        try {
            $processor->process($this->createResource(), $this->createPostOperation());
            $this->fail('Expected HttpException to be thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getStatusCode());
            $this->assertSame(static::ERROR_MESSAGE, $exception->getMessage());
        }
    }

    public function testGivenClientReturnsFailureWithoutErrorWhenProcessPostThenThrowsHttpExceptionWith422AndDefaultMessage(): void
    {
        // Arrange
        $preOrderPaymentResponseTransfer = (new PreOrderPaymentResponseTransfer())
            ->setIsSuccessful(false);

        $paymentClient = $this->createPaymentClientStub('cancelPreOrderPayment', $preOrderPaymentResponseTransfer);
        $processor = new PaymentCancellationsStorefrontProcessor($paymentClient);

        // Act + Assert
        try {
            $processor->process($this->createResource(), $this->createPostOperation());
            $this->fail('Expected HttpException to be thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getStatusCode());
            $this->assertSame(static::DEFAULT_ERROR_MESSAGE, $exception->getMessage());
        }
    }

    protected function createPaymentClientStub(string $method, PreOrderPaymentResponseTransfer $response): PaymentClientInterface
    {
        return Stub::makeEmpty(PaymentClientInterface::class, [
            $method => $response,
        ]);
    }

    protected function createResource(): PaymentCancellationsStorefrontResource
    {
        $resource = new PaymentCancellationsStorefrontResource();
        $resource->payment = [
            'paymentProviderName' => static::PAYMENT_PROVIDER_NAME,
            'paymentMethodName' => static::PAYMENT_METHOD_NAME,
        ];
        $resource->preOrderPaymentData = [
            'transactionId' => static::TRANSACTION_ID,
        ];

        return $resource;
    }

    protected function createPostOperation(): Post
    {
        return new Post(class: PaymentCancellationsStorefrontResource::class);
    }
}
