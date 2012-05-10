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

class Repository extends \Composer\Repository\ComposerRepository {

    private $name;

    public function __construct($name) {
        $this->name = $name;
        $this->url = ROOT . '/repositories/' .$name;
        $this->cache = new \rg\broker\customizations\Cache();
        $this->io = new \Composer\IO\NullIO();
    }

    public function getName() {
        return $this->name;
    }

}