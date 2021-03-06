<?php

namespace AlibabaCloud\Client\Credentials\Providers;

use AlibabaCloud\Client\Credentials\EcsRamRoleCredential;
use AlibabaCloud\Client\Credentials\StsCredential;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Client\Result\Result;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Stringy\Stringy;

/**
 * Class EcsRamRoleProvider
 *
 * @package   AlibabaCloud\Client\Credentials\Providers
 */
class EcsRamRoleProvider extends Provider
{
    /**
     * @var string
     */
    private $uri = 'http://100.100.100.200/latest/meta-data/ram/security-credentials/';

    /**
     * @var array
     */
    public static $config = [];

    /**
     * For TSC cache
     *
     * @var int
     */
    protected $expiration = 10;

    /**
     * Get credential.
     *
     * @return StsCredential
     * @throws ClientException
     * @throws ServerException
     */
    public function get()
    {
        $result = $this->getCredentialsInCache();

        if ($result === null) {
            $result = $this->request();

            if (!isset($result['AccessKeyId'], $result['AccessKeySecret'], $result['SecurityToken'])) {
                throw new ServerException($result, $this->error, \ALIBABA_CLOUD_INVALID_CREDENTIAL);
            }

            $this->cache($result->toArray());
        }

        return new StsCredential(
            $result['AccessKeyId'],
            $result['AccessKeySecret'],
            $result['SecurityToken']
        );
    }

    /**
     * Get credentials by request.
     *
     * @return Result
     * @throws ClientException
     * @throws ServerException
     */
    public function request()
    {
        $result = new Result($this->getResponse());

        if ($result->getResponse()->getStatusCode() === 404) {
            $message = 'The role was not found in the instance';
            throw new ClientException($message, \ALIBABA_CLOUD_INVALID_CREDENTIAL);
        }

        if (!$result->isSuccess()) {
            $message = 'Error retrieving credentials from result';
            throw new ServerException($result, $message, \ALIBABA_CLOUD_INVALID_CREDENTIAL);
        }

        return $result;
    }

    /**
     * Get data from meta.
     *
     * @return mixed|ResponseInterface
     * @throws ClientException
     */
    public function getResponse()
    {
        /**
         * @var EcsRamRoleCredential $credential
         */
        $credential = $this->client->getCredential();
        $url        = $this->uri . $credential->getRoleName();

        $options = [
            'http_errors'     => false,
            'timeout'         => 1,
            'connect_timeout' => 1,
            'debug'           => $this->client->isDebug(),
        ];

        try {
            return (new Client(self::$config))->request('GET', $url, $options);
        } catch (GuzzleException $e) {
            if (Stringy::create($e->getMessage())->contains('timed')) {
                $message = 'Timeout or instance does not belong to Alibaba Cloud';
            } else {
                $message = $e->getMessage();
            }

            throw new ClientException(
                $message,
                \ALIBABA_CLOUD_SERVER_UNREACHABLE,
                $e
            );
        }
    }
}
