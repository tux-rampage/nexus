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

namespace rampage\nexus\tests\node\config;

use rampage\nexus\node\config\TemplateConfigProcessor;

use VirtualFileSystem\FileSystem;
use PHPUnit_Framework_TestCase as TestCase;
use rampage\nexus\exceptions\ExceptionInterface;


class TeplateConfigProcessorTest extends TestCase
{
    /**
     * @var FileSystem
     */
    protected $vfs;

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->vfs = new FileSystem();
    }

    /**
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->vfs = null;
    }

    /**
     * The processor should write the file passed in constructor
     */
    public function testProcessTemplateWritesFile()
    {
        $processor = new TemplateConfigProcessor('template content', $this->vfs->path('/config.conf'));

        $processor->process([]);
        self::assertFileExists($this->vfs->path('/config.conf'));
    }

    /**
     * Processor should fail if the file is not creatable
     */
    public function testUncreatableFileThrowsException()
    {
        $this->setExpectedException(ExceptionInterface::class);

        $processor = new TemplateConfigProcessor('template content', $this->vfs->path('/no/such/dir/config.conf'));
        $processor->process([]);
    }

    /**
     * @return array
     */
    public function replacementTestDataProvider()
    {
        return [
            [
                'foo = ${foo}, bar = ${bar}',
                [
                    'foo' => 'bar',
                    'bar' => 'baz'
                ],
                'foo = bar, bar = baz'
            ]
        ];
    }

    /**
     * @dataProvider replacementTestDataProvider
     * @param unknown $template
     * @param array $vars
     * @param unknown $expectedResult
     */
    public function testReplacementWorks($template, array $vars, $expectedResult)
    {
        $processor = new TemplateConfigProcessor($template, $this->vfs->path('/config.conf'));
        $processor->process($vars);

        $this->assertEquals($expectedResult, file_get_contents($this->vfs->path('config.conf')));
    }
}