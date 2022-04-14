<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\User;

use App\Entity\User;
use App\Exception\User\UserNotFoundException;
use App\Service\User\ResetPasswordService;
use Symfony\Component\Uid\Uuid;

class ResetPasswordServiceTest extends UserServiceTestBase
{
    private ResetPasswordService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new ResetPasswordService($this->userRepository, $this->encoderService);
    }

    public function testResetPassword(): void
    {
        $resetPasswordTokenId = 'abcde';
        $password = 'new-password';
        $user = new User('user', 'user@api.com');

        $this->userRepository
            ->expects($this->exactly(1))
            ->method('findOneInactiveByIdAndResetPasswordTokenOrFail')
            ->with($user->getId(), $resetPasswordTokenId)
            ->willReturn($user);

        $user = $this->service->reset($user->getId(), $resetPasswordTokenId, $password);

        $this->assertInstanceOf(User::class, $user);
        $this->assertNull($user->getResetPasswordToken());
    }

    public function testResetPasswordForNonExistingUser(): void
    {
        $resetPasswordTokenId = 'abcde';
        $password = 'new-password';
        $user = new User('user', 'user@api.com');

        $this->userRepository
            ->expects($this->exactly(1))
            ->method('findOneInactiveByIdAndResetPasswordTokenOrFail')
            ->with($user->getId(), $resetPasswordTokenId)
            ->willThrowException(new UserNotFoundException());

        $this->expectException(UserNotFoundException::class);

        $this->service->reset($user->getId(), $resetPasswordTokenId, $password);

    }
}