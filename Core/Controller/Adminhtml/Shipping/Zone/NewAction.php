<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Shipping\Zone;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Core\Controller\Adminhtml\Shipping\Zone;

class NewAction extends Zone implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
