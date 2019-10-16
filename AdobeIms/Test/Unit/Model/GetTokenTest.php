<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\GetToken;
use Magento\AdobeImsApi\Api\Data\ConfigInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterfaceFactory;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Get user token test
 */
class GetTokenTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigInterface|MockObject $config
     */
    private $configMock;

    /**
     * @var CurlFactory|MockObject $curlFactoryMock
     */
    private $curlFactoryMock;

    /**
     * @var Json|MockObject $jsonMock
     */
    private $jsonMock;

    /**
     * @var TokenResponseInterfaceFactory|MockObject $tokenResponseFactoryMock
     */
    private $tokenResponseFactoryMock;

    /**
     * @var GetToken $getToken
     */
    private $getToken;

    /**
     * Prepare test objects.
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->curlFactoryMock = $this->getMockBuilder(CurlFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->jsonMock = $this->createMock(Json::class);
        $this->tokenResponseFactoryMock = $this->createMock(TokenResponseInterfaceFactory::class);
        $this->getToken = new GetToken(
            $this->configMock,
            $this->curlFactoryMock,
            $this->jsonMock,
            $this->tokenResponseFactoryMock
        );
    }

    /**
     * Test save.
     */
    public function testExecute(): void
    {
        $curl = $this->createMock(\Magento\Framework\HTTP\Client\Curl::class);
        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(2))
            ->method('addHeader')
            ->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('getTokenUrl')
            ->willReturn('http://www.some.url.com');
        $this->configMock->expects($this->once())
            ->method('getApiKey')
            ->willReturn('string');
        $this->configMock->expects($this->once())
            ->method('getPrivateKey')
            ->willReturn('string');
        $curl->expects($this->once())
            ->method('post')
            ->willReturn(null);
        $this->jsonMock->expects($this->once())
            ->method('unserialize')
            ->willReturn(['string']);
        $tokenResponse = $this->getMockBuilder(
            \Magento\AdobeIms\Model\OAuth\TokenResponse::class
        )->disableOriginalConstructor()->setMethods(['addData', 'getAccessToken', 'getRefreshToken'])->getMock();
        $this->tokenResponseFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($tokenResponse);
        $tokenResponse->expects($this->once())
            ->method('addData')
            ->willReturn($tokenResponse);
        $tokenResponse->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('string');
        $tokenResponse->expects($this->once())
            ->method('getRefreshToken')
            ->willReturn('string');
        $this->assertEquals($tokenResponse, $this->getToken->execute('code'));
    }
}
