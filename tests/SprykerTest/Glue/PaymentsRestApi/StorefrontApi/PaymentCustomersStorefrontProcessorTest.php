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
use Generated\Api\Storefront\PaymentCustomersStorefrontResource;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaymentCustomerResponseTransfer;
use RuntimeException;
use Spryker\Client\PaymentApp\PaymentAppClientInterface;
use Spryker\Glue\PaymentsRestApi\Api\Storefront\Processor\PaymentCustomersStorefrontProcessor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group PaymentsRestApi
 * @group StorefrontApi
 * @group PaymentCustomersStorefrontProcessorTest
 * Add your own group annotations below this line
 */
class PaymentCustomersStorefrontProcessorTest extends Unit
{
    protected const string PAYMENT_PROVIDER_NAME = 'DummyPayment';

    protected const string PAYMENT_METHOD_NAME = 'Invoice';

    protected const string CUSTOMER_FIRST_NAME = 'Sonia';

    protected const string CUSTOMER_LAST_NAME = 'Wagner';

    protected const string CUSTOMER_EMAIL = 'sonia@spryker.com';

    protected const string PSP_KEY = 'pspCustomerId';

    protected const string PSP_VALUE = 'cus_abc123';

    protected const string ERROR_MESSAGE = 'Customer lookup failed at the PSP.';

    protected const string DEFAULT_ERROR_MESSAGE = 'Payment customer request failed.';

    protected const string METHOD_NOT_FOUND_MESSAGE = 'Payment method not found';

    public function testGivenClientReturnsSuccessWhenProcessPostThenReturnsResourceWithEnrichedCustomer(): void
    {
        // Arrange
        $customerTransfer = (new CustomerTransfer())
            ->setFirstName(static::CUSTOMER_FIRST_NAME)
            ->setLastName(static::CUSTOMER_LAST_NAME)
            ->setEmail(static::CUSTOMER_EMAIL);

        $paymentCustomerResponseTransfer = (new PaymentCustomerResponseTransfer())
            ->setIsSuccessful(true)
            ->setCustomer($customerTransfer);

        $paymentAppClient = $this->createPaymentAppClientStub(['getCustomer' => $paymentCustomerResponseTransfer]);
        $processor = new PaymentCustomersStorefrontProcessor($paymentAppClient);

        // Act
        $result = $processor->process($this->createResource(), $this->createPostOperation());

        // Assert
        $this->assertInstanceOf(PaymentCustomersStorefrontResource::class, $result);
        $this->assertTrue($result->getIsSuccessful());
        $this->assertSame(static::CUSTOMER_EMAIL, $result->customer['email']);
        $this->assertSame(static::CUSTOMER_FIRST_NAME, $result->customer['first_name']);
    }

    public function testGivenClientReturnsFailureWithErrorWhenProcessPostThenThrowsHttpExceptionWith422AndProvidedErrorMessage(): void
    {
        // Arrange
        $paymentCustomerResponseTransfer = (new PaymentCustomerResponseTransfer())
            ->setIsSuccessful(false)
            ->setError(static::ERROR_MESSAGE);

        $paymentAppClient = $this->createPaymentAppClientStub(['getCustomer' => $paymentCustomerResponseTransfer]);
        $processor = new PaymentCustomersStorefrontProcessor($paymentAppClient);

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
        $paymentCustomerResponseTransfer = (new PaymentCustomerResponseTransfer())
            ->setIsSuccessful(false);

        $paymentAppClient = $this->createPaymentAppClientStub(['getCustomer' => $paymentCustomerResponseTransfer]);
        $processor = new PaymentCustomersStorefrontProcessor($paymentAppClient);

        // Act + Assert
        try {
            $processor->process($this->createResource(), $this->createPostOperation());
            $this->fail('Expected HttpException to be thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getStatusCode());
            $this->assertSame(static::DEFAULT_ERROR_MESSAGE, $exception->getMessage());
        }
    }

    public function testGivenClientThrowsExceptionWhenProcessPostThenThrowsHttpExceptionWith422AndPaymentMethodNotFoundMessage(): void
    {
        // Arrange
        $paymentAppClient = $this->createPaymentAppClientStub([
            'getCustomer' => function (): never {
                throw new RuntimeException('boom');
            },
        ]);
        $processor = new PaymentCustomersStorefrontProcessor($paymentAppClient);

        // Act + Assert
        try {
            $processor->process($this->createResource(), $this->createPostOperation());
            $this->fail('Expected HttpException to be thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getStatusCode());
            $this->assertSame(static::METHOD_NOT_FOUND_MESSAGE, $exception->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $stubMethods
     */
    protected function createPaymentAppClientStub(array $stubMethods): PaymentAppClientInterface
    {
        return Stub::makeEmpty(PaymentAppClientInterface::class, $stubMethods);
    }

    protected function createResource(): PaymentCustomersStorefrontResource
    {
        $resource = new PaymentCustomersStorefrontResource();
        $resource->payment = [
            'paymentProviderName' => static::PAYMENT_PROVIDER_NAME,
            'paymentMethodName' => static::PAYMENT_METHOD_NAME,
        ];
        $resource->customer = [
            'firstName' => static::CUSTOMER_FIRST_NAME,
            'lastName' => static::CUSTOMER_LAST_NAME,
            'email' => static::CUSTOMER_EMAIL,
        ];
        $resource->customerPaymentServiceProviderData = [
            static::PSP_KEY => static::PSP_VALUE,
        ];

        return $resource;
    }

    protected function createPostOperation(): Post
    {
        return new Post(class: PaymentCustomersStorefrontResource::class);
    }
}
