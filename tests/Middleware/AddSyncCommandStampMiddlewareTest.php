<?php

declare(strict_types=1);

namespace Macpaw\SymfonyMessengerBundle\Tests\Middleware;

use Macpaw\SymfonyMessengerBundle\Middleware\AddSyncCommandStampMiddleware;
use Macpaw\SymfonyMessengerBundle\Stamp\SyncCommandStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;

class AddSyncCommandStampMiddlewareTest extends TestCase
{
    public function testSyncTransportAddsSyncCommandStamp()
    {
        $envelope = new Envelope(new \stdClass());
        $sender = $this->createMock(SyncTransport::class);
        $sendersLocator = $this->createMock(SendersLocatorInterface::class);

        $sendersLocator->method('getSenders')->willReturn([$sender]);

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $stack->expects($this->once())->method('next')->willReturn($nextMiddleware);
        $nextMiddleware->expects($this->once())->method('handle')->willReturnCallback(
            function (Envelope $envelope) {
                $this->assertNotNull($envelope->last(SyncCommandStamp::class));
                return $envelope;
            }
        );

        $middleware = new AddSyncCommandStampMiddleware($sendersLocator);

        $middleware->handle($envelope, $stack);
    }

    public function testNonSyncTransportDoesNotAddSyncCommandStamp()
    {
        $envelope = new Envelope(new \stdClass());
        $sendersLocator = $this->createMock(SendersLocatorInterface::class);

        $sendersLocator->method('getSenders')->willReturn([]);

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $stack->expects($this->once())->method('next')->willReturn($nextMiddleware);
        $nextMiddleware->expects($this->once())->method('handle')->willReturnCallback(
            function (Envelope $envelope) {
                $this->assertNull($envelope->last(SyncCommandStamp::class));
                return $envelope;
            }
        );

        $middleware = new AddSyncCommandStampMiddleware($sendersLocator);

        $middleware->handle($envelope, $stack);
    }
}
