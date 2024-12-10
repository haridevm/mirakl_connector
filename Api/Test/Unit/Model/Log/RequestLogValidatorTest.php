<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Log;

use Mirakl\Api\Model\Log\RequestLogValidator;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @coversDefaultClass \Mirakl\Api\Model\Log\RequestLogValidator
 */
class RequestLogValidatorTest extends TestCase
{
    /**
     * @var RequestLogValidator
     */
    protected $requestLogValidator;

    /**
     * @var \Mirakl\Api\Helper\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \Mirakl\Core\Request\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(\Mirakl\Api\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(\Mirakl\Core\Request\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestLogValidator = new RequestLogValidator($this->configMock);
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithLoggingDisabled()
    {
        $this->configMock->expects($this->once())
            ->method('isApiLogEnabled')
            ->willReturn(false);

        $this->assertFalse($this->requestLogValidator->validate($this->requestMock));
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithEmptyFilter()
    {
        $this->configMock->expects($this->once())
            ->method('isApiLogEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getApiLogFilter')
            ->willReturn('');

        $this->assertTrue($this->requestLogValidator->validate($this->requestMock));
    }

    /**
     * @param string $filter
     * @param string $requestUri
     * @param array  $requestQueryParams
     * @param bool   $expected
     * @dataProvider getTestValidateWithFilterDataProvider
     * @covers ::validate
     */
    public function testValidateWithFilter(
        string $filter,
        string $requestUri,
        array $requestQueryParams,
        bool $expected
    ) {
        $this->configMock->expects($this->once())
            ->method('isApiLogEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getApiLogFilter')
            ->willReturn($filter);

        $this->requestMock->expects($this->once())
            ->method('getQueryParams')
            ->willReturn($requestQueryParams);

        $this->requestMock->expects($this->once())
            ->method('getUri')
            ->willReturn($requestUri);

        $this->assertSame($expected, $this->requestLogValidator->validate($this->requestMock));
    }

    /**
     * @return array
     */
    public function getTestValidateWithFilterDataProvider(): array
    {
        return [
            ['api/orders', 'locales', [], false],
            ['api/shipping/rates|api/locales', 'locales', [], true],
            ['api/shipping/rates|api/locales', 'shipping/rates', [], true],
            ['api/shipping/rates\?shipping_zone_code=INT|api/locales', 'shipping/rates', [], false],
            [
                'api/shipping/rates\?shipping_zone_code=INT|api/locales', 'shipping/rates',
                ['shipping_zone_code' => 'INT'],
                true,
            ],
        ];
    }
}
