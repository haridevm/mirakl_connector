<?php

declare(strict_types=1);

namespace Mirakl\Event\Controller\Adminhtml\Event;

use Magento\Framework\App\Action\HttpGetActionInterface;

class ShowData extends AbstractEventAction implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $event = $this->getEvent();

        if (!$event->getId()) {
            return $this->redirectError(__('This event no longer exists.'));
        }

        $eventData = $event->getCsvData();

        if (empty($eventData)) {
            return $this->redirectError(__('Data is empty for this event.'));
        }

        if (\Mirakl\array_is_assoc($eventData)) {
            $body = json_encode($eventData, JSON_PRETTY_PRINT);
        } else {
            $csvData = [$eventData];
            array_unshift($csvData, array_keys(reset($csvData)));
            $file = \Mirakl\create_temp_csv_file($csvData);
            $body = @$file->fread($file->fstat()['size']); // phpcs:ignore
        }

        $this->_response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody('<pre>' . htmlentities($body) . '</pre>')
            ->sendResponse();

        return $this->_response;
    }
}
