<?php

namespace Ekyna\Bundle\SubscriptionBundle\Order;

use Ekyna\Bundle\OrderBundle\Exception\InvalidItemException;
use Ekyna\Bundle\OrderBundle\Exception\InvalidSubjectException;
use Ekyna\Bundle\OrderBundle\Provider\AbstractItemProvider;
use Ekyna\Bundle\SubscriptionBundle\Entity\SubscriptionRepository;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Sale\Order\OrderItemInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class OrderItemProvider
 * @package Ekyna\Bundle\SubscriptionBundle\Order
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemProvider extends AbstractItemProvider
{
    const TYPE = 'subscription';

    /**
     * @var SubscriptionRepository
     */
    protected $repository;

    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Constructor.
     *
     * @param SubscriptionRepository $repository
     */
    public function __construct(SubscriptionRepository $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     * @param SubscriptionInterface $subject
     * @throws InvalidSubjectException
     */
    public function transform($subject)
    {
        if (!$this->supports($subject)) {
            throw new InvalidSubjectException('Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface');
        }

        $year = $subject->getPrice()->getPricing()->getYear();

        $item = $this->createNewOrderItem();
        $item
            ->setDesignation($this->translator->trans(
                'ekyna_subscription.order.designation',
                array('{{year}}' => $year)
            ))
            ->setReference('SUBS-' . $year)
            ->setPrice($subject->getPrice()->getAmount())
            ->setWeight(0)
            ->setSubjectType($this->getName())
            ->setSubjectData([
                'id' => $subject->getId()
            ])
            ->setSubject($subject)
        ;

        return $item;
    }

    /**
     * {@inheritdoc}
     * @return SubscriptionInterface
     * @throws InvalidItemException
     */
    public function reverseTransform(OrderItemInterface $item)
    {
        if (!$this->supports($item)) {
            throw new InvalidItemException('Unsupported order item.');
        }

        return $this->repository->findOneBy(['id' => $item->getSubjectData()['id']]);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidItemException
     */
    public function getFormOptions(OrderItemInterface $item, $property)
    {
        if (!$this->supports($item)) {
            throw new InvalidItemException('Unsupported order item.');
        }

        $options = [];
        if (in_array($property, ['quantity', 'price', 'reference', 'weight', 'tax'])) {
            $options['disabled'] = true;
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidSubjectException
     */
    public function generateFrontOfficePath($subjectOrOrderItem)
    {
        if (!$this->supports($subjectOrOrderItem)) {
            throw new InvalidSubjectException('Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface');
        }

        return $this->urlGenerator->generate('ekyna_subscription_account_index');
    }

    /**
     * {@inheritdoc}
     */
    public function generateBackOfficePath($subjectOrOrderItem)
    {
        if ($subjectOrOrderItem instanceof OrderItemInterface) {
            $subscription = $this->reverseTransform($subjectOrOrderItem);
        } elseif ($subjectOrOrderItem instanceof SubscriptionInterface) {
            $subscription = $subjectOrOrderItem;
        } else {
            throw new InvalidSubjectException('Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface');
        }

        return $this->urlGenerator->generate('ekyna_user_user_admin_show', [
            'userId' => $subscription->getUser()->getId(),
        ]);
    }

    /**
     * Returns whether the provider supports the given subject or order item.
     *
     * @param object $subjectOrOrderItem
     * @return boolean
     */
    public function supports($subjectOrOrderItem)
    {
        if ($subjectOrOrderItem instanceof SubscriptionInterface) {
            return true;
        }

        if ($subjectOrOrderItem instanceof OrderItemInterface) {
            return $subjectOrOrderItem->getSubjectType() === $this->getName()
                && array_key_exists('id', $subjectOrOrderItem->getSubjectData());
        }

        return false;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::TYPE;
    }
}
