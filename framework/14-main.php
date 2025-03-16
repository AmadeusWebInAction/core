<?php

main::initialize();

//NOTE: allows referring to values by name and avoids duplication
class main {

	static function initialize() {
		variables([
			//TODO: once reinstated! 'assistantEmail' => 'assistant@amadeusweb.world',
			'systemEmail' => 'team@amadeusweb.com',
		]);
	}

	static function defaultSocial() {
		return [
			[ 'type' => 'linkedin', 'url' => 'https://www.linkedin.com/company/amadeusweb/', 'name' => 'Amadeus Web' ],
			[ 'type' => 'linkedin', 'url' => 'https://www.linkedin.com/in/imran-ali-namazi/', 'name' => 'Founder Imran' ],
			[ 'type' => 'youtube', 'url' => 'https://www.youtube.com/@amadeuswebbuilder', 'name' => 'AMW Core' ],
			[ 'type' => 'github', 'url' => 'https://github.com/AmadeusWebInAction/', 'name' => 'AMW Network Code' ],
		];
	}

	static function defaultNetwork() {
		return [
			[ 'url' => variable('world') . 'daivism/', 'name' => 'Daivism Mission', 'icon' => 'daivism' ],
			[ 'url' => variable('world'), 'name' => 'AMW World', 'icon' => 'world' ],
			[ 'url' => variable('main'), 'name' => 'AMW Main', 'icon' => 'web' ],
			[ 'url' => variable('app'), 'name' => 'AMW Core v7', 'icon' => 'core' ],
			//TODO: WO + Listings + Imran
		];
	}

	static function runAndReturn() {
		doToBuffering(1);
		main::analytics();
		main::chat();
		$result = doToBuffering(2);
		doToBuffering(3);
		return $result;
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
