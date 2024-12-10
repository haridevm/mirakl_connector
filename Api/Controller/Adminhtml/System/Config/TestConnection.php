<?php
declare(strict_types=1);

namespace Mirakl\Api\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mirakl\Api\Model\Client\Authentication\Method\MethodPoolInterface;

class TestConnection extends Action implements HttpPostActionInterface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Config::api';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var MethodPoolInterface
     */
    private $methodPool;

    /**
     * @param Context             $context
     * @param JsonFactory         $resultJsonFactory
     * @param MethodPoolInterface $methodPool
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        MethodPoolInterface $methodPool
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->methodPool = $methodPool;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            $params = $this->getRequest()->getParams();
            $authMethod = $params['auth_method'] ?? '';
            if (!$authMethod) {
                throw new LocalizedException(__('No authentication method provided'));
            }
            $method = $this->methodPool->get($authMethod);
            $isSuccess = $method->testConnection($params);
            $message = $isSuccess
                ? __('Connection successful!')
                : __('Connection failed!');
        } catch (\Exception $e) {
            $message = __('An error occurred: %1', $e->getMessage());
            $isSuccess = false;
        }

        return $this->resultJsonFactory->create()->setData([
            'success' => $isSuccess,
            'message' => $message->render(),
        ]);
    }
}
