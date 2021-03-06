<?php

namespace AlibabaCloud\Client\Credentials\Requests;

use AlibabaCloud\Client\Credentials\RamRoleArnCredential;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Request\RpcRequest;

/**
 * Retrieving assume role credentials.
 *
 * @package   AlibabaCloud\Client\Credentials\Requests
 */
class AssumeRole extends RpcRequest
{

    /**
     * AssumeRole constructor.
     *
     * @param RamRoleArnCredential $arnCredential
     *
     * @throws ClientException
     */
    public function __construct(RamRoleArnCredential $arnCredential)
    {
        parent::__construct();
        $this->options['query']['RoleArn']         = $arnCredential->getRoleArn();
        $this->options['query']['RoleSessionName'] = $arnCredential->getRoleSessionName();
        $this->options['query']['DurationSeconds'] = ALIBABA_CLOUD_STS_EXPIRE;
        $this->product('Sts');
        $this->version('2015-04-01');
        $this->action('AssumeRole');
        $this->host('sts.aliyuncs.com');
        $this->scheme('https');
        $this->regionId('cn-hangzhou');
    }
}
