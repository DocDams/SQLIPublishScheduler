<?php

namespace SQLI\PublishSchedulerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SQLIPublishSchedulerBundle:Default:index.html.twig');
    }
}
