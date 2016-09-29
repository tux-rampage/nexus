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

use Rampage\Nexus\Config\PropertyConfigInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Zend\Crypt\PublicKey\Rsa\PrivateKey;
use Zend\Crypt\PublicKey\Rsa;
use Rampage\Nexus\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;


class GenerateKeyCommand extends Command
{
    /**
     * Comand name
     */
    const COMMAND = 'master:keys:generate';

    const OPTION_FORCE = 'force';

    /**
     * @var PropertyConfigInterface
     */
    private $config;

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::__construct()
     */
    public function __construct(PropertyConfigInterface $config)
    {
        $this->config = $config;
        parent::__construct(self::COMMAND);

    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setDescription('Creates the private RSA key')
            ->setHelp("Creates the private RSA key, if it does not exist.\nAn existing key can be overwritten by using the --force option.")
            ->addOption(self::OPTION_FORCE, null, InputOption::VALUE_NONE, 'Overwrite any exting key.');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$output->getFormatter()->hasStyle('warn')) {
            $output->getFormatter()->setStyle('warn', new OutputFormatterStyle('yellow'));
        }

        $key = $this->config->get('rsa.keys.private');
        $passphrase = null;

        if (is_array($key)) {
            $key = isset($key['key'])? $key['key'] : null;
            $passphrase = isset($key['passphrase'])? $key['passphrase'] : null;
        }

        if (!$key) {
            $output->writeln('<error>The private key is not configured! Please set rsa.keys.private in your config first!</>');
            return 1;
        }

        if (strpos($key, 'file://') !== 0) {
            $output->writeln('<error>Cannot update an embedded key certificate.</>');
            $output->writeln('Your runtime config does not define a key file (starting with file://).');
            return 3;
        }

        $key = substr($key, 7);
        if (!$key) {
            throw new RuntimeException('Empty key file path!');
            return 1;
        }

        $output->writeln('<info>Private Key:</> ' . $key);

        if (file_exists($key) && !$input->getOption(self::OPTION_FORCE)) {
            $output->writeln('<warn>The keyfile file already exists. Use --force to overwrite.</>');
            return 2;
        }

        $rsa = new Rsa();

        if ($passphrase !== null) {
            $rsa->getOptions()->setPassPhrase($passphrase);
        }

        $rsa->generateKeys(['private_key_bits' => 4096]);

        if (!file_put_contents($key, $rsa->getOptions()->getPrivateKey()->toString())) {
            throw new RuntimeException('Failed to write private RSA key: ' . $key);
        }

        $output->writeln('<info>Private key generated successfully</>');
    }
}
