<?php

declare(strict_types=1);

namespace Tourze\HotelProfileBundle\Tests\Controller\Admin\API;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\HotelProfileBundle\Controller\Admin\API\RoomTypesController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;

/**
 * @internal
 */
#[CoversClass(RoomTypesController::class)]
#[RunTestsInSeparateProcesses]
final class RoomTypesControllerTest extends AbstractWebTestCase
{
    public function testUnauthorizedAccessThrowsAccessDeniedException(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/api/room-types');
    }

    public function testAuthorizedAccessWithoutHotelIdReturnsEmptyArray(): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('GET', '/admin/api/room-types');
        self::getClient($client);
        $this->assertResponseStatusCodeSame(200);
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
        $this->assertEquals('[]', $content);
    }

    public function testAuthorizedAccessWithHotelIdReturnsRoomTypes(): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $client->request('GET', '/admin/api/room-types', ['hotelId' => 1]);
        self::getClient($client);
        $this->assertResponseStatusCodeSame(200);
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']), 'main');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/api/room-types');
    }
}
