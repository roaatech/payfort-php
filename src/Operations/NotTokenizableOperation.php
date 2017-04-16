<?php
/**
 * Created by PhpStorm.
 * User: mhh1422
 * Date: 03/01/2017
 * Time: 9:41 AM
 */

namespace ItvisionSy\Payment\PayFort\Operations;

abstract class NotTokenizableOperation extends Operation
{

    public function handle(array $data)
    {
        $this->process($data);
    }

}