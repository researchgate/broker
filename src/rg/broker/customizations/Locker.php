<?php
namespace rg\broker\customizations;

class Locker extends \Composer\Package\Locker {

    public function __construct() {
    }

    public function lockPackages($packages) {
    }

    public function isLocked() {
        return false;
    }

    public function isFresh() {
        return false;
    }

    public function getLockedPackages() {
        return array();
    }
}