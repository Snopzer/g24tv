<?php
class Zend_Controller_Action_Helper_Test extends Zend_Controller_Action_Helper_Abstract
{
    function preDispatch()
    {
       // $view = $this->getActionController()->view;
       // $view->footerQuote = $this->getQuote();
    }
    
    
    function getQuote()
    {
        $quotes[] = 'I want to run, I want to hide, I want to tear down the walls';
        $quotes[] = 'One man come in the name of love, One man come and go';
        return $quotes[rand(0, count($quotes)-1)];
    }

	function getName()
	{
		return "suresh babu k";
	}
   
}