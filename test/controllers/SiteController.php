<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2019-01-04
 * Version      :   1.0
 */

namespace Controllers;


use Render\Abstracts\Controller;

class SiteController extends Controller
{
    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function actionIndex()
    {
        $this->render('index');
    }
}