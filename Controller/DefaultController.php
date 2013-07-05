<?php

namespace Newscoop\ExamplePluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/testnewscoop")
     */
    public function indexAction(Request $request)
    {
        return $this->render('NewscoopExamplePluginBundle:Default:index.html.smarty');
    }

    /**
     * @Route("/admin/example_plugin")
     * @Template()
     */
    public function adminAction(Request $request)
    {
    	return array();
    }
}
