<?php
declare(strict_types=1);

use Phalcon\Mvc\Controller;


class ControllerBase extends Controller
{
    // Implement common logic
    protected function cmpdetails()
    {
        # code...
        $cmp = Company::findFirstById(3);
        return $cmp;
    }
    
    
}
