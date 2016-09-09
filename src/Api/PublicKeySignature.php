<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   LUKA Proprietary
 * @copyright Copyright (c) 2016 LUKA netconsult GmbH (www.luka.de)
 */

namespace Rampage\Nexus\Api;

use Psr\Http\Message\RequestInterface;
use Zend\Crypt\PublicKey\Rsa;
use Zend\Crypt\PublicKey\RsaOptions;
use Zend\Crypt\PublicKey\Rsa\PublicKey;
use Zend\Crypt\PublicKey\Rsa\PrivateKey;


class PublicKeySignature implements RequestSignatureInterface, SignRequestInterface
{
    /**
     * @var string
     */
    const SIGNATURE_HEADER = 'X-Request-Signature';

    /**
     * @var Rsa
     */
    private $rsa;

    /**
     * @param PrivateKey $privateKey
     * @param PublicKey $publicKey
     */
    public function __construct(PrivateKey $privateKey)
    {
        $options = new RsaOptions();
        $options->setPrivateKey($privateKey);
        $options->setBinaryOutput(false);

        $this->rsa = new Rsa($options);
    }

    /**
     * @param PublicKey $publicKey
     */
    public function setPublicKey(PublicKey $publicKey)
    {
        $this->rsa->getOptions()->setPublicKey($publicKey);
        return $this;
    }

    /**
     * @param RequestInterface $request
     * @param string $data
     * @return string
     */
    private function buildRequestData(RequestInterface $request, $data)
    {
        $info = [
            $request->getMethod(),
            $request->getUri()->getPath(),
            $request->getUri()->getHost(),
            $request->getHeaderLine('User-Agent'),
            $request->getHeaderLine('Date'),
        ];

        if (is_string($data) && ($data != '')) {
            $info[] = $data;
        }

        return implode('|', $info);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Api\SignRequestInterface::sign()
     */
    public function sign(RequestInterface $request, $data = null)
    {
        if (!$request->hasHeader('Date')) {
            $request = $request->withHeader('Date', (new \DateTime())->format(\DateTime::ISO8601));
        }

        $info = $this->buildRequestData($request, $data);
        $signature = $this->rsa->sign($info);
        return $request->withHeader(self::SIGNATURE_HEADER, $signature);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Api\RequestSignatureInterface::verify()
     */
    public function verify(RequestInterface $request, $data = null)
    {
        if (!$request->hasHeader(self::SIGNATURE_HEADER)) {
            return false;
        }

        $info = $this->buildRequestData($request, $data);
        $signature = $request->getHeaderLine(self::SIGNATURE_HEADER);
        $valid = ($signature != '') && $this->rsa->verify($info, $signature, null, Rsa::MODE_BASE64);

        return $valid;
    }
}
