<?php
/**
 * Copyright (c) 2016 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Action;

use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;
use Rampage\Nexus\Entities\Api\ArrayExportableInterface;
use Rampage\Nexus\Exception\UnexpectedValueException;
use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\Repository\PrototypeProviderInterface;
use Rampage\Nexus\Repository\RepositoryInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Stratigility\MiddlewareInterface;

use ArrayObject;
use Rampage\Nexus\Exception\Http\BadRequestException;
use Zend\InputFilter\InputFilterInterface;
use function GuzzleHttp\json_encode;


/**
 * Implements a repository based rest API
 */
abstract class AbstractRestApi implements MiddlewareInterface
{
    use JsonCollectionTrait;

    /**
     * @var RepositoryInterface|PrototypeProviderInterface
     */
    protected $repository;

    /**
     * @var ArrayExchangeInterface
     */
    protected $prototype;

    /**
     * @var ResponseInterface
     */
    protected $response = null;

    /**
     * @var ServerRequestInterface
     */
    protected $request = null;

    /**
     * @var string
     */
    protected $identifierAttribute = 'id';

    /**
     * Constructor
     *
     * @param RepositoryInterface $repository
     * @param object $prototype
     */
    public function __construct(RepositoryInterface $repository, ArrayExchangeInterface $prototype = null)
    {
        $this->repository = $repository;
        $this->prototype = $prototype;

        if (!$prototype && !($repository instanceof PrototypeProviderInterface)) {
            throw new InvalidArgumentException(sprintf('The repository must implement %s or $prototype must not be null', PrototypeProviderInterface::class));
        }
    }

    /**
     * Sets the attribute name that contains the identifier
     *
     * @param   string  $name   The Attribute name
     * @return  self
     */
    public function setIdentifierAttribute($name)
    {
        $this->identifierAttribute = (string)$name;
        return $this;
    }

    /**
     * Creates a new item
     *
     * @param array $data
     * @return \Rampage\Nexus\Entities\Api\ArrayExchangeInterface
     */
    public function create(array $data)
    {
        if (!method_exists($this->repository, 'save')) {
            return $this->response->withStatus(BadRequestException::NOT_ALLOWED);
        }

        $filter = $this->getCreateInputFilter();

        if (!$filter->setData($data)->isValid()) {
            return new JsonResponse([
                'invalid' => true,
                'messages' => $filter->getMessages()
            ], BadRequestException::UNPROCESSABLE);
        }

        if ($this->repository instanceof PrototypeProviderInterface) {
            $object = $this->repository->getPrototypeByData($data);
        } else {
            $object = clone $this->prototype;
        }

        $object->exchangeArray($data);
        $this->repository->save($object);

        return $object;
    }

    /**
     * Updates an existing item
     *
     * @param mixed $id
     * @param array $data
     * @throws UnexpectedValueException
     * @return \Rampage\Nexus\Entities\Api\ArrayExchangeInterface|object|NULL
     */
    public function update($id, array $data)
    {
        if (!method_exists($this->repository, 'save')) {
            return $this->response->withStatus(BadRequestException::NOT_ALLOWED);
        }

        $object = $this->get($id);

        if (!$object) {
            return null;
        }

        $filter = $this->getUpdateInputFilter();

        if (!$filter->setData($data)->isValid()) {
            return new JsonResponse([
                'invalid' => true,
                'messages' => $filter->getMessages()
            ], BadRequestException::UNPROCESSABLE);
        }

        $object->exchangeArray($filter->getValues());
        $this->repository->save($object);

        return $object;
    }

    /**
     * Returns an object with the given id
     *
     * @param int $id
     * @throws UnexpectedValueException
     * @return \Rampage\Nexus\Entities\Api\ArrayExchangeInterface|object|NULL
     */
    public function get($id)
    {
        $object = $this->repository->findOne($id);

        if ($object && !($object instanceof ArrayExchangeInterface)) {
            throw new UnexpectedValueException('The resulting entity does not implement ArrayExchangeInterface');
        }

        return $object;
    }

    /**
     * @param ServerRequestInterface $request
     * @return object[]
     */
    public function getList(ServerRequestInterface $request)
    {
        return $this->repository->findAll();
    }

    /**
     * Delete an object from persistence layer
     *
     * @param   mixed       $id The identifier of the object to remove
     * @return  object|null     The object that was removed or null, if the object is not present
     */
    public function delete($id)
    {
        if (!method_exists($this->repository, 'remove')) {
            throw new BadRequestException(BadRequestException::NOT_ALLOWED);
        }

        $object = $this->get($id);

        if (!$object) {
            return null;
        }

        $this->repository->remove($object);
        return $object;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteList()
    {
        throw new BadRequestException(BadRequestException::NOT_ALLOWED);
    }

    /**
     * Ensures there is a usable persed request body
     *
     * @param ServerRequestInterface $request
     * @throws RuntimeException
     * @return array
     */
    protected function ensureParsedBody(ServerRequestInterface $request)
    {
        $body = $request->getParsedBody();

        if (!is_array($body)) {
            throw new UnexpectedValueException('Bad request body');
        }

        return $body;
    }

    /**
     * @return InputFilterInterface
     */
    private function getCreateInputFilter()
    {
        return new RestApi\NoopInputFilter();
    }

    /**
     * @return InputFilterInterface
     */
    private function getUpdateInputFilter()
    {
        return new RestApi\NoopInputFilter();
    }


    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $this->request = $request;
        $this->response = $response;

        $method = strtolower($request->getMethod());
        $result = null;

        switch ($method) {
            case 'get':
                $id = $request->getAttribute($this->identifierAttribute);

                if (!$id) {
                    $result = $this->collectionToArray($this->getList($request));
                } else {
                    $result = $this->get($id);
                }
                break;

            case 'put':
                $id = $request->getAttribute($this->identifierAttribute);
                $data = $this->ensureParsedBody($request);
                $result = $this->update($id, $data);
                break;

            case 'post':
                $data = $this->ensureParsedBody($request);
                $result = $this->create($data);
                break;

            case 'delete':
                $id = $request->getAttribute($this->identifierAttribute);

                if ($id) {
                    $result = $this->delete($id);
                } else {
                    $result = $this->deleteList();
                }

                break;
        }

        if ($result === null) {
            if ($out) {
                return $out($request, $response);
            }

            return new TextResponse('Not found', 404, $response->getHeaders());
        }

        if ($result instanceof ResponseInterface) {
            return $result;
        } else if ($result instanceof ArrayExportableInterface) {
            $result = $result->toArray();
        } else if ($result instanceof ArraySerializableInterface) {
            $result = $result->getArrayCopy();
        }

        if (is_array($result) || ($result instanceof ArrayObject)) {
            return new JsonResponse($result, 200, $response->getHeaders());
        }

        throw new UnexpectedValueException('Unexpected REST Repository result: ' . (is_object($result)? get_class($result) : gettype($result)));
    }
}
