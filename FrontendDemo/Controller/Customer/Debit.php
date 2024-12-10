<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Controller\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Debit extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * Debit a Mirakl order
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response->setHttpResponseCode(204)
            ->sendHeaders();

        ob_end_flush();
        flush();

        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $this->_eventManager->dispatch(
            'mirakl_customer_debit',
            ['body' => $request->getContent()]
        );

        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
    }
}
