<?php

declare(strict_types=1);

namespace Macpaw\SymfonyMessengerBundle\Middleware;

use Doctrine\ORM\EntityManagerInterface;
use Macpaw\SymfonyMessengerBundle\Stamp\SyncCommandStamp;
use Symfony\Bridge\Doctrine\Messenger\DoctrineTransactionMiddleware as BaseDoctrineTransactionMiddleware;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

class DoctrineTransactionMiddleware extends BaseDoctrineTransactionMiddleware
{
    protected function handleForManager(
        EntityManagerInterface $entityManager,
        Envelope $envelope,
        StackInterface $stack
    ): Envelope {
        if (
            !$entityManager->getConnection()->isTransactionActive()
            && (
                null !== $envelope->last(SyncCommandStamp::class)
                || null !== $envelope->last(ConsumedByWorkerStamp::class)
            )
        ) {
            return parent::handleForManager($entityManager, $envelope, $stack);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
