<?php
namespace rg\broker\repositories;

class Repository extends \Composer\Repository\ComposerRepository {

    private $name;

    public function __construct($name) {
        $this->name = $name;
        $this->url = ROOT . '/repositories/' .$name;
    }

    public function getName() {
        return $this->name;
    }

}