<?php
namespace Mirakl\Core\Model\Shipping\Type;

use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Api\Helper\Shipping as ShippingHelper;
use Mirakl\Connector\Helper\Config as ConnectorConfig;
use Mirakl\Core\Model\ResourceModel\Shipping\Type as ShippingTypeResource;
use Mirakl\MMP\Common\Domain\Shipping\ShippingTypeWithDescription;
use Mirakl\Process\Model\Process as ProcessModel;
use Psr\Log\LoggerInterface;

class Synchronizer
{
    const CODE = 'SH12';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ShippingHelper
     */
    private $shippingHelper;

    /**
     * @var ConnectorConfig
     */
    private $connectorConfig;

    /**
     * @var ShippingTypeResource
     */
    private $shippingTypeResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ShippingHelper        $shippingHelper
     * @param ConnectorConfig       $connectorConfig
     * @param ShippingTypeResource  $shippingTypeResource
     * @param LoggerInterface       $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ShippingHelper $shippingHelper,
        ConnectorConfig $connectorConfig,
        ShippingTypeResource $shippingTypeResource,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->shippingHelper = $shippingHelper;
        $this->connectorConfig = $connectorConfig;
        $this->shippingTypeResource = $shippingTypeResource;
        $this->logger = $logger;
    }

    /**
     * Synchronize active shipping methods from Mirakl to Magento
     */
    public function synchronize(ProcessModel $process = null)
    {
        try {
            if ($process) {
                $process->output(__('Fetching active shipping methods from Mirakl'));
            }
            $shippingTypes = $this->fetchSippingTypes();
            if ($process) {
                $process->output(__('Saving active shipping methods in Magento'));
            }
            $this->shippingTypeResource->updateShippingTypes($shippingTypes);
            if ($process) {
                $process->output(__('Done!'));
            }
            $this->connectorConfig->setSyncDate('shipping_type');
        } catch (\Exception $e) {
            if ($process) {
                $process->output(__('An error occurred: %1', $e->getMessage()));
            }
            $this->logger->critical($e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch active shipping methods from Mirakl
     * @return array
     */
    private function fetchSippingTypes()
    {
        $locales = [];
        foreach($this->storeManager->getStores() as $store) {
            $locales[] = $this->connectorConfig->getLocale($store);
        }

        $miraklLocales = $this->shippingHelper->getActiveLocales()->walk('getCode');
        $locales = array_intersect($locales, $miraklLocales) ?: $locales;

        // Retrieve shipping methods label and description by other store locales
        $resultShippingTypes = [];
        foreach ($locales as $locale) {
            $shippingTypes = $this->shippingHelper->getActiveShippingTypes($locale);
            foreach ($shippingTypes as $shippingType) {
                $shippingType = $this->extractShippingTypeData($shippingType, $locale);
                if (!isset($resultShippingTypes[$shippingType['code']])) {
                    $resultShippingTypes[$shippingType['code']] = $shippingType;
                } else {
                    $resultShippingTypes[$shippingType['code']]['label'][$locale] = $shippingType['label'][$locale];
                    $resultShippingTypes[$shippingType['code']]['description'][$locale] = $shippingType['description'][$locale];
                }
            }
        }

        return $resultShippingTypes;
    }

    /**
     * @param ShippingTypeWithDescription $shippingType
     * @param string                      $locale
     * @return array
     */
    private function extractShippingTypeData(ShippingTypeWithDescription $shippingType, string $locale)
    {
        return [
            'code'                 => $shippingType->getCode(),
            'click_and_collect'    => (bool) $shippingType->getClickAndCollect(),
            'delivery_by_operator' => (bool) $shippingType->getDeliveryByOperator(),
            'mandatory_tracking'   => (bool) $shippingType->getMandatoryTracking(),
            'label'                => [$locale => $shippingType->getLabel() ?: ''],
            'description'          => [$locale => $shippingType->getDescription() ?: ''],
        ];
    }
}
