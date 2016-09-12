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

namespace Rampage\Nexus;

use DateTime;

/**
 * Provides the application version
 */
final class Version
{
    private static $version = null;

    const VALID_TAG_REGEX = '~^[a-z0-9.-]+$~';
    const SEMVER_REGEX = '~^v?\d+(\.\d+)*(-(dev|alpha|beta|patch|rc)\d*)?$~i';

    /**
     * @param string $name
     * @return bool
     */
    private function validateTagOrBranchName($name)
    {
        return (bool)preg_match(self::VALID_TAG_REGEX, $name);
    }

    /**
     * @param string $tag
     * @return bool
     */
    private function isSemVer($tag)
    {
        return (bool)preg_match(self::SEMVER_REGEX, $tag);
    }

    /**
     * @return string
     */
    private function loadFromInfoFile()
    {
        if (is_readable(__DIR__ . '/../resources/version.info')) {
            return trim(file_get_contents(__DIR__ . '/../resources/version.info'));
        }

        return null;
    }

    /**
     * Extract the version from tags pointing to the HEAD
     *
     * This will prefer semantic versioned tags and fall back to named
     * tags.
     *
     * @param array $tags
     * @return string
     */
    private function extractFromTags($tags)
    {
        $version = '';

        foreach ($tags as $tag) {
            $tag = trim($tag);

            if (!$this->validateTagOrBranchName($tag)) {
                continue;
            }

            if ($this->isSemVer($tag)) {
                return strtolower($tag);
            }

            if ($version == '') {
                $version = strtolower($tag);
            }
        }

        return $version;
    }

    /**
     * Read the date from GIT
     *
     * @return string
     */
    private function readGitDate()
    {
        $output = [];
        $status = 0;

        exec('git log -1 --format=%ci', $output, $status);

        try {
            $dateStr = (($status == 0) && (count($output) > 0))? trim(array_shift($output)) : '';
            $date = ($dateStr == '')? new DateTime() : DateTime::createFromFormat(DateTime::ISO8601, $dateStr);
        } catch (\Exception $e) {
            $date = new \DateTime();
        }

        return $date? $date->format('Ymd') : (new DateTime())->format('Ymd');
    }

    /**
     * {@inheritDoc}
     * @see Task::main()
     */
    protected function discover()
    {
        $version = $this->loadFromInfoFile();

        if ($version) {
            return $version;
        }

        $output = [];
        $status = 0;
        $cwd = getcwd();

        chdir(__DIR__ . '/../');
        exec('git tag -l --points-at HEAD', $output, $status);

        $version = ($status == 0)? $this->extractFromTags($output) : null;

        if ($version == '') {
            exec('git rev-parse --abbrev-ref HEAD', $output, $status);
            $version = ($status == 0)? strtolower(trim(array_shift($output))) : null;
            $gitDate = $this->readGitDate();

            if (!$this->isSemVer($version)) {
                if ($this->validateTagOrBranchName((string)$version)) {
                    $version =  'dev-' . $version . '@' . $gitDate;
                } else {
                    $version = 'UNKNOWN@' . $gitDate;
                }
            }
        }

        chdir($cwd);
        return $version;
    }

    /**
     * Returns the current version
     *
     * @return string
     */
    public static function getVersion()
    {
        if (!self::$version) {
            self::$version = (new self())->discover();
        }

        return self::$version;
    }
}
