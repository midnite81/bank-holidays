<?php

namespace Midnite81\BankHolidays\Services;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\RequestFactory;
use Midnite81\BankHolidays\Contracts\Services\IClient;
use Midnite81\BankHolidays\Exceptions\MissingConfigKeyException;
use Midnite81\BankHolidays\Exceptions\RequestFailedException;
use Psr\Http\Client\ClientExceptionInterface;

class Client implements IClient
{
    /**
     * @var HttpClient|null
     */
    protected $httpClient;

    /**
     * @var MessageFactory|RequestFactory|null
     */
    protected $requestFactory;

    /**
     * @var array
     */
    protected $config;

    /**
     * Client constructor.
     *
     * @param HttpClient|null     $httpClient
     * @param RequestFactory|null $requestFactory
     * @param array               $config
     *
     * @throws MissingConfigKeyException
     */
    public function __construct(
        HttpClient $httpClient = null,
        RequestFactory $requestFactory = null,
        array $config = []
    ) {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->processConfig($config);
    }

    /**
     * Gets the bank holiday data
     *
     * @throws RequestFailedException
     */
    public function getData()
    {
        $request = $this->requestFactory->createRequest('GET', $this->config['bank-holiday-url']);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new RequestFailedException($e);
        } catch (\Exception $e) {
            throw new RequestFailedException($e);
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param array $config
     *
     * @throws MissingConfigKeyException
     */
    private function processConfig(array $config)
    {
        if (!array_key_exists('bank-holiday-url', $config)) {
            throw new MissingConfigKeyException('bank-holiday-url');
        }

        $this->config = $config;
    }
}
