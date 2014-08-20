<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\cluster;

use rampage\nexus\entities\ApplicationInstance;


class ClusterNode extends LocalNode
{
    protected $master = null;

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\LocalNode::__construct()
     */
    public function __construct(ClusterManager $master)
    {
        $this->master = $master;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\LocalNode::changeApplicationState()
     */
    protected function changeApplicationState(ApplicationInstance $application, $newState)
    {
        parent::changeApplicationState($application, $newState);
        $this->master->updateApplicationState($application);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\LocalNode::stage()
     */
    public function stage(ApplicationInstance $application)
    {
        $this->master->syncApplication($application);
        $this->master->downloadArchive($application);

        parent::stage($application);
    }
}
