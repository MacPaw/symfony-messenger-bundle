<?php

declare(strict_types=1);

namespace Macpaw\SymfonyMessengerBundle\Middleware;

use Macpaw\SymfonyMessengerBundle\Stamp\SyncCommandStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;

class AddSyncCommandStampMiddleware implements MiddlewareInterface
{
    public function __construct(private SendersLocatorInterface $sendersLocator)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        foreach ($this->sendersLocator->getSenders($envelope) as $sender) {
            if ($sender instanceof SyncTransport) {
                $envelope = $envelope->with(new SyncCommandStamp());
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
