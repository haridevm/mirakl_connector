<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Controller\Order;

use GuzzleHttp\Exception\BadResponseException;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Mirakl\MMP\Common\Domain\Order\Message\CreateOrderThread;

class PostIncident extends PostThread implements HttpPostActionInterface
{
    /**
     * Submit incident action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());

            return $resultRedirect;
        }

        $result = $this->initOrders();

        if ($result !== true) {
            return $result;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->registry->registry('current_order');
        /** @var \Mirakl\MMP\FrontOperator\Domain\Order $miraklOrder */
        $miraklOrder = $this->registry->registry('mirakl_order');

        $type = $this->_request->getParam('type');

        $data = $this->getRequest()->getPostValue();

        if (!empty($data)) {
            $orderLineId = $this->_request->getParam('order_line_id');

            try {
                if ($type === 'close') {
                    // Close incident with API OR63
                    $this->orderApi->closeIncident($miraklOrder, $orderLineId, $data['reason']);
                    $this->messageManager->addSuccessMessage(__('Incident has been successfully closed.'));
                } else {
                    // Open incident with API OR62
                    $reasonCode = $data['subject'];
                    $reasonLabel = $data['reasons'][$reasonCode] ?? '';

                    $this->orderApi->openIncident($miraklOrder, $orderLineId, $reasonCode);
                    $this->messageManager->addSuccessMessage(__('Incident has been successfully created.'));

                    // Create a thread for incident with API OR43
                    $messageInput = [
                        'body' => $data['body'],
                        'to'   => $this->getTo($data['recipients']),
                    ];

                    if (!empty($reasonLabel)) {
                        $messageInput['topic'] = [
                            'type'  => 'FREE_TEXT',
                            'value' => $reasonLabel,
                        ];
                    }

                    $files = $this->buildFiles();

                    $this->orderApi->createOrderThread($miraklOrder, new CreateOrderThread($messageInput), $files);

                    $this->session->setFormData([]);
                }
            } catch (BadResponseException $e) {
                $response = \Mirakl\parse_json_response($e->getResponse());
                $message = $response['message'] ?? $e->getMessage();
                $this->session->setFormData($data);
                $this->logger->critical($message);
                $this->messageManager->addErrorMessage(__('An error occurred while sending the message. '
                        . 'Please contact store owner if the problem persists.'));
            } catch (\Exception $e) {
                $this->session->setFormData($data);
                $this->logger->warning($e->getMessage());
                $this->messageManager->addErrorMessage(
                    $type == 'open'
                    ? __('An error occurred while opening an incident.')
                    : __('An error occurred while closing incident.')
                );
            }
        }

        $resultRedirect->setUrl($this->url->getUrl('marketplace/order/view', [
            'order_id'  => $order->getId(),
            'remote_id' => $miraklOrder->getId(),
        ]));

        return $resultRedirect;
    }
}
