<?php

namespace ModbusTcpClient\Composer\Read\Coil;


use ModbusTcpClient\Composer\Request;
use ModbusTcpClient\Exception\ModbusException;
use ModbusTcpClient\Packet\ErrorResponse;
use ModbusTcpClient\Packet\ModbusFunction\ReadCoilsRequest;
use ModbusTcpClient\Packet\ResponseFactory;

class ReadCoilRequest implements Request
{
    /**
     * @var string uri to modbus server. Example: 'tcp://192.168.100.1:502'
     */
    private $uri;

    /** @var ReadCoilAddress[] */
    private $addresses;

    /** @var ReadCoilsRequest */
    private $request;


    public function __construct(string $uri, array $addresses, $request)
    {
        $this->addresses = $addresses;
        $this->request = $request;
        $this->uri = $uri;
    }

    /**
     * @return ReadCoilsRequest
     */
    public function getRequest(): ReadCoilsRequest
    {
        return $this->request;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return ReadCoilAddress[]
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }

    public function __toString()
    {
        return $this->request->__toString();
    }

    /**
     * @param string $binaryData
     * @return array|ErrorResponse
     * @throws ModbusException
     * @throws \Exception
     */
    public function parse(string $binaryData)
    {
        $response = ResponseFactory::parseResponse($binaryData)->withStartAddress($this->request->getStartAddress());
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        $result = [];
        foreach ($this->addresses as $address) {
            $result[$address->getName()] = $address->extract($response);
        }
        return $result;
    }
}
