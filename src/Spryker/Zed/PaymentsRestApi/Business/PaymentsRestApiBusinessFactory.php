<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PaymentsRestApi\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\PaymentsRestApi\Business\Quote\PaymentQuoteMapper;
use Spryker\Zed\PaymentsRestApi\Business\Quote\PaymentQuoteMapperInterface;
use Spryker\Zed\PaymentsRestApi\Dependency\Facade\PaymentsRestApiToPaymentFacadeInterface;
use Spryker\Zed\PaymentsRestApi\PaymentsRestApiDependencyProvider;

/**
 * @method \Spryker\Zed\PaymentsRestApi\PaymentsRestApiConfig getConfig()
 */
class PaymentsRestApiBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\PaymentsRestApi\Business\Quote\PaymentQuoteMapperInterface
     */
    public function createPaymentQuoteMapper(): PaymentQuoteMapperInterface
    {
        return new PaymentQuoteMapper(
            $this->getPaymentFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\PaymentsRestApi\Dependency\Facade\PaymentsRestApiToPaymentFacadeInterface
     */
    protected function getPaymentFacade(): PaymentsRestApiToPaymentFacadeInterface
    {
        return $this->getProvidedDependency(PaymentsRestApiDependencyProvider::FACADE_PAYMENT);
    }
}
