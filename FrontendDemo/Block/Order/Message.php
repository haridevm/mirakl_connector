<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Order;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic as GenericSession;
use Magento\Framework\View\Element\Template\Context;
use Mirakl\Api\Helper\Order as OrderApi;
use Mirakl\Api\Helper\Reason as ReasonApi;
use Mirakl\Connector\Helper\Order as OrderHelper;
use Mirakl\Connector\Helper\Tax as TaxHelper;
use Mirakl\Core\Helper\Config as CoreConfig;
use Mirakl\MMP\Common\Domain\Collection\Message\OrderMessageCollection;
use Mirakl\MMP\Common\Domain\Message\OrderMessage;
use Mirakl\MMP\Common\Domain\UserType;
use Mirakl\MMP\FrontOperator\Domain\Collection\Reason\ReasonCollection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Message extends View
{
    /**
     * @var GenericSession
     */
    protected $session;

    /**
     * @var OrderApi
     */
    protected $orderApi;

    /**
     * @var ReasonApi
     */
    protected $reasonApi;

    /**
     * @var CoreConfig
     */
    protected $coreConfig;

    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_template = 'order/message.phtml';

    /**
     * @var array
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_postMessage;

    /**
     * @param Context         $context
     * @param Registry        $registry
     * @param GenericSession  $session
     * @param HttpContext     $httpContext
     * @param OrderApi        $orderApi
     * @param ReasonApi       $reasonApi
     * @param CoreConfig      $coreConfig
     * @param OrderHelper     $orderHelper
     * @param TaxHelper       $taxHelper
     * @param CurrencyFactory $currencyFactory
     * @param array           $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        GenericSession $session,
        HttpContext $httpContext,
        OrderApi $orderApi,
        ReasonApi $reasonApi,
        CoreConfig $coreConfig,
        OrderHelper $orderHelper,
        TaxHelper $taxHelper,
        CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $httpContext,
            $orderHelper,
            $taxHelper,
            $currencyFactory,
            $data
        );
        $this->session = $session;
        $this->orderApi = $orderApi;
        $this->reasonApi = $reasonApi;
        $this->coreConfig = $coreConfig;
    }

    /**
     * Get review product post action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl(
            'marketplace/order/postMessage',
            [
                'order_id' => $this->getOrder()->getId(),
                'remote_id' => $this->getMiraklOrder()->getId(),
            ]
        );
    }

    /**
     * Retrieves order messages visible by the customer
     *
     * @return OrderMessageCollection
     */
    public function getMessages()
    {
        return $this->orderApi->getOrderMessages($this->getMiraklOrder(), UserType::CUSTOMER);
    }

    /**
     * Retrieve form data stored in session
     *
     * @param string $field
     * @return array|string
     */
    public function getPostMessage($field = null)
    {
        if ($this->_postMessage === null) {
            $this->_postMessage = $this->session->getFormData(true);
        }

        if ($field) {
            return isset($this->_postMessage[$field]) ? $this->_postMessage[$field] : '';
        }

        return $this->_postMessage;
    }

    /**
     * @return ReasonCollection
     */
    public function getReasons()
    {
        $locale = $this->coreConfig->getLocale();

        return $this->reasonApi->getOrderMessageReasons($locale);
    }

    /**
     * Builds the sender name of the specified order message
     *
     * @param OrderMessage $message
     * @return string
     */
    public function getSenderName(OrderMessage $message)
    {
        return $this->isOperatorMessage($message)
            ? __('Customer Service')
            : $message->getUserSender()->getName();
    }

    /**
     * Returns true if the given message was sent by the customer
     *
     * @param OrderMessage $message
     * @return bool
     */
    public function isCustomerMessage(OrderMessage $message)
    {
        return $message->getUserSender()->getType() == UserType::CUSTOMER;
    }

    /**
     * Returns true if the given message was sent by the operator
     *
     * @param OrderMessage $message
     * @return bool
     */
    public function isOperatorMessage(OrderMessage $message)
    {
        return $message->getUserSender()->getType() == UserType::OPERATOR;
    }
}
