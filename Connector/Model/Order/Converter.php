<?php
namespace Mirakl\Connector\Model\Order;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory as OrderItemTaxFactory;
use Mirakl\Connector\Helper\Config;
use Mirakl\MMP\Common\Domain\Collection\Order\Tax\OrderTaxAmountCollection;
use Mirakl\MMP\Common\Domain\Order\CustomerBillingAddress;
use Mirakl\MMP\Common\Domain\Order\Tax\OrderTaxAmount;
use Mirakl\MMP\Common\Domain\Order\Tax\OrderTaxMode;
use Mirakl\MMP\Front\Domain\Collection\Order\Create\CreateOrderOfferCollection;
use Mirakl\MMP\Front\Domain\Order\Create\CreateOrder;
use Mirakl\MMP\Front\Domain\Order\Create\CreateOrderOffer;
use Mirakl\MMP\Front\Domain\Order\Create\CreateOrderPaymentInfo;
use Mirakl\MMP\FrontOperator\Domain\Order\CustomerShippingAddress;
use Mirakl\MMP\FrontOperator\Domain\Order\OrderCustomer;

class Converter
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var OrderItemTaxFactory
     */
    protected $orderItemTaxFactory;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var array
     */
    protected $orderItemsTaxes = [];

    /**
     * @var GetOrderCustomerId
     */
    protected $getOrderCustomerId;

    /**
     * @param Config                      $config
     * @param CountryFactory              $countryFactory
     * @param OrderItemTaxFactory         $orderItemTaxFactory
     * @param EventManagerInterface       $eventManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param GetOrderCustomerId          $getOrderCustomerId
     */
    public function __construct(
        Config $config,
        CountryFactory $countryFactory,
        OrderItemTaxFactory $orderItemTaxFactory,
        EventManagerInterface $eventManager,
        CustomerRepositoryInterface $customerRepository,
        GetOrderCustomerId $getOrderCustomerId
    ) {
        $this->config = $config;
        $this->countryFactory = $countryFactory;
        $this->orderItemTaxFactory = $orderItemTaxFactory;
        $this->eventManager = $eventManager;
        $this->customerRepository = $customerRepository;
        $this->getOrderCustomerId = $getOrderCustomerId;
    }

    /**
     * Transforms Magento order data into Mirakl format
     *
     * @param   Order   $order
     * @return  CreateOrder
     */
    public function convert(Order $order)
    {
        $this->eventManager->dispatch('mirakl_connector_convert_order_before', [
            'order' => $order
        ]);

        // Create order object to send to Mirakl
        $createOrder = new CreateOrder();
        $createOrder
            ->setCommercialId($order->getIncrementId())
            ->setShippingZoneCode($order->getMiraklShippingZone())
            ->setScored((bool) $order->getMiraklAutoScore());

        // Create customer to associate with the order
        $orderCustomer = $this->createOrderCustomer($order);

        // Assign billing address to the customer
        $billingAddress = $this->createCustomerBillingAddress($order);
        $orderCustomer->setBillingAddress($billingAddress);

        // Assign shipping address to the customer
        $shippingAddress = $this->createCustomerShippingAddress($order);
        $orderCustomer->setShippingAddress($shippingAddress);

        // Assign customer to the order to send
        $createOrder->setCustomer($orderCustomer);

        // Assign offers to the order to send
        $offerList = $this->createOffers($order);
        $createOrder->setOffers($offerList);

        // Specify the order tax mode explicitly
        $createOrder->setOrderTaxMode($this->getOrderTaxMode($order));

        // Create payment information to associate to the order
        $payment = $order->getPayment();
        if ($payment) {
            $paymentInfo = new CreateOrderPaymentInfo();
            $paymentInfo->setPaymentType($payment->getMethod());

            // Assign payment information to the order to send
            $createOrder->setPaymentInfo($paymentInfo);
        }

        // Assign payment workflow used in configuration
        $createOrder->setPaymentWorkflow($this->config->getPaymentWorkflow());

        $this->eventManager->dispatch('mirakl_connector_convert_order_after', [
            'order'        => $order,
            'create_order' => $createOrder,
        ]);

        return $createOrder;
    }

    /**
     * @param   Order   $order
     * @return  CustomerBillingAddress
     */
    protected function createCustomerBillingAddress(Order $order)
    {
        $customerBillingAddress = new CustomerBillingAddress();

        if (!$billingAddress = $order->getBillingAddress()) {
            return $customerBillingAddress;
        }

        $country = $this->getCountryByCode($billingAddress->getCountryId());

        $street1 = $billingAddress->getStreetLine(1); // main address street
        $street2 = [$billingAddress->getStreetLine(2)]; // address can have up to 2 complement street lines natively in Magento
        if ($complement = $billingAddress->getStreetLine(3)) {
            $street2[] = $complement;
        }
        $street2 = implode(', ', $street2);

        $customerBillingAddress
            ->setFirstname($billingAddress->getFirstname())
            ->setLastname($billingAddress->getLastname())
            ->setCity($billingAddress->getCity())
            ->setCountry($country->getName())
            ->setCountryIsoCode($country->getData('iso3_code'))
            ->setStreet1($street1)
            ->setStreet2($street2)
            ->setZipCode($billingAddress->getPostcode())
            ->setPhone($billingAddress->getTelephone());

        if ($company = $billingAddress->getCompany()) {
            $customerBillingAddress->setCompany($company);
        }

        if ($region = $billingAddress->getRegion()) {
            $customerBillingAddress->setState($region);
        }

        return $customerBillingAddress;
    }

    /**
     * @param   Order   $order
     * @return  CustomerShippingAddress
     */
    protected function createCustomerShippingAddress(Order $order)
    {
        $customerShippingAddress = new CustomerShippingAddress();

        if (!$shippingAddress = $order->getShippingAddress()) {
            return $customerShippingAddress;
        }

        $country = $this->getCountryByCode($shippingAddress->getCountryId());

        $street1 = $shippingAddress->getStreetLine(1);
        $street2 = [$shippingAddress->getStreetLine(2)];
        if ($complement = $shippingAddress->getStreetLine(3)) {
            $street2[] = $complement;
        }
        $street2 = implode(', ', $street2);

        $customerShippingAddress
            ->setFirstname($shippingAddress->getFirstname())
            ->setLastname($shippingAddress->getLastname())
            ->setCity($shippingAddress->getCity())
            ->setCountry($country->getName())
            ->setCountryIsoCode($country->getData('iso3_code'))
            ->setStreet1($street1)
            ->setStreet2($street2)
            ->setZipCode($shippingAddress->getPostcode())
            ->setPhone($shippingAddress->getTelephone());

        if ($company = $shippingAddress->getCompany()) {
            $customerShippingAddress->setCompany($company);
        }

        if ($region = $shippingAddress->getRegion()) {
            $customerShippingAddress->setState($region);
        }

        return $customerShippingAddress;
    }

    /**
     * Create offers associated to specified order
     *
     * @param   Order   $order
     * @return  CreateOrderOfferCollection
     */
    protected function createOffers(Order $order)
    {
        $offerList = new CreateOrderOfferCollection();

        foreach ($order->getAllItems() as $item) {
            /** @var Order\Item $item */
            if (!$item->getMiraklOfferId()) {
                continue;
            }

            $offer = $this->createOffer($item);
            $offerList->add($offer);
        }

        return $offerList;
    }

    /**
     * @param   Order\Item  $orderItem
     * @return  CreateOrderOffer
     */
    protected function createOffer(Order\Item $orderItem)
    {
        if (!$orderItem->getMiraklOfferId()) {
            throw new \InvalidArgumentException(__('Trying to create an offer for API OR01 with a non-marketplace order item.'));
        }

        $order = $orderItem->getOrder();
        $orderTaxMode = $this->getOrderTaxMode($order);

        $offer = new CreateOrderOffer();
        $offer->setOfferId((int) $orderItem->getMiraklOfferId())
            ->setQuantity((int) $orderItem->getQtyOrdered())
            ->setShippingTypeCode($orderItem->getMiraklShippingType())
            ->setCurrencyIsoCode($order->getBaseCurrencyCode());

        if ($this->config->isSendOrderLineId($orderItem->getStore())) {
            $offer->setOrderLineId((int) $orderItem->getId());
        }

        if ($orderTaxMode === OrderTaxMode::TAX_INCLUDED) {
            // Offer and shipping prices including tax
            $offer->setPrice((float) $orderItem->getBaseRowTotalInclTax())
                ->setOfferPrice((float) $orderItem->getBasePriceInclTax())
                ->setShippingPrice((float) $orderItem->getMiraklBaseShippingInclTax());
        } else {
            // Offer and shipping prices excluding tax
            $offer->setPrice((float) $orderItem->getBaseRowTotal())
                ->setOfferPrice((float) $orderItem->getBasePrice())
                ->setShippingPrice((float) $orderItem->getMiraklBaseShippingExclTax());
        }

        $taxes = $this->getOrderItemCustomTaxDetails($orderItem, 'taxes');
        $shippingTaxes = $this->getOrderItemCustomTaxDetails($orderItem, 'shipping_taxes');

        if (!$taxes->count() && !$shippingTaxes->count()) {
            $taxes = $this->getOrderItemTaxDetails($orderItem);
            $shippingTaxes = $this->getOrderItemShippingTaxDetails($orderItem, (bool) $order->getMiraklIsShippingInclTax());
        }

        $offer->setTaxes($taxes);
        $offer->setShippingTaxes($shippingTaxes);

        return $offer;
    }

    /**
     * @param   Order   $order
     * @return  OrderCustomer
     */
    protected function createOrderCustomer(Order $order)
    {
        $orderCustomer = new OrderCustomer();

        if (!$order->getCustomerIsGuest() && !$order->getCustomerId()) {
            throw new LocalizedException(__('Order customer ID is missing'));
        }

        $orderCustomer
            ->setEmail($order->getCustomerEmail())
            ->setFirstname($order->getCustomerFirstname() ?: __('Guest'))
            ->setLastname($order->getCustomerLastname() ?: __('Guest'))
            ->setLocale($this->config->getLocale($order->getStore()));

        try {
            $customer = $this->customerRepository->getById((int) $order->getCustomerId());
        } catch(NoSuchEntityException $e) {
            $customer = null;
        }

        $customerId = $this->getOrderCustomerId->execute($order, $customer);
        $orderCustomer->setCustomerId($customerId);

        return $orderCustomer;
    }

    /**
     * @param   string  $code
     * @return  Country
     */
    protected function getCountryByCode($code)
    {
        return $this->countryFactory->create()->loadByCode($code);
    }

    /**
     * Returns order items taxes information
     *
     * @param   Order   $order
     * @return  array
     */
    protected function getOrderItemsTaxes(Order $order)
    {
        if (!isset($this->orderItemsTaxes[$order->getId()])) {
            $this->orderItemsTaxes[$order->getId()] = $this->orderItemTaxFactory->create()
                ->getTaxItemsByOrderId($order->getId());
        }

        return $this->orderItemsTaxes[$order->getId()];
    }

    /**
     * @param   Order\Item  $orderItem
     * @param   string      $taxType
     * @return  OrderTaxAmountCollection
     */
    protected function getOrderItemCustomTaxDetails(Order\Item $orderItem, $taxType)
    {
        $taxes = new OrderTaxAmountCollection();

        if (!$orderItem->getMiraklCustomTaxApplied()) {
            return $taxes;
        }

        $customTaxApplied = unserialize($orderItem->getMiraklCustomTaxApplied());

        if (!is_array($customTaxApplied) || empty($customTaxApplied[$taxType])) {
            return $taxes;
        }

        $taxesByCode = [];
        foreach ($customTaxApplied[$taxType] as $tax) {
            $code = $tax['type'];
            if (!isset($taxesByCode[$code])) {
                $taxesByCode[$code] = 0;
            }
            $taxesByCode[$code] += $tax['base_amount'];
        }

        foreach ($taxesByCode as $code => $amount) {
            $tax = new OrderTaxAmount($amount, $code);
            $taxes->add($tax);
        }

        return $taxes;
    }

    /**
     * @param   Order\Item  $orderItem
     * @return  OrderTaxAmountCollection
     */
    protected function getOrderItemTaxDetails(Order\Item $orderItem)
    {
        $taxes = new OrderTaxAmountCollection();

        foreach ($this->getOrderItemsTaxes($orderItem->getOrder()) as $orderItemTax) {
            if ($orderItemTax['item_id'] != $orderItem->getId() || $orderItemTax['taxable_item_type'] != 'product') {
                continue;
            }
            $tax = new OrderTaxAmount($orderItemTax['real_base_amount'], $orderItemTax['code']);
            $tax->setRate($orderItemTax['tax_percent']);
            $taxes->add($tax);
        }

        return $taxes;
    }

    /**
     * @param   Order\Item  $orderItem
     * @param   bool        $priceInclTax
     * @return  OrderTaxAmountCollection
     */
    protected function getOrderItemShippingTaxDetails(Order\Item $orderItem, $priceInclTax = false)
    {
        $shippingTaxes = new OrderTaxAmountCollection();

        if (!$orderItem->getMiraklShippingTaxApplied()) {
            return $shippingTaxes;
        }

        $shippingTaxApplied = unserialize($orderItem->getMiraklShippingTaxApplied());

        if (!is_array($shippingTaxApplied) || !$orderItem->getMiraklBaseShippingTaxAmount()) {
            return $shippingTaxes;
        }

        if ($priceInclTax) {
            // Shipping price including tax
            $shippingPriceInclTax = $orderItem->getMiraklBaseShippingInclTax();
            foreach (array_reverse($shippingTaxApplied) as $shippingTaxInfo) {
                foreach ($shippingTaxInfo['rates'] as $rateInfo) {
                    $shippingTaxAmount = $this->round(
                        $shippingPriceInclTax - ($shippingPriceInclTax / (1 + $rateInfo['percent'] / 100))
                    );
                    $shippingTax = new OrderTaxAmount($shippingTaxAmount, $rateInfo['code']);
                    $shippingTax->setRate(sprintf('%.4F', $rateInfo['percent']));
                    $shippingTaxes->add($shippingTax);
                }
                $shippingPriceInclTax -= $shippingTaxInfo['amount'];
            }
        } else {
            // Shipping price excluding tax
            $shippingPriceExclTax = $orderItem->getMiraklBaseShippingExclTax();
            foreach ($shippingTaxApplied as $shippingTaxInfo) {
                foreach ($shippingTaxInfo['rates'] as $rateInfo) {
                    $shippingTaxAmount = $this->round($shippingPriceExclTax * $rateInfo['percent'] / 100);
                    $shippingTax = new OrderTaxAmount($shippingTaxAmount, $rateInfo['code']);
                    $shippingTax->setRate(sprintf('%.4F', $rateInfo['percent']));
                    $shippingTaxes->add($shippingTax);
                }
                $shippingPriceExclTax += $orderItem->getMiraklBaseShippingExclTax() * $shippingTaxInfo['percent'] / 100;
            }
        }

        return $shippingTaxes;
    }

    /**
     * @param   Order   $order
     * @return  string
     */
    protected function getOrderTaxMode(Order $order)
    {
        if ($order->getMiraklIsOfferInclTax()) {
            return OrderTaxMode::TAX_INCLUDED;
        }

        return OrderTaxMode::TAX_EXCLUDED;
    }

    /**
     * @param   float   $price
     * @param   int     $precision
     * @return  float
     */
    protected function round($price, $precision = 2)
    {
        return round($price, (int) $precision);
    }
}
