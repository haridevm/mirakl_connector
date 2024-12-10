<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Api\Helper\Channel as ChannelApi;
use Mirakl\SalesChannels\Model\Config;
use Mirakl\MMP\Common\Domain\Collection\Channel\ChannelCollection;

class ChannelMapping extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Mirakl_SalesChannels::system/config/form/field/channel_mapping.phtml';

    /**
     * @var ChannelApi
     */
    private $channelApi;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ChannelCollection
     */
    private $miraklChannels;

    /**
     * @param Context $context
     * @param ChannelApi $channelApi
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ChannelApi $channelApi,
        Config $config,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->channelApi = $channelApi;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn('store_code', [
            'label' => __('Store View')
        ]);

        $this->addColumn('channel_code', [
            'label' => __('Mirakl Channel')
        ]);
    }

    /**
     * @return StoreInterface[]
     */
    public function getStores()
    {
        return $this->_storeManager->getStores();
    }

    /**
     * @return ChannelCollection
     */
    public function getMiraklChannels(): ChannelCollection
    {
        if (!isset($this->miraklChannels)) {
            try {
                $this->miraklChannels = $this->channelApi->getChannels();
            } catch (\Exception $e) {
                $this->miraklChannels = new ChannelCollection();
            }
        }

        return $this->miraklChannels;
    }

    /**
     * @param string $storeCode
     * @return string|false
     */
    public function getSelectedChannel(string $storeCode)
    {
        $channelMapping = $this->config->getChannelMapping();
        foreach ($channelMapping as $mappingRow) {
            if ($mappingRow['store_code'] === $storeCode) {
                return $mappingRow['channel_code'] ?? false;
            }
        }

        return false;
    }
}
