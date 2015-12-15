<?php

namespace Ekyna\Bundle\SubscriptionBundle\Payum\Action\AtosSips;

use Ekyna\Bundle\PaymentBundle\Payum\Action\AbstractCapturePaymentAction;
use Ekyna\Bundle\SubscriptionBundle\Entity\Payment;
use Ekyna\Component\Sale\Payment\PaymentInterface;
use Payum\Core\Security\TokenInterface;

/**
 * Class CapturePaymentUsingAtosSipsAction
 * @package Ekyna\Bundle\SubscriptionBundle\Payum\Action\AtosSips
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CapturePaymentUsingAtosSipsAction extends AbstractCapturePaymentAction
{
    /**
     * {@inheritdoc}
     *
     * @param Payment $payment
     */
    protected function composeDetails(PaymentInterface $payment, TokenInterface $token)
    {
        $details = $payment->getDetails();

        if (array_key_exists('amount', $details)) {
            return;
        }

        // TODO Check
        $details['currency_code'] = '978';
        // TODO Check
        $details['amount'] = abs($payment->getAmount() * 100);

        /** @var \Ekyna\Bundle\SubscriptionBundle\Entity\Subscription $subscription */
        $subscription = $payment->getSubscriptions()->first();
        $user = $subscription->getUser();

        $customerName = $user->getLastName() . ' ' . $user->getFirstName();
        if (32 < strlen($customerName)) {
            $customerName = substr($customerName, 0, 32);
        }

        $details['customer_id'] = $user->getId();
        $details['order_id']    = $customerName;

//        $details['customer_email'] = $user->getEmail();
//        $details['customer_name'] = $user->getLastName();
//        $details['customer_firstname'] = $user->getFirstName();

        $payment->setDetails($details);
    }

    /**
     * {@inheritdoc}
     */
    protected function supportsPayment($payment)
    {
        return $payment instanceof Payment;
    }
}
