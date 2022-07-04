<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Message;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Class OrderItemSubjectChange
 * @package Ekyna\Bundle\SubscriptionBundle\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemSubjectChange extends OrderItemQuantityChange
{
    private ?string $fromSubjectProvider;
    private ?int    $fromSubjectIdentifier;
    private ?string $toSubjectProvider;
    private ?int    $toSubjectIdentifier;

    public function setFromSubject(?string $provider, ?int $identifier): void
    {
        $this->fromSubjectProvider = $provider;
        $this->fromSubjectIdentifier = $identifier;
    }

    public function setToSubject(?string $provider, ?int $identifier): void
    {
        $this->toSubjectProvider = $provider;
        $this->toSubjectIdentifier = $identifier;
    }

    public function getFromSubject(): SubjectIdentity
    {
        return new SubjectIdentity($this->fromSubjectProvider, $this->fromSubjectIdentifier);
    }

    public function getToSubject(): SubjectIdentity
    {
        return new SubjectIdentity($this->toSubjectProvider, $this->toSubjectIdentifier);
    }
}
