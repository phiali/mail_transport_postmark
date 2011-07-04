<?php

class App_Application_Resource_Postmark
	extends \Zend_Application_Resource_ResourceAbstract
{
	public function init()
	{
		$options = $this->getOptions();

		$transport = new Postmark_Mail_Transport_Postmark($options['apikey']);

		Zend_Mail::setDefaultTransport($transport);
	}
}


