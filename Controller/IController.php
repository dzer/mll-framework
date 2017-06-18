<?php
/**
 * User: shenzhe
 * Date: 13-6-17
 *  controller 接口
 */

namespace Mll\Controller;

interface IController
{
    public function json($data = [], $code = 200, $header = [], $options = []);

    public function beforeAction();

    public function afterAction();

}