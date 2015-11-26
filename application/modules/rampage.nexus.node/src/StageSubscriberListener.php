<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\node;

class StageSubscriberList implements StageSubscriberInterface
{
    /**
     * @var \SplObjectStorage|StageSubscriberInterface[]
     */
    protected $subscribers;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->subscribers = new \SplObjectStorage();
    }

    /**
     * @param StageSubscriberInterface $subscriber
     * @return self
     */
    public function add(StageSubscriberInterface $subscriber)
    {
        if (!$this->subscribers->contains($subscriber)) {
            $this->subscribers->attach($subscriber);
        }

        return $this;
    }

    /**
     * @param StageSubscriberInterface $subscriber
     * @return self
     */
    public function remove(StageSubscriberInterface $subscriber)
    {
        if ($this->subscribers->contains($subscriber)) {
            $this->subscribers->offsetUnset($subscriber);
        }

        return $this;
    }

    /**
     * @see \rampage\nexus\node\StageSubscriberInterface::afterActivate()
     */
    public function afterActivate($params)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->afterActivate($params);
        }
    }

    /**
     * @see \rampage\nexus\node\StageSubscriberInterface::afterDeactivate()
     */
    public function afterDeactivate($params)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->afterDeactivate($params);
        }
    }

    /**
     * @see \rampage\nexus\node\StageSubscriberInterface::afterRollback()
     */
    public function afterRollback($params, $isRollbackTarget)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->afterRollback($params, $isRollbackTarget);
        }
    }

    /**
     * @see \rampage\nexus\node\StageSubscriberInterface::beforeActivate()
     */
    public function beforeActivate($params)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->beforeActivate($params);
        }
    }

    /**
     * @see \rampage\nexus\node\StageSubscriberInterface::beforeDeactivate()
     */
    public function beforeDeactivate($params)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->beforeDeactivate($params);
        }
    }

    /**
     * @see \rampage\nexus\node\StageSubscriberInterface::beforeRollback()
     */
    public function beforeRollback($params, $isRollbackTarget)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->beforeRollback($params, $isRollbackTarget);
        }
    }
}
