<?php

declare(strict_types=1);

namespace Mirakl\Process\Ui\Component\Listing;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Mirakl\Process\Ui\Component\GetParentIdInRequestTrait;

class MassAction extends \Magento\Ui\Component\MassAction
{
    use GetParentIdInRequestTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface       $request
     * @param ContextInterface       $context
     * @param UiComponentInterface[] $components
     * @param array                  $data
     */
    public function __construct(
        RequestInterface $request,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($context, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepare(): void
    {
        $parentId = $this->getParentId();
        if ($parentId) {
            foreach ($this->getChildComponents() as $actionComponent) {
                $componentConfig = $actionComponent->getConfiguration();
                $componentConfig['url'] = sprintf(
                    '%s%s/%s/',
                    $componentConfig['url'],
                    'parent_id',
                    $parentId
                );
                $actionComponent->setData('config', $componentConfig);
            }
        }
        parent::prepare();
    }
}
