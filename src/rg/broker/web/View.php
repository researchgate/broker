<?php
/*
 * This file is part of rg\broker.
 *
 * (c) ResearchGate GmbH <bastian.hofmann@researchgate.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace rg\broker\web;

class View extends \Slim_View {

    public function render($template) {
        $response = parent::render('header.php');
        $response .= parent::render($template);
        $response .= parent::render('footer.php');
        return $response;
    }
}