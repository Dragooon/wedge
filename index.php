<?php
/**
 * Welcome to Wedge.
 *
 * Wedge (http://wedge.org)
 * Copyright © 2010 René-Gilles Deberdt, wedge.org
 * Portions are © 2011 Simple Machines.
 * License: http://wedge.org/license/
 */

if (defined('WEDGE'))
	return;

define('WEDGE_VERSION', '1.0-alpha-1');
define('WEDGE', 2); // Internal snapshot number.

// Get everything started up...
if (version_compare(PHP_VERSION, '5.4') < 0 && function_exists('set_magic_quotes_runtime'))
	@set_magic_quotes_runtime(0);

error_reporting(E_ALL | E_STRICT);
$time_start = microtime(true);

// Makes sure that headers can be sent!
ob_start();

define('ROOT_DIR', str_replace('\\', '/', dirname(__FILE__)));

// Is it our first run..?
if (!file_exists(ROOT_DIR . '/Settings.php'))
{
	require_once(ROOT_DIR . '/core/app/OriginalFiles.php');
	create_settings_file();
	create_generic_folders();
	create_main_htaccess();
}

// Load our settings...
require_once(ROOT_DIR . '/Settings.php');

// Crucial paths.
$boarddir = ROOT_DIR;
foreach (array('source' => 'core/app', 'cache' => 'gz', 'css' => 'gz/css', 'js' => 'gz/js') as $var => $path)
	${$var . 'dir'} = ROOT_DIR . '/' . $path;

// And important files.
loadSource(array(
	'Class-System',
	'QueryString',
	'Subs',
	'Errors',
	'Load',
	'Security',
));

// Are we installing, or doing something that needs the forum to be down?
if (!empty($maintenance) && $maintenance > 1)
{
	if ($maintenance == 2) // Installing
		require_once(ROOT_DIR . '/install/install.php');
	else // Downtime
		show_db_error();
	return;
}

// Initiate the database connection.
loadDatabase();

// Upgrade if the latest version needs it.
if (empty($we_shot) || $we_shot < WEDGE)
{
	loadSource('Upgrade');
	upgrade_db();
}

// Load the actions and database settings, and perform operations
// like optimizing or running scheduled tasks.
loadSettings();

// Register an error handler.
set_error_handler('error_handler');

// Start the session, if it hasn't already been.
loadSession();

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Special case: session keep-alive, do nothing.
if ($action === 'keepalive')
	exit;

// Allow modifying $action_list/$action_no_log globals easily.
call_hook('action_list');

$context['action'] = $action = isset($action_list[$action]) ? $action : (isset($settings['default_action'], $action_list[$settings['default_action']]) ? $settings['default_action'] : '');
$context['subaction'] = isset($_GET['sa']) ? $_GET['sa'] : null;

// Load the user's cookie (or set as guest) and load their settings.
we::getInstance();

// Check the request for anything hinky.
checkUserBehavior();

// Allow plugins to check for the request as well, and manipulate $action.
call_hook('behavior', array(&$action));

// Last chance to get the board ID if we have a default one. Use the 'behavior' hook to force it.
if (empty($action) && empty($board) && empty($topic))
{
	if (isset($_GET['category']) && is_numeric($_GET['category']))
		$action = 'boards';
	elseif (isset($settings['default_index']) && strpos($settings['default_index'], 'board') === 0)
		$board = (int) substr($settings['default_index'], 5);
}

// Load the current board's information.
loadBoard();

// Load the current user's permissions.
loadPermissions();

// Load the current theme. Note that ?theme=1 will also work, may be used for guest theming.
// Attachments don't require the entire theme to be loaded.
if ($action !== 'dlattach' || empty($settings['allow_guestAccess']) || we::$is_member)
	loadTheme();

// Check if the user should be disallowed access.
is_not_banned();

// If we are in a topic and don't have permission to approve it then duck out now.
if (!empty($topic) && $action !== 'feed' && empty($board_info['cur_topic_approved']) && !allowedTo('approve_posts'))
	if (MID != $board_info['cur_topic_starter'] || we::$is_guest)
		fatal_lang_error('not_a_topic', false);

// Do some logging, unless this is an attachment, avatar, toggle of editor buttons, theme option, XML feed etc.
if (!$action || !in_array($action, $action_no_log))
{
	// Log this user as online.
	writeLog();

	// Track forum statistics and hits...?
	if (!empty($settings['hitStats']))
		trackStats(array('hits' => '+'));
}

// What function shall we execute? (Done this way for memory's sake.)
call_user_func(determine_action($action));

wetem::add('sidebar', 'sidebar_quick_access');

// Just quickly sneak the feed stuff in...
if (!empty($settings['xmlnews_enable']) && !empty($settings['xmlnews_sidebar']) && (!empty($settings['allow_guestAccess']) || we::$is_member) && function_exists('template_sidebar_feed'))
	wetem::add('sidebar', 'sidebar_feed');

// I have only one thing to say to you... Bye!
obExit(null, null, true);

// Since we're not leaving obExit the special route, we need to make sure we update the error count.
if (!isset($settings['app_error_count']))
	$settings['app_error_count'] = 0;
if (!empty($context['app_error_count']))
	updateSettings(array('app_error_count' => $settings['app_error_count'] + $context['app_error_count']));

// Loads a named file from the app folder. Uses cache if possible.
// $source_name can be a string or an array of strings.
function loadSource($source_name)
{
	global $sourcedir, $cachedir, $db_show_debug;
	static $done = array();

	foreach ((array) $source_name as $file)
	{
		if (isset($done[$file]))
			continue;
		$done[$file] = true;
		if (defined('WEDGE_INSTALL') || strpos($file, 'getid3') !== false)
			$cache = $sourcedir . '/' . $file . '.php';
		else
		{
			$cache = $cachedir . '/app/' . str_replace(array('/', '..'), array('_', 'UP'), $file) . '.php';
			if (!file_exists($cache) || filemtime($cache) < filemtime($sourcedir . '/' . $file . '.php'))
			{
				copy($sourcedir . '/' . $file . '.php', $cache);
				// !! Disabling this temporarily (until I add a setting for it), to get proper line numbers when debugging.
				if (false && empty($db_show_debug))
				{
					require_once($sourcedir . '/Subs-MinifyPHP.php');
					minify_php($cache);
				}
			}
		}
		require_once($cache);
	}
}

function determine_action($action)
{
	global $settings, $board, $topic, $maintenance, $action_list;

	// Is the forum in maintenance mode? (doesn't apply to administrators.)
	if (!empty($maintenance) && !allowedTo('admin_forum'))
	{
		// You can only login.... otherwise, you're getting the "maintenance mode" display.
		if ($action === 'login2' || $action === 'logout')
		{
			loadSource($action = ucfirst($action));
			return $action;
		}
		// Welcome. You are unauthorized. Your death will now be implemented.
		else
		{
			loadSource('Subs-Auth');
			return 'InMaintenance';
		}
	}
	// If guest access is off, a guest can only do one of the very few following actions.
	elseif (empty($settings['allow_guestAccess']) && we::$is_guest && (empty($action) || !in_array($action, array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'mailq', 'verification'))))
	{
		loadSource('Subs-Auth');
		return 'KickGuest';
	}
	// The user might need to reagree to the agreement; post2 is here so we don't break drafts for posts
	// because that would really suck otherwise and PMs are allowed in case someone wants to discuss it.
	elseif (!empty($settings['agreement_force']) && (we::$user['activated'] == 6 && !we::$is_admin) && (empty($action) || !in_array($action, array('login', 'login2', 'logout', 'reminder', 'activate', 'mailq', 'post2', 'pm'))))
	{
		loadSource('Subs-Auth');
		return 'Reagree';
	}
	// Or not...
	elseif (empty($action))
	{
		// Action and board are both empty... Go home!
		if (empty($board) && empty($topic))
			return index_action();

		// Topic is empty, and action is empty.... MessageIndex!
		if (empty($topic))
		{
			loadSource('MessageIndex');
			return 'MessageIndex';
		}
		// Board is not empty... topic is not empty... action is empty.. Display!
		else
		{
			loadSource('Display');
			return 'Display';
		}
	}

	// Get the function and file to include - if it's not there, do the board index.
	if (!isset($action_list[$action]))
		return index_action('fallback_action');

	// Otherwise, it was set - so let's go to that action.
	$target = (array) $action_list[$action];
	if (isset($target[2]))
		loadPluginSource($target[2], $target[0]);
	else
		loadSource($target[0]);

	// Remember, if the function is the same as the filename, you may declare it just once.
	return isset($target[1]) ? $target[1] : $target[0];
}

function index_action($hook_action = 'default_action')
{
	global $settings, $sourcedir;

	// Some plugins may want to specify default "front page" behavior through the 'default_action' hook, and/or a
	// last-minute fallback ('fallback_action'). If they do, they shall return the name of the function they want to call.
	foreach (call_hook($hook_action) as $func)
		if (!empty($func) && is_callable($func))
			return $func;

	// Otherwise, if the admin specified a custom homepage, fall back to it.
	if (isset($settings['default_index']) && file_exists($sourcedir . '/' . $settings['default_index'] . '.php'))
	{
		loadSource($settings['default_index']);
		return $settings['default_index'];
	}

	loadSource('Boards');
	return 'Boards';
}
