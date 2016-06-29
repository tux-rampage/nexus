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

namespace Rampage\Nexus\Deployment;


interface StageSubscriberInterface
{
    /**
     * Callead before the application is activated
     *
     * Exceptions thrown in this stage will not proceed enabling the application.
     * Depending on the deploy strategy, this will leave the previous version in place or stay in a maintenance mode
     *
     * @param array|ArrayAccess $params
     */
    public function beforeActivate($params);

    /**
     * Called after the application was activated
     *
     * Exceptions thrown in this state will cause the application state to become failed, but it will not
     * disable the application (which means leaving it a possibly broken state)
     *
     * @param  array|ArrayAccess $params  User paramters
     */
    public function afterActivate($params);

    /**
     * Callead before the application is deactivated
     *
     * Exceptions thrown in this stage will not proceed enabling the application.
     * Depending on the deploy strategy, this will leave the previous version in place or stay in a maintenance mode
     *
     * @param array|ArrayAccess $params
     */
    public function beforeDeactivate($params);

    /**
     * Called after the application was deactivated
     *
     * Exceptions thrown in this state will cause the application state to become failed, but it will not
     * disable the application (which means leaving it a possibly broken state)
     *
     * @param  array|ArrayAccess $params  User paramters
     */
    public function afterDeactivate($params);


    /**
     * Called before rollback
     *
     * Called on the **current** instance that is being rolled back
     *
     * @param  array|ArrayAccess  $params   User params
     */
    public function beforeRollback($params, $isRollbackTarget);

    /**
     * Called after rollback
     *
     * This is called on the **previous** instance that was rolled back to.
     *
     * @param  array|ArrayAccess  $params   User paramters
     */
    public function afterRollback($params, $isRollbackTarget);
}
