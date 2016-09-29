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

namespace Rampage\Nexus\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Rampage\Nexus\OAuth2\Repository\UserRepositoryInterface;
use Rampage\Nexus\OAuth2\Entities\User;
use Zend\Crypt\Password\PasswordInterface;

class CreateUserCommand extends Command
{
    /**
     * Command name
     */
    const COMMAND = 'master:createuser';

    /**
     * @var string
     */
    private $randomizePool = '!$?';

    /**
     * @var UserRepositoryInterface
     */
    private $repository;

    /**
     * @var PasswordInterface
     */
    private $passwordStrategy;

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::__construct()
     */
    public function __construct(UserRepositoryInterface $repository, PasswordInterface $passwordStrategy)
    {
        parent::__construct(self::COMMAND);

        $this->repository = $repository;
        $this->passwordStrategy = $passwordStrategy;

        for ($i = 65; $i < 91; $i++) {
            $this->randomizePool .= chr($i);
        }

        for ($i = 97; $i < 123; $i++) {
            $this->randomizePool .= chr($i);
        }
    }

    /**
     * @param int $length
     * @return string
     */
    private function randomString($length)
    {
        $result = '';
        $max = strlen($this->randomizePool) - 1;

        while (strlen($result) < $length) {
            $offset = rand(0, $max);
            $result .= substr($this->randomizePool, $offset, 1);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->addArgument('username', InputArgument::REQUIRED, 'The username to add/modify')
            ->setDescription('Creates or resets a user with a random password')
            ->setHelp("If the user already exists, its password will be reset to a random one\nThe new password will be printed on screen.");
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $user = $this->repository->findOne($username);
        $password = $this->randomString(8);

        if (!$user) {
            $user = new User($username, $this->passwordStrategy);
        }

        $user->setPassword($password);
        $this->repository->save($user);

        $output->writeln('<info>New Password for "' . $user->getName() . '": </info>' . $password);
        return 0;
    }
}
