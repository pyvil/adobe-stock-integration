<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeStockImageAdminUi\Test\Unit\Controller\Adminhtml\License;

use Magento\AdobeStockClientApi\Api\Data\UserQuotaInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\AdobeStockClientApi\Api\ClientInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\AdobeStockImageAdminUi\Controller\Adminhtml\License\Quota;
use Magento\AdobeStockClientApi\Api\Data\UserQuotaInterface;

/**
 * Get quota test.
 */
class QuotaTest extends TestCase
{
    /**
     * @var MockObject|ClientInterface $clientInterfaceMock
     */
    private $clientInterfaceMock;

    /**
     * @var MockObject|LoggerInterface $logger
     */
    private $logger;

    /**
     * @var MockObject|ActionContext $context
     */
    private $context;

    /**
     * @var Quota $quota
     */
    private $quota;

    /**
     * @var MockObject $resultFactory
     */
    private $resultFactory;

    /**
     * @var MockObject $jsonObject
     */
    private $jsonObject;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->clientInterfaceMock = $this->createMock(ClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->jsonObject = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($this->jsonObject);

        $this->quota = new Quota(
            $this->context,
            $this->clientInterfaceMock,
            $this->logger
        );
    }

    /**
     * Verify that Quota can be retrieved
     */
    public function testExecute()
    {
        /** @var UserQuotaInterface|MockObject $quota */
        $quota = $this->createMock(UserQuotaInterface::class);
        $quota->expects($this->once())->method('getImages')->willReturn(2);
        $quota->expects($this->once())->method('getCredits')->willReturn(1);

        $data = [
            'success' => true,
            'result' => [
                'credits' => 1,
                'images' => 2
            ]
        ];
        $this->clientInterfaceMock->expects($this->once())
            ->method('getQuota')
            ->willReturn($quota);
        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(200);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($data));
        $this->quota->execute();
    }

    /**
     * Verify that exception will throw if quota not available.
     */
    public function testExecuteWithException()
    {
        $result = [
            'success' => false,
            'message' => new Phrase('An error occurred during get quota operation. Contact support.')
        ];
        $this->clientInterfaceMock->expects($this->once())
            ->method('getQuota')
            ->willThrowException(new \Exception());
        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($result));
        $this->quota->execute();
    }
}
