<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use jonasarts\Bundle\RegistryBundle\Engine\DoctrineRegistryEngine;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyEntity;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKeyEntity;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use PHPUnit\Framework\TestCase;

class DoctrineRegistryEngineTest extends TestCase
{
    /**
     * Creates an engine wired with the given em/repos. Unprovided dependencies default to stubs.
     */
    private function createEngine(
        ?EntityManagerInterface $em = null,
        ?EntityRepository $registryRepo = null,
        ?EntityRepository $systemRepo = null,
    ): DoctrineRegistryEngine {
        $regRepo = $registryRepo ?? $this->createStub(EntityRepository::class);
        $sysRepo = $systemRepo ?? $this->createStub(EntityRepository::class);
        $entityManager = $em ?? $this->createStub(EntityManagerInterface::class);

        $entityManager->method('getRepository')
            ->willReturnCallback(static fn (string $class) => match ($class) {
                RegistryKeyEntity::class => $regRepo,
                SystemKeyEntity::class => $sysRepo,
                default => throw new InvalidArgumentException('Unknown: '.$class),
            });

        return new DoctrineRegistryEngine($entityManager);
    }

    // --- Registry Exists ---

    public function testRegistryExistsReturnsTrue(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(1)->setKey('k')->setName('n')->setType(RegistryKeyType::STRING)->setValue('v');

        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['user_id' => 1, 'key' => 'k', 'name' => 'n', 'type' => 's'])
            ->willReturn($entity);

        $engine = $this->createEngine(registryRepo: $repo);

        $this->assertTrue($engine->registryExists(1, 'k', 'n', RegistryKeyType::STRING));
    }

    public function testRegistryExistsReturnsFalse(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['user_id' => 1, 'key' => 'k', 'name' => 'n', 'type' => 'i'])
            ->willReturn(null);

        $engine = $this->createEngine(registryRepo: $repo);

        $this->assertFalse($engine->registryExists(1, 'k', 'n', RegistryKeyType::INTEGER));
    }

    // --- Registry Read ---

    public function testRegistryReadReturnsValue(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(1)->setKey('k')->setName('n')->setType(RegistryKeyType::INTEGER)->setValue('42');

        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['user_id' => 1, 'key' => 'k', 'name' => 'n', 'type' => 'i'])
            ->willReturn($entity);

        $engine = $this->createEngine(registryRepo: $repo);

        $this->assertSame('42', $engine->registryRead(1, 'k', 'n', RegistryKeyType::INTEGER));
    }

    public function testRegistryReadReturnsFalseWhenNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['user_id' => 1, 'key' => 'k', 'name' => 'n', 'type' => 's'])
            ->willReturn(null);

        $engine = $this->createEngine(registryRepo: $repo);

        $this->assertFalse($engine->registryRead(1, 'k', 'n', RegistryKeyType::STRING));
    }

    public function testRegistryReadIncludesTypeInQuery(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with($this->callback(static fn (array $criteria): bool => array_key_exists('type', $criteria) && 'i' === $criteria['type']))
            ->willReturn(null);

        $engine = $this->createEngine(registryRepo: $repo);

        $engine->registryRead(1, 'k', 'n', RegistryKeyType::INTEGER);
    }

    // --- Registry Write ---

    public function testRegistryWriteCreatesNewEntity(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(RegistryKeyEntity::class));
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, registryRepo: $repo);

        $this->assertTrue($engine->registryWrite(1, 'k', 'n', RegistryKeyType::STRING, 'hello'));
    }

    public function testRegistryWriteUpdatesExistingEntity(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(1)->setKey('k')->setName('n')->setType(RegistryKeyType::STRING)->setValue('old');

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($entity);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, registryRepo: $repo);

        $this->assertTrue($engine->registryWrite(1, 'k', 'n', RegistryKeyType::STRING, 'new'));
    }

    public function testRegistryWriteStringifiesIntValue(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $persisted = null;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(static function ($entity) use (&$persisted) {
                $persisted = $entity;
            });
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, registryRepo: $repo);
        $engine->registryWrite(1, 'k', 'n', RegistryKeyType::INTEGER, 42);

        $this->assertInstanceOf(RegistryKeyEntity::class, $persisted);
        $this->assertSame('42', $persisted->getValue());
    }

    public function testRegistryWriteJsonEncodesArrayValue(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $persisted = null;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(static function ($entity) use (&$persisted) {
                $persisted = $entity;
            });
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, registryRepo: $repo);
        $engine->registryWrite(1, 'k', 'n', RegistryKeyType::ARRAY, ['x' => 1]);

        $this->assertInstanceOf(RegistryKeyEntity::class, $persisted);
        $this->assertSame('{"x":1}', $persisted->getValue());
    }

    public function testRegistryWriteStringifiesBoolValue(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $persisted = null;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(static function ($entity) use (&$persisted) {
                $persisted = $entity;
            });
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, registryRepo: $repo);
        $engine->registryWrite(1, 'k', 'n', RegistryKeyType::BOOLEAN, true);

        $this->assertInstanceOf(RegistryKeyEntity::class, $persisted);
        $this->assertSame('1', $persisted->getValue());
    }

    public function testRegistryWriteStringifiesFloatValue(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $persisted = null;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(static function ($entity) use (&$persisted) {
                $persisted = $entity;
            });
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, registryRepo: $repo);
        $engine->registryWrite(1, 'k', 'n', RegistryKeyType::FLOAT, 3.14);

        $this->assertInstanceOf(RegistryKeyEntity::class, $persisted);
        $this->assertSame('3.14', $persisted->getValue());
    }

    public function testRegistryWriteStringifiesNullValue(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $persisted = null;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(static function ($entity) use (&$persisted) {
                $persisted = $entity;
            });
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, registryRepo: $repo);
        $engine->registryWrite(1, 'k', 'n', RegistryKeyType::STRING, null);

        $this->assertInstanceOf(RegistryKeyEntity::class, $persisted);
        $this->assertSame('', $persisted->getValue());
    }

    // --- Registry Delete ---

    public function testRegistryDeleteRemovesEntity(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(1)->setKey('k')->setName('n')->setType(RegistryKeyType::STRING)->setValue('v');

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($entity);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('remove')->with($entity);
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, registryRepo: $repo);

        $this->assertTrue($engine->registryDelete(1, 'k', 'n', RegistryKeyType::STRING));
    }

    public function testRegistryDeleteReturnsFalseWhenNotFound(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('remove');

        $engine = $this->createEngine(em: $em, registryRepo: $repo);

        $this->assertFalse($engine->registryDelete(1, 'k', 'n', RegistryKeyType::STRING));
    }

    // --- Registry All ---

    public function testRegistryAllReturnsEntities(): void
    {
        $entities = [new RegistryKeyEntity(), new RegistryKeyEntity()];

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findAll')->willReturn($entities);

        $engine = $this->createEngine(registryRepo: $repo);

        $this->assertCount(2, $engine->registryAll());
    }

    // --- System Exists ---

    public function testSystemExistsReturnsTrue(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('k')->setName('n')->setType(RegistryKeyType::STRING)->setValue('v');

        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['key' => 'k', 'name' => 'n', 'type' => 's'])
            ->willReturn($entity);

        $engine = $this->createEngine(systemRepo: $repo);

        $this->assertTrue($engine->systemExists('k', 'n', RegistryKeyType::STRING));
    }

    public function testSystemExistsReturnsFalse(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['key' => 'k', 'name' => 'n', 'type' => 'i'])
            ->willReturn(null);

        $engine = $this->createEngine(systemRepo: $repo);

        $this->assertFalse($engine->systemExists('k', 'n', RegistryKeyType::INTEGER));
    }

    // --- System Read ---

    public function testSystemReadReturnsValue(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('k')->setName('n')->setType(RegistryKeyType::INTEGER)->setValue('99');

        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['key' => 'k', 'name' => 'n', 'type' => 'i'])
            ->willReturn($entity);

        $engine = $this->createEngine(systemRepo: $repo);

        $this->assertSame('99', $engine->systemRead('k', 'n', RegistryKeyType::INTEGER));
    }

    public function testSystemReadReturnsFalseWhenNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['key' => 'k', 'name' => 'n', 'type' => 's'])
            ->willReturn(null);

        $engine = $this->createEngine(systemRepo: $repo);

        $this->assertFalse($engine->systemRead('k', 'n', RegistryKeyType::STRING));
    }

    public function testSystemReadIncludesTypeInQuery(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with($this->callback(static fn (array $criteria): bool => array_key_exists('type', $criteria) && 'f' === $criteria['type']))
            ->willReturn(null);

        $engine = $this->createEngine(systemRepo: $repo);

        $engine->systemRead('k', 'n', RegistryKeyType::FLOAT);
    }

    // --- System Write ---

    public function testSystemWriteCreatesNewEntity(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(SystemKeyEntity::class));
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, systemRepo: $repo);

        $this->assertTrue($engine->systemWrite('k', 'n', RegistryKeyType::STRING, 'hello'));
    }

    public function testSystemWriteUpdatesExistingEntity(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('k')->setName('n')->setType(RegistryKeyType::STRING)->setValue('old');

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($entity);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, systemRepo: $repo);

        $this->assertTrue($engine->systemWrite('k', 'n', RegistryKeyType::STRING, 'new'));
    }

    public function testSystemWriteJsonEncodesArrayValue(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $persisted = null;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(static function ($entity) use (&$persisted) {
                $persisted = $entity;
            });
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, systemRepo: $repo);
        $engine->systemWrite('k', 'n', RegistryKeyType::ARRAY, ['y' => 2]);

        $this->assertInstanceOf(SystemKeyEntity::class, $persisted);
        $this->assertSame('{"y":2}', $persisted->getValue());
    }

    // --- System Delete ---

    public function testSystemDeleteRemovesEntity(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('k')->setName('n')->setType(RegistryKeyType::STRING)->setValue('v');

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($entity);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('remove')->with($entity);
        $em->expects($this->once())->method('flush');

        $engine = $this->createEngine(em: $em, systemRepo: $repo);

        $this->assertTrue($engine->systemDelete('k', 'n', RegistryKeyType::STRING));
    }

    public function testSystemDeleteReturnsFalseWhenNotFound(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('remove');

        $engine = $this->createEngine(em: $em, systemRepo: $repo);

        $this->assertFalse($engine->systemDelete('k', 'n', RegistryKeyType::STRING));
    }

    // --- System All ---

    public function testSystemAllReturnsEntities(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findAll')->willReturn([new SystemKeyEntity()]);

        $engine = $this->createEngine(systemRepo: $repo);

        $this->assertCount(1, $engine->systemAll());
    }

    public function testSystemAllReturnsEmptyArray(): void
    {
        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findAll')->willReturn([]);

        $engine = $this->createEngine(systemRepo: $repo);

        $this->assertSame([], $engine->systemAll());
    }
}
