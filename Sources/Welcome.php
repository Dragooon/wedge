<?php
/**
 * Wedge
 *
 * This file is a sample homepage that can be used in place of your board index.
 *
 * @package wedge
 * @copyright 2010-2011 Wedgeward, wedge.org
 * @license http://wedge.org/license/
 *
 * @version 0.1
 */

if (!defined('WEDGE'))
	die('Hacking attempt...');

/*	It contains only the following function:

	void Welcome()
		- prepares the homepage data.
		- uses the Welcome template (main block) and language file.
		- is accessed via index.php, if $modSettings['default_index'] == 'Welcome'.
		- to create your own custom entry point, just set $modSettings['default_index'] to 'Homepage'
		  and create your own Homepage.php file so it won't be overwritten by Wedge updates!
*/

// Welcome to the show.
function Welcome()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $language, $user_info;

	// Load the 'Welcome' template.
	loadTemplate('Welcome');
	// We don't have language files for now, but in case we add them...
	// loadLanguage('Welcome');

	// Set a canonical URL for this page.
	$context['canonical_url'] = $scripturl;

	// Do not let search engines index anything if there is a random thing in $_GET.
	if (!empty($_GET))
		$context['robot_no_index'] = true;

	// In this case, we want to load the info center as well -- but not in the sidebar.
	// We first create an info layer at the end of the main block and inject the info center into it.
	// For the purpose of our sample, we're using the opportunity to skip the calendar and recent posts.
	loadTemplate('InfoCenter');
	loadLayer('info_center', 'default', 'lastchild');
	loadBlock(
		array(
			'info_center_statistics',
			'info_center_usersonline',
			'info_center_personalmsg',
		),
		'info_center'
	);

	/*
		Except for $context['page_title'], the rest is taken directly
		from Boards.php and used to generate the info center.
	*/

	// Get the user online list.
	loadSource('Subs-MembersOnline');
	$membersOnlineOptions = array(
		'show_hidden' => allowedTo('moderate_forum'),
		'sort' => 'log_time',
		'reverse_sort' => true,
	);
	$context += getMembersOnlineStats($membersOnlineOptions);

	$context['show_buddies'] = !empty($user_info['buddies']);

	// Are we showing all membergroups on the board index?
	if (!empty($settings['show_group_key']))
		$context['membergroups'] = cache_quick_get('membergroup_list', 'Subs-Membergroups.php', 'cache_getMembergroupList', array());

	// Track most online statistics? (Subs-MembersOnline.php)
	if (!empty($modSettings['trackStats']))
		trackStatsUsersOnline($context['num_guests'] + $context['num_spiders'] + $context['num_users_online']);

	// Retrieve the latest posts if the theme settings require it.
	if (isset($settings['number_recent_posts']) && $settings['number_recent_posts'] > 1)
	{
		$latestPostOptions = array(
			'number_posts' => $settings['number_recent_posts'],
		);
		$context['latest_posts'] = cache_quick_get('boards-latest_posts:' . md5($user_info['query_wanna_see_board'] . $user_info['language']), 'Subs-Recent.php', 'cache_getLastPosts', array($latestPostOptions));
	}

	$settings['display_recent_bar'] = !empty($settings['number_recent_posts']) ? $settings['number_recent_posts'] : 0;
	$settings['show_member_bar'] &= allowedTo('view_mlist');
	$context['show_stats'] = allowedTo('view_stats') && !empty($modSettings['trackStats']);
	$context['show_member_list'] = allowedTo('view_mlist');
	$context['show_who'] = allowedTo('who_view') && !empty($modSettings['who_enabled']);

	// Load the calendar?
	if (!empty($modSettings['cal_enabled']) && allowedTo('calendar_view'))
	{
		// Retrieve the calendar data (events, birthdays, holidays).
		$eventOptions = array(
			'include_holidays' => $modSettings['cal_showholidays'] > 1,
			'include_birthdays' => $modSettings['cal_showbdays'] > 1,
			'include_events' => $modSettings['cal_showevents'] > 1,
			'num_days_shown' => empty($modSettings['cal_days_for_index']) || $modSettings['cal_days_for_index'] < 1 ? 1 : $modSettings['cal_days_for_index'],
		);
		$context += cache_quick_get('calendar_index_offset_' . ($user_info['time_offset'] + $modSettings['time_offset']), 'Subs-Calendar.php', 'cache_getRecentEvents', array($eventOptions));

		// Whether one or multiple days are shown on the board index.
		$context['calendar_only_today'] = $modSettings['cal_days_for_index'] == 1;

		// This is used to show the "how-do-I-edit" help.
		$context['calendar_can_edit'] = allowedTo('calendar_edit_any');
	}
	else
		$context['show_calendar'] = false;

	$context['page_title'] = isset($txt['homepage_title']) ? $txt['homepage_title'] : sprintf($txt['forum_index'], $context['forum_name']);

	add_js('
	var oInfoCenterToggle = new weToggle({
		bCurrentlyCollapsed: ', empty($options['collapse_header_ic']) ? 'false' : 'true', ',
		aSwappableContainers: [\'upshrinkHeaderIC\'],
		aSwapImages: [{ sId: \'upshrink_ic\', altExpanded: ' . JavaScriptEscape($txt['upshrink_description']) . ' }],
		oThemeOptions: { bUseThemeSettings: ' . ($context['user']['is_guest'] ? 'false' : 'true') . ', sOptionName: \'collapse_header_ic\' },
		oCookieOptions: { bUseCookie: ' . ($context['user']['is_guest'] ? 'true' : 'false') . ', sCookieName: \'upshrinkIC\' }
	});');

	call_hook('info_center');
}

?>