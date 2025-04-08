<?php
function role_is($what) {
	$role = variable(USERROLE);
	return $what == $role;
}

DEFINE('COUNTRY', 'user-country');
DEFINE('USERROLE', 'user-role');
DEFINE('DEMOMODE', 'demo-mode');

DEFINE('REMOVEQS', '<i class="fa fa-lock-open"></i> &nbsp;&nbsp; <abbr title="remove action from url">NOQS</abbr>');

session_start();
if (_userAction('render') == 'logout' || variable('node') == 'logout') {
	session_destroy();
	variable(USERROLE, NOTLOGGEDIN);
} else {
	country('check');
	user_role('check');
	demo_mode('check');
}
session_commit();


function country($action) {
	$key = COUNTRY;
	$value = _sessionValue($key);
	$thisPage = pageUrl(variable('all_page_parameters')); //so it will stay on the same page...
	if($action == 'check') {
		if ($new = _userAction('set-' . $key))
			_sessionValue($key, $value = $key);
		variable($key, $value);
	} else if ($action == 'link') {
		$made = madeUserAction('set-' . $key);

		$items = variable('all-countries');
		foreach ($items as $slug => $name) {
			echo getLink('<b>' . $name . '</b>',
				pageUrl($thisPage . '?set-' . $key . '=' . $slug),
				'btn ' . ($value == $item['key'] ? 'disabled' : 'btn-info country country-') . $slug);
			
			if ($value == $item['key'] && $made)
				echo getLink(REMOVEQS, $thisPage, 'btn btn-info m-3');
			
			echo BRNL . BRNL;
		}

	} else if ($action == 'return') {
		return $value;
	}
}


function user_role($action) {
	$key = USERROLE;
	$value = _sessionValue($key);
	if ($action == 'check') {
		if ($new = _userAction('set-' . $key)) {
			_sessionValue($key, $new);
			variable($key, $new);
		} else if ($value == false) {
			_sessionValue($key, NOTLOGGEDIN);
			variable($key, NOTLOGGEDIN);
		} else {
			variable($key, $value);
		}
	} else if ($action == 'link') {
		$items = variable('all-roles');
		foreach ($items as $roleKey => $item) {
			if ($roleKey == NOTLOGGEDIN) continue;
			
			echo getLink('<b class="' . 'btn ' . ($value == $roleKey ? 'disabled' : 'btn-info') . '">'
				. $item['status'] . '</b> &mdash; ' . $item['about'],
				pageUrl('my/?set-' . $key . '=' . $roleKey));
			
			if ($value == $roleKey && madeUserAction('set-' . $key))
				echo getLink(REMOVEQS, pageUrl('my'), 'btn btn-info m-3');
			
			echo BRNL . BRNL;
		}
	} else if ($action == 'return') {
		return $value;
	}
}

function demo_mode($action) {
	$key = DEMOMODE;
	$value = _sessionValue($key);
	if($action == 'check') {
		if ($new = _userAction($key) == 'toggle')
			_sessionValue($key, $value = !$value);
		variable($key, $value);
	} else if ($action == 'link') {
		$made = madeUserAction($key);
		echo getLink($value ? 'Exit Demo Mode' : 'Go Into Demo Mode',
			pageUrl('my/?' . $key . '=toggle'),
			($made ? 'disabled ' : '') . 'btn btn-' . ($value ? 'warning' : 'danger')) . BRNL . BRNL;
		if ($made) echo getLink('Stop Toggling Demo Mode',
			pageUrl('my'), 'btn btn-warning') . BRNL . BRNL;
	} else if ($action == 'return') {
		return $value;
	}
}

function madeUserAction($what) {
	return !!_userAction($what);
}

function _sessionValue($key, $value = null) {
	if ($value === null)
		return isset($_SESSION[$key]) ? $_SESSION[$key] : false;

	$_SESSION[$key] = $value;
}

function _userAction($what) {
	return isset($_GET[$what]) ? $_GET[$what] : false;
}
