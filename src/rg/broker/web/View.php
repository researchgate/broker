<?php
namespace rg\broker\web;

class View extends \Slim_View {

    public function render($template) {
        $response = parent::render('header.php');
        $response .= parent::render($template);
        $response .= parent::render('footer.php');
        return $response;
    }
}