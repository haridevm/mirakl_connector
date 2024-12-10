<?php
declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Resolver\Offer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mirakl\Api\Helper\Offer as OfferHelper;
use Mirakl\GraphQl\Model\Resolver\AbstractResolver;
use Mirakl\MMP\Common\Domain\Message\MessageCustomer;
use Mirakl\MMP\Front\Domain\Offer\Message\CreateOfferMessage;

class SellerThreadResolver extends AbstractResolver implements ResolverInterface
{
    /**
     * @var OfferHelper
     */
    protected $offerHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadata;

    /**
     * @param OfferHelper                 $offerHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerMetadataInterface   $customerMetadata
     */
    public function __construct(
        OfferHelper $offerHelper,
        CustomerRepositoryInterface $customerRepository,
        CustomerMetadataInterface $customerMetadata
    )
    {
        $this->offerHelper = $offerHelper;
        $this->customerRepository = $customerRepository;
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $currentUserId = $context->getUserId();

        $this->checkLoggedCustomer($context);

        $offerId = $this->getInput($args, 'input.offer', true);
        $customer = $this->customerRepository->getById($currentUserId);

        $message = new CreateOfferMessage();
        $message->setSubject($this->getInput($args, 'input.subject', true));
        $message->setBody($this->getInput($args, 'input.body', true));
        if ($visible = $this->getInput($args, 'input.visible')) {
            $message->setVisible($visible);
        }

        $messageCustomer = new MessageCustomer();
        $messageCustomer->setCustomerId($currentUserId);
        $messageCustomer->setEmail($customer->getEmail());
        $messageCustomer->setFirstname($customer->getFirstname());
        $messageCustomer->setLastname($customer->getLastname());
        if ($locale =  $this->getInput($args, 'input.locale')) {
            $messageCustomer->setLocale($locale);
        }

        $genderId = $customer->getGender();
        if (is_numeric($genderId)) {
            $genderOptions = $this->customerMetadata->getAttributeMetadata('gender')->getOptions();
            $gender = $genderOptions[$genderId] ?? null;
            $genderLabel = $gender ? $gender->getLabel() : null;
            $messageCustomer->setCivility($genderLabel);
        }

        $message->setCustomer($messageCustomer);

        $messageCreated = null;
        try {
            $messageCreated = $this->offerHelper->createOfferMessage($offerId, $message);
        } catch (\Exception $e) {
            throw $this->mapSdkError($e);
        }

        $data = $messageCreated->toArray();
        $data['model'] = $messageCreated;

        return $data;
    }
}
