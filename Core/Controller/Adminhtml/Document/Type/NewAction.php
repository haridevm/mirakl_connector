<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Document\Type;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Core\Controller\Adminhtml\Document\Type;

class NewAction extends Type implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
