<?php
namespace rg\broker\customizations;

class PearDownloader extends \Composer\Downloader\FileDownloader {

    /**
     * Extract file to directory
     *
     * @param string $file Extracted file
     * @param string $path Directory
     * @throws \UnexpectedValueException If can not extract downloaded file to path
     */
    protected function extract($file, $path) {
        $archive = new \PharData($file);
        $archive->extractTo($path, null, true);
        @unlink($path . '/package.sig');
        @unlink($path . '/package.xml');
    }

}