<?php
function role_is($what) {
	$role = variable(USERROLE);
	return $what == $role;
}

DEFINE('USERROLE', 'user-role');
DEFINE('DEMOMODE', 'demo-mode');

session_start();
if (_userAction('render') == 'logout') {
	session_destroy();
	variable(USERROLE, NOTLOGGEDIN);
} else {
	user_role('check');
	demo_mode('check');
}
session_commit();

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
		foreach ($items as $item) {
			echo getLink('<b>' . $item['status'] . '</b> &mdash; (' . humanize($item['key']) . ')',
				pageUrl('my/?set-' . $key . '=' . $item['key']),
				'btn ' . ($value == $item['key'] ? 'disabled' : 'btn-info'));
			
			if ($value == $item['key'] && madeUserAction('set-' . $key))
				echo getLink('<i class="fa fa-lock-open"></i> &nbsp;&nbsp; <abbr title="remove action from url">NOQS</abbr>', pageUrl('my'), 'btn btn-info m-3');
			
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
