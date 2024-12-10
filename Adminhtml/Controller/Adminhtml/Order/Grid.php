<?php

declare(strict_types=1);

namespace Mirakl\Adminhtml\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;

class Grid extends \Magento\Sales\Controller\Adminhtml\Order implements HttpPostActionInterface
{
    /**
     * Mirakl orders grid
     *
     * @inheritdoc
     */
    public function execute()
    {
        $this->_initOrder();
        $resultLayout = $this->resultLayoutFactory->create();

        return $resultLayout;
    }
}
