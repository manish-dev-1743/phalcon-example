<?php
declare(strict_types=1);


use Phalcon\Validation;


class RegisterController extends ControllerBase
{

    public function indexAction()
    {
        $home = HOME::findFirstById(1);
        $cmp = $this->cmpdetails();
        $this->view->cmp = $cmp;
        $this->view->home = $home;
    }

}