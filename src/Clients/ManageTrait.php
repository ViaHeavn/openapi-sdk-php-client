<?php

namespace AlibabaCloud\Client\Clients;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Credentials\AccessKeyCredential;
use AlibabaCloud\Client\Credentials\CredentialsInterface;
use AlibabaCloud\Client\Credentials\EcsRamRoleCredential;
use AlibabaCloud\Client\Credentials\Providers\CredentialsProvider;
use AlibabaCloud\Client\Credentials\Providers\EcsRamRoleProvider;
use AlibabaCloud\Client\Credentials\Providers\RamRoleArnProvider;
use AlibabaCloud\Client\Credentials\Providers\RsaKeyPairProvider;
use AlibabaCloud\Client\Credentials\RamRoleArnCredential;
use AlibabaCloud\Client\Credentials\RsaKeyPairCredential;
use AlibabaCloud\Client\Credentials\StsCredential;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Client\Filter\Filter;

/**
 * Trait ManageTrait.
 *
 * @mixin     Client
 */
trait ManageTrait
{
    /**
     * @param int $timeout
     *
     * @return AccessKeyCredential|CredentialsInterface|StsCredential
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function getSessionCredential($timeout = \ALIBABA_CLOUD_TIMEOUT)
    {
        switch (\get_class($this->credential)) {
            case EcsRamRoleCredential::class:
                return (new EcsRamRoleProvider($this))->get();
            case RamRoleArnCredential::class:
                return (new RamRoleArnProvider($this))->get($timeout);
            case RsaKeyPairCredential::class:
                return (new RsaKeyPairProvider($this))->get($timeout);
            default:
                return $this->credential;
        }
    }

    /**
     * Naming clients.
     *
     * @param string $name
     *
     * @return static
     * @throws ClientException
     */
    public function name($name)
    {
        Filter::name($name);

        return AlibabaCloud::set($name, $this);
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
     * Set the current client as the global client.
     *
     * @return static
     * @throws ClientException
     */
    public function asGlobalClient()
    {
        return $this->asDefaultClient();
    }

    /**
     * Set the current client as the default client.
     *
     * @return static
     * @throws ClientException
     */
    public function asDefaultClient()
    {
        return $this->name(CredentialsProvider::getDefaultName());
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        if (isset($this->options['debug'])) {
            return true === $this->options['debug'] && PHP_SAPI === 'cli';
        }

        return false;
    }
}
