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
     * @var Rsa
     */
    private $rsa;

    /**
     * @param PrivateKey $privateKey
     * @param PublicKey $publicKey
     */
    public function __construct(PrivateKey $privateKey, PublicKey $publicKey = null)
    {
        $options = new RsaOptions();
        $options->setPrivateKey($privateKey);
        $options->setBinaryOutput(false);

        if ($publicKey) {
            $options->setPublicKey($publicKey);
        }

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
     * {@inheritDoc}
     * @see \Rampage\Nexus\Api\SignRequestInterface::sign()
     */
    public function sign(RequestInterface $request, $data = null)
    {
        if (!$request->hasHeader('Date')) {
            $request = $request->withHeader('Date', (new \DateTime())->format(\DateTime::ISO8601));
        }

        $info = [
            $request->getMethod(),
            $request->getUri()->getPath(),
            $request->getUri()->getHost(),
            $request->getHeaderLine('User-Agent'),
            $request->getHeaderLine('Date'),
        ];

        if ($data) {

        }

        $signature = $this->rsa->sign(implode('|', $info));
        return $request->withHeader('X-Request-Signature', $signature);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Api\RequestSignatureInterface::verify()
     */
    public function verify(RequestInterface $request, array $data = null)
    {
        // TODO Auto-generated method stub

    }


}
