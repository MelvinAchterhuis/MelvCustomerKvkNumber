<?php declare(strict_types=1);

namespace Melv\CustomerKvkNumber\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\Event\DataMappingEvent;
use Shopware\Core\Checkout\Customer\CustomerEvents;

class MappingRegisterCustomer implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CustomerEvents::MAPPING_REGISTER_CUSTOMER => 'mapKvkNumber',
            CustomerEvents::MAPPING_CUSTOMER_PROFILE_SAVE => 'mapKvkNumber',
        ];
    }

    public function mapKvkNumber(DataMappingEvent $event): bool
    {
        $inputData = $event->getInput();
        $outputData = $event->getOutput();

        $kvkNumber = $inputData->get('kvkNumber', '');
        $outputData['customFields'] = array('melv_customer_kvk' => $kvkNumber);

        $event->setOutput($outputData);
        return true;
    }
}
