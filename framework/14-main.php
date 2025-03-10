<?php

main::initialize();

//NOTE: allows referring to values by name and avoids duplication
class main {

	static function initialize() {
		variable('systemEmail', 'imran@amadeusweb.com');
	}

	static function chat() {
		$val = variable('ChatraID');
		$val = $val && $val != 'none' ? ($val != '--use-amadeusweb' ? $val : 'wqzHJQrofB47q5oFj') : false;
		if (!$val) return;
		variable('ChatraID', $val);
		runModule('chatra');
	}

	static function analytics() {
		$val = variable('google-analytics');
		$val = $val && $val != 'none' ? ($val != '--use-amadeusweb' ? $val : 'UA-166048963-1') : false;
		if (!$val) return;
		variable('google-analytics', $val);
		runModule('google-analytics');
	}
}
