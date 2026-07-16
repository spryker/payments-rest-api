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
use Generated\Api\Storefront\Payments\PaymentsPaymentStorefrontObject;
use Generated\Api\Storefront\Payments\PaymentsQuoteStorefrontObject;
use Generated\Api\Storefront\PaymentsStorefrontResource;
use Generated\Shared\Transfer\PreOrderPaymentResponseTransfer;
use Spryker\Client\Payment\PaymentClientInterface;
use Spryker\Glue\PaymentsRestApi\Api\Storefront\Processor\PaymentsStorefrontProcessor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group PaymentsRestApi
 * @group StorefrontApi
 * @group PaymentsStorefrontProcessorTest
 * Add your own group annotations below this line
 */
class PaymentsStorefrontProcessorTest extends Unit
{
    protected const string PAYMENT_PROVIDER_NAME = 'DummyPayment';

    protected const string PAYMENT_METHOD_NAME = 'Invoice';

    protected const int PAYMENT_AMOUNT = 1000;

    protected const string TRANSACTION_ID = 'tx_abc123';

    protected const string REDIRECT_URL = 'https://psp.example.com/pay/tx_abc123';

    protected const string ERROR_MESSAGE = 'Provider rejected the request.';

    protected const string DEFAULT_ERROR_MESSAGE = 'Payment initialization failed.';

    public function testGivenClientReturnsSuccessWhenProcessPostThenReturnsResourceWithSuccessFlagAndPreOrderPaymentData(): void
    {
        // Arrange
        $preOrderPaymentResponseTransfer = (new PreOrderPaymentResponseTransfer())
            ->setIsSuccessful(true)
            ->setPreOrderPaymentData([
                'transactionId' => static::TRANSACTION_ID,
                'redirectUrl' => static::REDIRECT_URL,
            ]);

        $paymentClient = $this->createPaymentClientStub('initializePreOrderPayment', $preOrderPaymentResponseTransfer);
        $processor = new PaymentsStorefrontProcessor($paymentClient);
        $resource = $this->createResource();

        // Act
        $result = $processor->process($resource, $this->createPostOperation());

        // Assert
        $this->assertInstanceOf(PaymentsStorefrontResource::class, $result);
        $this->assertTrue($result->getIsSuccessful());
        $this->assertSame(
            ['transactionId' => static::TRANSACTION_ID, 'redirectUrl' => static::REDIRECT_URL],
            $result->getPreOrderPaymentData(),
        );
    }

    public function testGivenClientReturnsFailureWithErrorWhenProcessPostThenThrowsHttpExceptionWith422AndProvidedErrorMessage(): void
    {
        // Arrange
        $preOrderPaymentResponseTransfer = (new PreOrderPaymentResponseTransfer())
            ->setIsSuccessful(false)
            ->setError(static::ERROR_MESSAGE);

        $paymentClient = $this->createPaymentClientStub('initializePreOrderPayment', $preOrderPaymentResponseTransfer);
        $processor = new PaymentsStorefrontProcessor($paymentClient);

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

        $paymentClient = $this->createPaymentClientStub('initializePreOrderPayment', $preOrderPaymentResponseTransfer);
        $processor = new PaymentsStorefrontProcessor($paymentClient);

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

    protected function createResource(): PaymentsStorefrontResource
    {
        $resource = new PaymentsStorefrontResource();
        $resource->payment = PaymentsPaymentStorefrontObject::fromArray([
            'paymentProviderName' => static::PAYMENT_PROVIDER_NAME,
            'paymentMethodName' => static::PAYMENT_METHOD_NAME,
            'amount' => static::PAYMENT_AMOUNT,
        ]);
        $resource->quote = PaymentsQuoteStorefrontObject::fromArray([
            'customer' => ['firstName' => 'Sonia', 'lastName' => 'Wagner', 'email' => 'sonia@spryker.com'],
            'billingAddress' => ['iso2Code' => 'DE'],
            'currency' => ['code' => 'EUR'],
        ]);

        return $resource;
    }

    protected function createPostOperation(): Post
    {
        return new Post(class: PaymentsStorefrontResource::class);
    }
}
