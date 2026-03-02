<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\PaymentsRestApi;

use Spryker\Glue\Kernel\AbstractFactory;
use Spryker\Glue\PaymentsRestApi\Dependency\Client\PaymentsRestApiToPaymentAppClientInterface;
use Spryker\Glue\PaymentsRestApi\Dependency\Client\PaymentsRestApiToPaymentClientInterface;
use Spryker\Glue\PaymentsRestApi\Processor\Expander\PaymentMethodByCheckoutDataExpander;
use Spryker\Glue\PaymentsRestApi\Processor\Expander\PaymentMethodByCheckoutDataExpanderInterface;
use Spryker\Glue\PaymentsRestApi\Processor\Mapper\PaymentCustomerMapper;
use Spryker\Glue\PaymentsRestApi\Processor\Mapper\PaymentCustomerMapperInterface;
use Spryker\Glue\PaymentsRestApi\Processor\Mapper\PaymentMethodMapper;
use Spryker\Glue\PaymentsRestApi\Processor\Mapper\PaymentMethodMapperInterface;
use Spryker\Glue\PaymentsRestApi\Processor\Payment\Payment;
use Spryker\Glue\PaymentsRestApi\Processor\Payment\PaymentInterface;
use Spryker\Glue\PaymentsRestApi\Processor\RestResponseBuilder\PaymentCustomerRestResponseBuilder;
use Spryker\Glue\PaymentsRestApi\Processor\RestResponseBuilder\PaymentCustomerRestResponseBuilderInterface;
use Spryker\Glue\PaymentsRestApi\Processor\RestResponseBuilder\PaymentMethodRestResponseBuilder;
use Spryker\Glue\PaymentsRestApi\Processor\RestResponseBuilder\PaymentMethodRestResponseBuilderInterface;

/**
 * @method \Spryker\Glue\PaymentsRestApi\PaymentsRestApiConfig getConfig()
 */
class PaymentsRestApiFactory extends AbstractFactory
{
    public function createPaymentMethodByCheckoutDataExpander(): PaymentMethodByCheckoutDataExpanderInterface
    {
        return new PaymentMethodByCheckoutDataExpander($this->createPaymentMethodRestResponseBuilder());
    }

    public function createPaymentMethodRestResponseBuilder(): PaymentMethodRestResponseBuilderInterface
    {
        return new PaymentMethodRestResponseBuilder($this->getResourceBuilder(), $this->createPaymentMethodMapper());
    }

    public function createPaymentMethodMapper(): PaymentMethodMapperInterface
    {
        return new PaymentMethodMapper($this->getConfig());
    }

    public function createPaymentCustomerRestResponseBuilder(): PaymentCustomerRestResponseBuilderInterface
    {
        return new PaymentCustomerRestResponseBuilder($this->getResourceBuilder(), $this->createPaymentCustomerMapper());
    }

    public function createPaymentCustomerMapper(): PaymentCustomerMapperInterface
    {
        return new PaymentCustomerMapper();
    }

    public function createPayment(): PaymentInterface
    {
        return new Payment($this->getPaymentClient(), $this->getPaymentAppClient(), $this->createPaymentMethodRestResponseBuilder(), $this->createPaymentCustomerRestResponseBuilder());
    }

    public function getPaymentClient(): PaymentsRestApiToPaymentClientInterface
    {
        return $this->getProvidedDependency(PaymentsRestApiDependencyProvider::CLIENT_PAYMENT);
    }

    public function getPaymentAppClient(): PaymentsRestApiToPaymentAppClientInterface
    {
        return $this->getProvidedDependency(PaymentsRestApiDependencyProvider::CLIENT_PAYMENT_APP);
    }
}
