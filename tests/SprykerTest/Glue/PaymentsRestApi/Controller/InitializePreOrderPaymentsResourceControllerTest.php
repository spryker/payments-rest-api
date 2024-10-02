<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\PaymentsRestApi\Controller;

use Codeception\Stub;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;
use Generated\Shared\Transfer\PreOrderPaymentResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\RestPaymentTransfer;
use Generated\Shared\Transfer\RestPreOrderPaymentRequestAttributesTransfer;
use Spryker\Client\Payment\PaymentClient;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\PaymentsRestApi\Controller\PreOrderPaymentsResourceController;
use Spryker\Glue\PaymentsRestApi\Dependency\Client\PaymentsRestApiToPaymentClientBridge;
use Spryker\Glue\PaymentsRestApi\PaymentsRestApiDependencyProvider;
use SprykerTest\Glue\PaymentsRestApi\PaymentsRestApiControllerTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group PaymentsRestApi
 * @group Controller
 * @group InitializePreOrderPaymentsResourceControllerTest
 * Add your own group annotations below this line
 */
class InitializePreOrderPaymentsResourceControllerTest extends Unit
{
    /**
     * @var \SprykerTest\Glue\PaymentsRestApi\PaymentsRestApiControllerTester
     */
    protected PaymentsRestApiControllerTester $tester;

    /**
     * @return void
     */
    public function testRequestInitializePreOrderPaymentReturnsCreatedHttpResponseWithPaymentProviderData(): void
    {
        // Arrange
        $preOrderPaymentResponseTransfer = new PreOrderPaymentResponseTransfer();
        $preOrderPaymentResponseTransfer
            ->setIsSuccessful(true)
            ->setPreOrderPaymentData([
                'foo' => 'bar',
            ]);

        $paymentClientStub = Stub::make(PaymentClient::class, [
            'initializePreOrderPayment' => $preOrderPaymentResponseTransfer,
        ]);

        $paymentsRestApiToPaymentClientBridge = new PaymentsRestApiToPaymentClientBridge($paymentClientStub);
        $this->tester->setDependency(PaymentsRestApiDependencyProvider::CLIENT_PAYMENT, $paymentsRestApiToPaymentClientBridge);

        $preOrderPaymentsResourceController = new PreOrderPaymentsResourceController();

        $restPaymentTransfer = new RestPaymentTransfer();
        $restPaymentTransfer
            ->setAmount(10000)
            ->setPaymentMethodName('foo')
            ->setPaymentProviderName('bar');

        $restPreOrderPaymentRequestAttributesTransfer = new RestPreOrderPaymentRequestAttributesTransfer();
        $restPreOrderPaymentRequestAttributesTransfer
            ->setQuote(new QuoteTransfer())
            ->setPayment($restPaymentTransfer)
            ->setPreOrderPaymentData([
                'foo' => 'bar',
            ]);

        $restRequestStub = Stub::makeEmpty(RestRequestInterface::class);

        //Act
        $restResponse = $preOrderPaymentsResourceController->postAction(
            $restRequestStub,
            $restPreOrderPaymentRequestAttributesTransfer,
        );

        //Assert
        $this->assertCount(0, $restResponse->getErrors());
        $this->assertCount(1, $restResponse->getResources());

        $restResource = $restResponse->getResources()[0];

        /** @var \Generated\Shared\Transfer\PreOrderPaymentResponseTransfer $attributes */
        $attributes = $restResource->getAttributes();

        $this->assertSame($preOrderPaymentResponseTransfer->getPreOrderPaymentData(), $attributes->getPreOrderPaymentData());
    }

    /**
     * @return void
     */
    public function testRequestInitializePreOrderPaymentReturnsUnprocessableHttpResponseWhenPaymentMethodWasNotFound(): void
    {
        // Arrange
        $preOrderPaymentResponseTransfer = new PreOrderPaymentResponseTransfer();
        $preOrderPaymentResponseTransfer
            ->setIsSuccessful(false)
            ->setError('Could not find a payment method matching your request.');

        $paymentClientStub = Stub::make(PaymentClient::class, [
            'initializePreOrderPayment' => $preOrderPaymentResponseTransfer,
        ]);

        $paymentsRestApiToPaymentClientBridge = new PaymentsRestApiToPaymentClientBridge($paymentClientStub);
        $this->tester->setDependency(PaymentsRestApiDependencyProvider::CLIENT_PAYMENT, $paymentsRestApiToPaymentClientBridge);

        $preOrderPaymentsResourceController = new PreOrderPaymentsResourceController();

        $restPaymentTransfer = new RestPaymentTransfer();
        $restPaymentTransfer
            ->setAmount(10000)
            ->setPaymentMethodName('foo')
            ->setPaymentProviderName('bar');

        $restPreOrderPaymentRequestAttributesTransfer = new RestPreOrderPaymentRequestAttributesTransfer();
        $restPreOrderPaymentRequestAttributesTransfer
            ->setQuote(new QuoteTransfer())
            ->setPayment($restPaymentTransfer)
            ->setPreOrderPaymentData([
                'foo' => 'bar',
            ]);

        $restRequestStub = Stub::makeEmpty(RestRequestInterface::class);

        //Act
        $restResponse = $preOrderPaymentsResourceController->postAction(
            $restRequestStub,
            $restPreOrderPaymentRequestAttributesTransfer,
        );

        //Assert
        $this->assertCount(1, $restResponse->getErrors());

        $restErrorMessageTransfer = $restResponse->getErrors()[0];

        $this->assertSame(HttpCode::UNPROCESSABLE_ENTITY, $restErrorMessageTransfer->getStatus());
    }
}
