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

        $details['order_id'] = uniqid().'-'.$payment->getId();

        /*$details['customer_id'] = $payment->getClientId();
        $details['customer_email'] = $payment->getClientEmail();*/

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
