<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\nexus\controllers\cluster;

use rampage\nexus\exceptions;
use rampage\nexus\entities\Node;

use rampage\nexus\DocumentManagerAwareInterface;
use rampage\nexus\traits\DocumentManagerAwareTrait;

use Zend\Mvc\Controller\AbstractRestfulController;


/**
 * @method \Zend\Http\Request getRequest()
 */
class NodeController extends AbstractRestfulController implements DocumentManagerAwareInterface
{
    use DocumentManagerAwareTrait;

    /**
     * @var Node
     */
    protected $node;

    /**
     * Try authentication
     */
    public function authenticate()
    {
        $idHeader = $this->getRequest()->getHeader('X-Rampage-Node-Id');
        $sigHeader = $this->getRequest()->getHeader('X-Rampage-Node-Signature');

        if (!$idHeader || !($id = $idHeader->getFieldValue())) {
            throw new exceptions\InvalidArgumentException('Missing node identification header');
        }

        $node = $this->documentManager->find(Node::class, $id);
        if (!$node) {
            throw new exceptions\InvalidArgumentException('Invalid node id');
        }
    }
}
