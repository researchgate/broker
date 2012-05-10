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

    public function setLockData(array $packages, $devPackages, array $aliases) {

    }
}