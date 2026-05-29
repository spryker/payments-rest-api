<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\PaymentsRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\CheckoutDataStorefrontResource;
use Generated\Api\Storefront\PaymentMethodsStorefrontResource;
use Generated\Shared\Transfer\PaymentMethodTransfer;
use Spryker\ApiPlatform\Exception\ApiPlatformContextException;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\Glue\PaymentsRestApi\PaymentsRestApiConfig;
use Spryker\Service\Serializer\SerializerServiceInterface;

class CheckoutDataPaymentMethodsRelationshipResolver extends AbstractRelationshipResolver
{
    public function __construct(
        protected PaymentsRestApiConfig $config,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\ApiPlatformContextException When a parent resource is
     *     not a {@see CheckoutDataStorefrontResource} — this resolver is wired only to that resource
     *     and any other parent type indicates a configuration error in `checkout-data.resource.yml`.
     *
     * @return array<\Generated\Api\Storefront\PaymentMethodsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        $resources = [];

        foreach ($this->getParentResources() as $parent) {
            if (!$parent instanceof CheckoutDataStorefrontResource) {
                throw new ApiPlatformContextException(sprintf(
                    'Resolver "%s" can only resolve "payment-methods" for parents of type "%s", got "%s".',
                    static::class,
                    CheckoutDataStorefrontResource::class,
                    get_debug_type($parent),
                ));
            }

            foreach ($parent->paymentMethodsRelationshipData ?? [] as $paymentMethodData) {
                $resource = $this->buildPaymentMethodResource($paymentMethodData);

                if ($resource !== null) {
                    $resources[] = $resource;
                }
            }
        }

        return $resources;
    }

    /**
     * @param array<string, mixed> $paymentMethodData
     */
    protected function buildPaymentMethodResource(array $paymentMethodData): ?PaymentMethodsStorefrontResource
    {
        $paymentMethodTransfer = (new PaymentMethodTransfer())->fromArray($paymentMethodData, true);
        $paymentProviderTransfer = $paymentMethodTransfer->getPaymentProvider();

        if ($paymentProviderTransfer === null) {
            return null;
        }

        $paymentMethodKey = (string)$paymentMethodTransfer->getMethodName();
        $paymentProviderKey = (string)$paymentProviderTransfer->getPaymentProviderKey();

        return $this->serializer->denormalize(
            [
                'idPaymentMethod' => (string)$paymentMethodTransfer->getIdPaymentMethod(),
                'paymentMethodName' => $paymentMethodTransfer->getName(),
                'paymentProviderName' => $paymentProviderKey,
                'priority' => $this->config->getPaymentMethodPriority()[$paymentMethodKey] ?? null,
                'requiredRequestData' => $this->config->getRequiredRequestDataForPaymentMethod($paymentProviderKey, $paymentMethodKey),
                'paymentMethodAppConfiguration' => $paymentMethodTransfer->getPaymentMethodAppConfiguration()?->toArray(true, true),
            ],
            PaymentMethodsStorefrontResource::class,
        );
    }
}
