<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Elasticsearch\Endpoints\Get;
use Magento\AdobeIms\Model\GetAccessToken;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Get user acces toke test
 */
class GetAccessTokenTest extends TestCase
{

    /**
     * @var UserContextInterface|MockObject $userContext
     */
    private $userContext;

    /**
     * @var UserProfileRepositoryInterface|MockObject $userProfile
     */
    private $userProfile;

    private $getAccessToken;

    /**
     * Prepare test objects.
     */
    public function setUp(): void
    {
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->userProfile = $this->createMock(UserProfileRepositoryInterface::class);

        $this->getAccessToken = new GetAccessToken(
            $this->userContext,
            $this->userProfile
        );
    }

    /**
     * Test save.
     *
     * @param string|null $token
     * @dataProvider expectedDataProvider
     */
    public function testExecute(?string $token): void
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $userProfileMock = $this->createMock(\Magento\AdobeImsApi\Api\Data\UserProfileInterface::class);
        $this->userProfile->expects($this->exactly(1))
            ->method('getByUserId')
            ->willReturn($userProfileMock);
        $userProfileMock->expects($this->once())->method('getAccessToken')->willReturn($token);

        $this->assertEquals($token, $this->getAccessToken->execute());
    }

    /**
     * Test execute with exception
     */
    public function testExecuteWIthException()
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $this->userProfile->expects($this->exactly(1))
            ->method('getByUserId')
            ->willThrowException(new NoSuchEntityException());

        $this->getAccessToken->execute();
    }

    /**
     * Data provider for get acces token method.
     */
    public function expectedDataProvider()
    {
        return
            [
                [
                    'token' => 'kladjflakdjf3423rfzddsf'
                ],
                [
                    'null_token' => null
                ]
            ];
    }
}
