<?php

namespace Ekyna\Bundle\SubscriptionBundle\Payum\Action\Paypal;

use Ekyna\Bundle\PaymentBundle\Payum\Action\AbstractCapturePaymentAction;
use Ekyna\Bundle\SubscriptionBundle\Entity\Payment;
use Ekyna\Component\Sale\Payment\PaymentInterface;
use Payum\Core\Security\TokenInterface;

/**
 * Class CapturePaymentUsingExpressCheckoutAction
 * @package Ekyna\Bundle\PaymentBundle\Payum\Action\Paypal
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CapturePaymentUsingExpressCheckoutAction extends AbstractCapturePaymentAction
{
    /**
     * {@inheritdoc}
     *
     * @param Payment $payment
     */
    protected function composeDetails(PaymentInterface $payment, TokenInterface $token)
    {
        $details = $payment->getDetails();

        if (array_key_exists('PAYMENTREQUEST_0_INVNUM', $details)) {
            return;
        }

        $details['NOSHIPPING'] = 1;
        $details['LANDINGPAGE'] = 'Billing';
        $details['SOLUTIONTYPE'] = 'Sole';

        $details['PAYMENTREQUEST_0_INVNUM'] = uniqid().'-'.$payment->getId();
        $details['PAYMENTREQUEST_0_CURRENCYCODE'] = $payment->getCurrency();
        $details['PAYMENTREQUEST_0_AMT'] = round($payment->getAmount(), 2);
        $details['PAYMENTREQUEST_0_ITEMAMT'] = round($payment->getAmount(), 2);

        $m = $itemTotal = 0;

        foreach ($payment->getSubscriptions() as $subscription) {
            $price = $subscription->getPrice();
            $amount = $price->getAmount();
            $details['L_PAYMENTREQUEST_0_NAME'.$m] = 'Cotisation '.$price->getPricing()->getYear();
            $details['L_PAYMENTREQUEST_0_AMT'.$m] = round($amount, 2);
            $details['L_PAYMENTREQUEST_0_QTY'.$m] = 1;
            $itemTotal += $amount;
            $m++;
        }

        $details['PAYMENTREQUEST_0_ITEMAMT'] = round($itemTotal, 2);

        $details['PAYMENTREQUEST_0_SHIPPINGAMT'] = 0;

        $details['PAYMENTREQUEST_0_TAXAMT'] = 0;

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
