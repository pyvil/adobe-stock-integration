<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Controller\Adminhtml\User;

use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Magento\AdobeIms\Controller\Adminhtml\User\Profile;
use Magento\Framework\Controller\Result\Json;
use PHPUnit\Framework\TestCase;

/**
 * Ensure that User Profile data can be returned.
 */
class ProfileTest extends TestCase
{

    /**
     * @var MockObject|UserProfileRepositoryInterface $userProfileRepository
     */
    private $userProfileRepository;

    /**
     * @var MockObject|UserContextInterface $userContext
     */
    private $userContext;

    /**
     * @var MockObject|Action\Context $action
     */
    private $action;

    /**
     * @var MockObject|ResultFactory $resultFactory
     */
    private $resultFactory;

    /**
     * @var MockObject|LoggerInterface $logger
     */
    private $logger;

    /**
     * @var Profile $profile
     */
    private $profile;

    /**
     * @var MockObject
     */
    private $jsonObject;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->action = $this->createMock(\Magento\Backend\App\Action\Context::class);

        $this->userContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->userProfileRepository = $this->createMock(UserProfileRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->jsonObject = $this->createMock(Json::class);
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->action->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($this->jsonObject);
        $this->profile = new Profile(
            $this->action,
            $this->userContext,
            $this->userProfileRepository,
            $this->logger
        );

    }

    /**
     * Ensure that User Profile data can be returned.
     *
     * @dataProvider userDataProvider
     * @param array $result
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function testExecute(array $result): void
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $userProfileMock = $this->createMock(\Magento\AdobeImsApi\Api\Data\UserProfileInterface::class);
        $userProfileMock->expects($this->once())->method('getEmail')->willReturn('exaple@adobe.com');
        $userProfileMock->expects($this->once())->method('getName')->willReturn('Smith');
        $userProfileMock->expects($this->once())->method('getImage')->willReturn('https://adobe.com/sample-image.png');

        $this->userProfileRepository->expects($this->exactly(1))
            ->method('getByUserId')
            ->willReturn($userProfileMock);

        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(200);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($result));
        $this->assertEquals($this->jsonObject, $this->profile->execute());
    }

    /**
     * Execute with exception
     */
    public function testExecuteWithExecption()
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(null);
        $this->userProfileRepository->expects($this->exactly(1))
            ->method('getByUserId')
            ->willThrowException(new \Exception());
        $result = [
            'success' => false,
            'message' => __('An error occurred during get user data. Contact support.'),
        ];
        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($result));
        $this->profile->execute();

    }

    /**
     * User data provider
     */
    public function userDataProvider()
    {
        return
            [
                [
                    [
                        'success' => true,
                        'error_message' => '',
                        'result' => [
                            'email' => 'exaple@adobe.com',
                            'name' => 'Smith',
                            'image' => 'https://adobe.com/sample-image.png'
                        ]
                    ]
                ]
            ];

    }
}
