<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   LUKA Proprietary
 * @copyright Copyright (c) 2016 LUKA netconsult GmbH (www.luka.de)
 */

namespace Rampage\Nexus\Package;

use Rampage\Nexus\Exception\RuntimeException;

use SplFileInfo;
use PharData;

/**
 * Interface for loading archive files
 */
interface ArchiveLoaderInterface
{
    /**
     * Ensures the archive is available locally
     *
     * @param   string      $archive    The path or URI to the archive
     * @return  SplFileInfo             The file info instance for the local archive file
     * @throws  RuntimeException        When the file cannot be provided locally
     */
    public function ensureLocalArchiveFile($archive);

    /**
     * Returns the package from the given archive
     *
     * @param   PharData            $archive    The archive instance
     * @return  PackageInterface                The resulting package
     * @throws  RuntimeException                When the packagetype is not available
     */
    public function getPackage(PharData $archive);
}
