<?php
defined('SYSTEM_IN') or exit('Access Denied');
class homeAddons  extends BjSystemModule {
    public function do_control($name=''){
		if ( !empty($name) ){
			$this->__mobile($name);
		}else{
			exit('控制器不存在');
		}
	}
}

