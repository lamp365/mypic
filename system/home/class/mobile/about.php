<?php
namespace home\controller;

class about extends \home\controller\base
{
	public function index()
	{
		$_GP     = $this->request;
		$seting  = globaSetting();
        $kefuQQ  = getQQ_onWork($seting);
		include themePage('about');
	}

	public function cc()
	{
		$a = new \twinh\tenpay\Tenpay();
		$a->index();
	}
}