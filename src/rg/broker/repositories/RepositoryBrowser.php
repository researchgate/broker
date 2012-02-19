<?php
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