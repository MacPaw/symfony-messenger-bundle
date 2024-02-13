<?php

declare(strict_types=1);

namespace Macpaw\SymfonyMessengerBundle\Tests\Middleware;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Macpaw\SymfonyMessengerBundle\Middleware\DoctrineTransactionMiddleware;
use Macpaw\SymfonyMessengerBundle\Stamp\SyncCommandStamp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

class DoctrineTransactionMiddlewareTest extends TestCase
{
    private Connection|MockObject $connection;
    private ManagerRegistry|MockObject $managerRegistry;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $entityManager->method('getConnection')->willReturn($this->connection);

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->method('getManager')->willReturn($entityManager);
    }

    #[DataProvider('transactionInitiationDataProvider')]
    public function testTransactionInitiation(
        bool $isTransactionActive,
        array $stamps,
        bool $shouldBeginTransaction,
    ): void {
        $this->connection->method('isTransactionActive')->willReturn($isTransactionActive);

        if ($shouldBeginTransaction) {
            $this->connection->expects($this->once())->method('beginTransaction');
        } else {
            $this->connection->expects($this->never())->method('beginTransaction');
        }

        $envelope = new Envelope(new \stdClass(), $stamps);
        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $stack->expects($this->once())->method('next')->willReturn($nextMiddleware);
        $nextMiddleware->expects($this->once())->method('handle')->willReturnArgument(0);

        $middleware = new DoctrineTransactionMiddleware($this->managerRegistry);

        $middleware->handle($envelope, $stack);
    }

    public static function transactionInitiationDataProvider(): iterable
    {
        yield 'No active transaction, SyncCommandStamp present, should begin transaction' => [
            false, [new SyncCommandStamp()], true
        ];

        yield 'No active transaction, ConsumedByWorkerStamp present, should begin transaction' => [
            false, [new ConsumedByWorkerStamp()], true
        ];

        yield 'No active transaction, both stamps present, should begin transaction' => [
            false, [new SyncCommandStamp(), new ConsumedByWorkerStamp()], true
        ];

        yield 'No active transaction, no stamps, should not begin transaction' => [
            false, [], false
        ];

        yield 'Active transaction, SyncCommandStamp present, should not begin transaction' => [
            true, [new SyncCommandStamp()], false
        ];

        yield 'Active transaction, ConsumedByWorkerStamp present, should not begin transaction' => [
            true, [new ConsumedByWorkerStamp()], false
        ];

        yield 'Active transaction, both stamps present, should not begin transaction' => [
            true, [new SyncCommandStamp(), new ConsumedByWorkerStamp()], false
        ];

        yield 'Active transaction, no stamps, should not begin transaction' => [
            true, [], false
        ];
    }
}
