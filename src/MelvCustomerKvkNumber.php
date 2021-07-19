<?php declare(strict_types=1);

namespace Melv\CustomerKvkNumber;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class MelvCustomerKvkNumber extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        $this->createCustomFields();
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->deleteCustomFields();

        parent::uninstall($uninstallContext);
    }

    private function createCustomFields()
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $customFieldSetUuid = Uuid::randomHex();

        $customFieldSetRepository->upsert([
            [
                'id' => $customFieldSetUuid,
                'name' => 'melv_customer',
                'config' => [
                    'label' => [
                        'en-GB' => 'KvK',
                        'de-DE' => 'KvK',
                        'nl-NL' => 'KvK'
                    ]
                ],
                'customFields' => [
                    [
                        'id' => Uuid::randomHex(),
                        'name' => 'melv_customer_kvk',
                        'type' => CustomFieldTypes::TEXT,
                        'config' => [
                            'type' => 'text',
                            'componentName' => 'sw-field',
                            'customFieldType' => 'text',
                            'customFieldPosition' => 1,
                            'label' => [
                                'en-GB' => 'KvK-number',
                                'de-DE' => 'KvK-number',
                                'nl-NL' => 'KvK-nummer'
                            ]
                        ]
                    ],
                ],
                'relations' => [
                    [
                        'id' => $customFieldSetUuid,
                        'entityName' => $this->container->get(CustomerDefinition::class)->getEntityName()
                    ],
                ]
            ]
        ], Context::createDefaultContext());
    }

    private function deleteCustomFields()
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $entityIds = $customFieldSetRepository->search(
            (new Criteria())->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('name', 'melv_customer'),
            ])),
            Context::createDefaultContext()
        )->getEntities()->getIds();

        if (count($entityIds) < 1) {
            return;
        }

        $entityIds = array_map(function ($element) {
            return ['id' => $element];
        }, array_values($entityIds));

        $customFieldSetRepository->delete(
            $entityIds,
            Context::createDefaultContext()
        );
    }
}
