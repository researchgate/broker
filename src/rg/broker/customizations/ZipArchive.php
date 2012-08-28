<?php
/*
 * This file is part of rg\broker.
 *
 * (c) ResearchGate GmbH <bastian.hofmann@researchgate.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace rg\broker\customizations;

/**
 * This is just a small extension to the PHP supplied ZipArchive class. It adds 
 * a recursive add function and the possibility to exclude directories.
 *
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class ZipArchive extends \ZipArchive { 

    /**
     * Array containing directory names to exclude
     * @var array
     */
    protected $excludeDirectories = array();

    /**
     * Contains the "base path" of the archive. This means that files will be 
     * added relative to this base path, instead of with absolute paths. 
     * @var string
     */
    public $archiveBaseDir = "";

    /**
     * Add directories recursively
     * 
     * @param String $path
     */
    public function addDir($path) 
    {
        $archiveDirName = str_replace($this->archiveBaseDir, "", $path); 
    
        if (!empty($archiveDirName)) {
            $this->addEmptyDir(ltrim($archiveDirName, "/")); 
        }

        $nodes = glob($path . '/*'); 
        foreach ($nodes as $node) {
            if (is_dir($node)) { 
                foreach ($this->excludeDirectories as $dir) {
                    if (strpos($node, $dir) !== false) {
                        continue 2;
                    }
                }
                $this->addDir($node); 
            } else if (is_file($node)) {
                $archiveFileName = str_replace($this->archiveBaseDir . "/", "", $node); 
                $this->addFile($node, $archiveFileName); 
            } 
        } 
    }

    /**
     * Add a directory name to the list of directories to exclude
     * 
     * @param [type] $dir [description]
     */
    public function addExcludeDirectory($dir) 
    {
        $this->excludeDirectories []= $dir;
    }

    /**
     * @return array
     */
    public function getExcludeDirectories()
    {
        return $this->excludeDirectories;
    }
    
    /**
     * @param  array $excludeDirectories
     * @return ZipArchive
     */
    public function setExcludeDirectories(array $newExcludeDirectories)
    {
        $this->excludeDirectories = $newExcludeDirectories;
        return $this;
    }

    /**
     * @return String
     */
    public function getArchiveBaseDir()
    {
        return $this->archiveBaseDir;
    }
    
    /**
     * @param  String $archiveBaseDir
     * @return ZipArchive
     */
    public function setArchiveBaseDir($newArchiveBaseDir)
    {
        $this->archiveBaseDir = $newArchiveBaseDir;
        return $this;
    }
}