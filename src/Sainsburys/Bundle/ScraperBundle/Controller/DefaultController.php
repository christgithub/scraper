<?php

namespace Sainsburys\Bundle\ScraperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SainsburysScraperBundle:Default:index.html.twig', array('name' => $name));
    }
}
