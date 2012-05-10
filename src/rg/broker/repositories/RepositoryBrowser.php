<?php
/*
 * This file is part of rg\broker.
 *
 * (c) ResearchGate GmbH <bastian.hofmann@researchgate.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace rg\broker\repositories;

class RepositoryBrowser {
    public function getRepositories() {
        $iterator = new \DirectoryIterator(ROOT . '/repositories');

        $repositories = array();

        foreach ($iterator as $file) {
            if ($file->isDir() &&
                $file->getFilename() !== '.' &&
                $file->getFilename() !== '..') {
                $repositories[] = $file->getFilename();
            }
        }

        return $repositories;
    }
}