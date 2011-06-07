<?php
/**
 * Wedge
 *
 * Displays a given topic, be it a blog or forum topic.
 *
 * @package wedge
 * @copyright 2010-2011 Wedgeward, wedge.org
 * @license http://wedge.org/license/
 *
 * @version 0.1
 */

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// OK, we're going to need this!
	add_js_file('scripts/topic.js');

	// Let them know, if their report was a success!
	if ($context['report_sent'])
		echo '
			<div class="windowbg" id="profile_success">
				', $txt['report_sent'], '
			</div>';

	// Or let them know if their draft was saved.
	template_display_draft();

	// Show the anchor for the top and for the first message. If the first message is new, say so.
	echo '
			<a id="top"></a><a id="msg', $context['first_message'], '"></a>', $context['first_new_message'] ? '<a id="new"></a>' : '';

	// Build the normal button array.
	$normal_buttons = array(
		'reply' => array('test' => 'can_reply', 'text' => 'reply', 'image' => 'reply.gif', 'lang' => true, 'url' => $scripturl . '?action=post;topic=' . $context['current_topic'] . '.' . $context['start'] . ';last_msg=' . $context['topic_last_message'], 'active' => true),
		'notify' => array('test' => 'can_mark_notify', 'text' => $context['is_marked_notify'] ? 'unnotify' : 'notify', 'image' => ($context['is_marked_notify'] ? 'un' : '') . 'notify.gif', 'lang' => true, 'custom' => 'onclick="return confirm(' . JavaScriptEscape($context['is_marked_notify'] ? $txt['notification_disable_topic'] : $txt['notification_enable_topic']) . ');"', 'url' => $scripturl . '?action=notify;sa=' . ($context['is_marked_notify'] ? 'off' : 'on') . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_query']),
		'mark_unread' => array('test' => 'can_mark_unread', 'text' => 'mark_unread', 'image' => 'markunread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=topic;t=' . $context['mark_unread_time'] . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_query']),
		'send' => array('test' => 'can_send_topic', 'text' => 'send_topic', 'image' => 'sendtopic.gif', 'lang' => true, 'url' => $scripturl . '?action=emailuser;sa=sendtopic;topic=' . $context['current_topic'] . '.0'),
		'print' => array('text' => 'print', 'image' => 'print.gif', 'lang' => true, 'custom' => 'rel="new_win nofollow"', 'url' => $scripturl . '?action=printpage;topic=' . $context['current_topic'] . '.0'),
	);

	// Allow adding new buttons easily.
	call_hook('display_buttons', array(&$normal_buttons));

	// Show the topic title, previous/next links and page index... "Pages: [1]".
	echo '
			<div class="posthead">', $context['prevnext_prev'], '
				<div id="top_subject">', $context['subject'], '</div>', $context['prevnext_next'], '
			</div>';


	// Is this topic also a poll?
	if ($context['is_poll'])
		template_topic_poll();

	// Does this topic have some events linked to it?
	if (!empty($context['linked_calendar_events']))
	{
		echo '
			<div class="linked_events">
				<we:title>
					', $txt['calendar_linked_events'], '
				</we:title>
				<div class="windowbg wrc">
					<ul class="reset">';

		foreach ($context['linked_calendar_events'] as $event)
			echo '
						<li>
							', ($event['can_edit'] ? '<a href="' . $event['modify_href'] . '"> <img src="' . $settings['images_url'] . '/icons/modify_small.gif" title="' . $txt['modify'] . '" class="edit_event"></a> ' : ''), '<strong>', $event['title'], '</strong>: ', $event['start_date'], ($event['start_date'] != $event['end_date'] ? ' - ' . $event['end_date'] : ''), '
						</li>';

		echo '
					</ul>
				</div>
			</div>';
	}

	echo '
			<div class="pagesection">', template_button_strip($normal_buttons, 'right'), '
				<div class="pagelinks floatleft">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#lastPost"><strong>' . $txt['go_down'] . '</strong></a>' : '', '</div>
			</div>', $context['browser']['is_ie6'] ? '
			<div class="clear"></div>' : '';

	// Show the topic information - icon, subject, etc.
	echo '
			<div id="forumposts">
				<form action="', $scripturl, '?action=quickmod2;topic=', $context['current_topic'], '.', $context['start'], '" method="post" accept-charset="UTF-8" name="quickModForm" id="quickModForm" style="margin: 0;" onsubmit="return window.oQuickModify && oQuickModify.sCurMessageId ? oQuickModify.modifySave(\'' . $context['session_id'] . '\', \'' . $context['session_var'] . '\') : false">';

	$ignoredMsgs = array();
	$removableMessageIDs = array();
	$alternate = false;
	$remove_confirm = JavaScriptEscape($txt['remove_message_confirm']);

	// Get all the messages...
	while ($message = $context['get_message']())
	{
		$ignoring = false;
		$alternate = !$alternate;
		if ($message['can_remove'])
			$removableMessageIDs[] = $message['id'];

		// Are we ignoring this message?
		if (!empty($message['is_ignored']))
		{
			$ignoring = true;
			$ignoredMsgs[] = $message['id'];
		}

		// Show the message anchor and a "new" anchor if this message is new.
		if ($message['id'] != $context['first_message'])
			echo '
				<a id="msg', $message['id'], '"></a>', $message['first_new'] ? '<a id="new"></a>' : '';

		echo '
				<div class="', $message['approved'] ? ($message['alternate'] == 0 ? 'windowbg' : 'windowbg2') : 'approvebg', ' wrc">
					<div class="post_wrapper">';

		// Show information about the poster of this message.
		echo '
						<div class="poster">
							<h4>';

		// Show online and offline buttons?
		if (!empty($modSettings['onlineEnable']) && !$message['member']['is_guest'])
			echo '
								', $context['can_send_pm'] ? '<a href="' . $message['member']['online']['href'] . '" title="' . $message['member']['online']['label'] . '">' : '', '<img src="', $message['member']['online']['image_href'], '" alt="', $message['member']['online']['text'], '">', $context['can_send_pm'] ? '</a>' : '';

		// Show a link to the member's profile.
		echo '
								<a href="', $message['member']['href'], '" id="um', $message['id'], '_', $message['member']['id'], '" class="umme">', $message['member']['name'], '</a>
							</h4>
							<ul class="reset smalltext" id="msg_', $message['id'], '_extra_info">';

		// Show the member's custom title, if they have one.
		if (!empty($message['member']['title']))
			echo '
								<li class="title">', $message['member']['title'], '</li>';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (!empty($message['member']['group']))
			echo '
								<li class="membergroup">', $message['member']['group'], '</li>';

		// Don't show these things for guests.
		if (!$message['member']['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
				echo '
								<li class="postgroup">', $message['member']['post_group'], '</li>';
			echo '
								<li class="stars">', $message['member']['group_stars'], '</li>';

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
				echo '
								<li class="avatar">
									<a href="', $scripturl, '?action=profile;u=', $message['member']['id'], '">
										', $message['member']['avatar']['image'], '
									</a>
								</li>';

			// Show how many posts they have made.
			if (!isset($context['disabled_fields']['posts']))
				echo '
								<li class="postcount">', $txt['member_postcount'], ': ', $message['member']['posts'], '</li>';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $message['member']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
				echo '
								<li class="gender">', $txt['gender'], ': ', $message['member']['gender']['image'], '</li>';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $message['member']['blurb'] != '')
				echo '
								<li class="blurb">', $message['member']['blurb'], '</li>';

			// Any custom fields to show as icons?
			if (!empty($message['member']['custom_fields']))
			{
				$shown = false;
				foreach ($message['member']['custom_fields'] as $custom)
				{
					if ($custom['placement'] != 1 || empty($custom['value']))
						continue;
					if (empty($shown))
					{
						$shown = true;
						echo '
								<li class="im_icons">
									<ul>';
					}
					echo '
										<li>', $custom['value'], '</li>';
				}
				if ($shown)
					echo '
									</ul>
								</li>';
			}

			// This shows the popular messaging icons.
			if ($message['member']['has_messenger'] && $message['member']['can_view_profile'])
				echo '
								<li class="im_icons">
									<ul>', !empty($message['member']['icq']['link']) ? '
										<li>' . $message['member']['icq']['link'] . '</li>' : '', !empty($message['member']['msn']['link']) ? '
										<li>' . $message['member']['msn']['link'] . '</li>' : '', !empty($message['member']['aim']['link']) ? '
										<li>' . $message['member']['aim']['link'] . '</li>' : '', !empty($message['member']['yim']['link']) ? '
										<li>' . $message['member']['yim']['link'] . '</li>' : '', '
									</ul>
								</li>';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				echo '
								<li class="profile">
									<ul>';
				// Don't show the profile button if you're not allowed to view the profile.
				if ($message['member']['can_view_profile'])
					echo '
										<li><a href="', $message['member']['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['view_profile'] . '" title="' . $txt['view_profile'] . '">' : $txt['view_profile']), '</a></li>';

				// Don't show an icon if they haven't specified a website.
				if ($message['member']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					echo '
										<li><a href="', $message['member']['website']['url'], '" title="' . $message['member']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $message['member']['website']['title'] . '">' : $txt['www']), '</a></li>';

				// Don't show the email address if they want it hidden.
				if (in_array($message['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					echo '
										<li><a href="', $scripturl, '?action=emailuser;sa=email;msg=', $message['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '">' : $txt['email']), '</a></li>';

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
										<li><a href="', $scripturl, '?action=pm;sa=send;u=', $message['member']['id'], '" title="', $message['member']['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($message['member']['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . ($message['member']['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline']) . '">' : ($message['member']['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline']), '</a></li>';

				// Show the IP address if you're suitably privileged.
				if ($message['can_see_ip'] && !empty($message['member']['ip']))
				{
					// Because this seems just a touch convoluted if a single line.
					if (!$context['can_moderate_forum'])
						echo '
										<li><a href="', $scripturl, '?action=help;in=see_member_ip" onclick="return reqWin(this);" class="help"><img src="', $settings['images_url'], '/ip.gif" alt="', $txt['ip'], ': ', $message['member']['ip'], '" title="', $txt['ip'], ': ', $message['member']['ip'], '"></a></li>';
					else
						echo '
										<li><a href="', $scripturl, '?action=', !empty($message['member']['is_guest']) ? 'trackip' : 'profile;u=' . $message['member']['id'] . ';area=tracking;sa=ip', ';searchip=', $message['member']['ip'], '"><img src="', $settings['images_url'], '/ip.gif" alt="', $txt['ip'], ': ', $message['member']['ip'], '" title="', $txt['ip'], ': ', $message['member']['ip'], '"></a></li>';
				}

				echo '
									</ul>
								</li>';
			}

			// Any custom fields for standard placement?
			if (!empty($message['member']['custom_fields']))
			{
				foreach ($message['member']['custom_fields'] as $custom)
					if (empty($custom['placement']) || empty($custom['value']))
						echo '
								<li class="custom">', $custom['title'], ': ', $custom['value'], '</li>';
			}

			// Are we showing the warning status?
			if ($message['member']['can_see_warning'])
				echo '
								<li class="warning">', $context['can_issue_warning'] && $message['member']['warning_status'] != 'ban' ? '<a href="' . $scripturl . '?action=profile;u=' . $message['member']['id'] . ';area=issuewarning">' : '', '<img src="', $settings['images_url'], '/warning_', $message['member']['warning_status'], '.gif" alt="', $txt['user_warn_' . $message['member']['warning_status']], '">', $context['can_issue_warning'] && $message['member']['warning_status'] != 'ban' ? '</a>' : '', '<span class="warn_', $message['member']['warning_status'], '">', $txt['warn_' . $message['member']['warning_status']], '</span></li>';
		}
		// Otherwise, show the guest's email.
		elseif (!empty($message['member']['email']) && in_array($message['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
			echo '
								<li class="email"><a href="', $scripturl, '?action=emailuser;sa=email;msg=', $message['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '">' : $txt['email']), '</a></li>';

		// Done with the information about the poster... on to the post itself.
		echo '
							</ul>
						</div>
						<div class="postarea">
							<div class="flow_hidden">
								<div class="keyinfo">
									<div class="messageicon">
										<img src="', $message['icon_url'] . '"', $message['can_modify'] ? ' id="msg_icon_' . $message['id'] . '"' : '', '>
									</div>
									<h5 id="subject_', $message['id'], '">
										<a href="', $message['href'], '" rel="nofollow">', $message['subject'], '</a>
									</h5>
									<div class="smalltext">&#171; <strong>', !empty($message['counter']) ? $txt['reply_noun'] . ' #' . $message['counter'] : '', ' ', '</strong> ', $message['time'], ' &#187;</div>
									<div id="msg_', $message['id'], '_quick_mod"></div>
								</div>';

		// If this is the first post, (#0) just say when it was posted - otherwise give the reply #.
		if ($message['can_approve'] || $context['can_reply'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'])
			echo '
								<ul class="reset smalltext quickbuttons">';

		// Can we merge this post to the previous one? (Normally requires same author)
		if ($message['can_mergeposts'])
			echo '
									<li class="mergepost_button"><a href="', $scripturl, '?action=mergeposts;pid=', $message['id'], ';msgid=', $message['last_post_id'], ';topic=', $context['current_topic'], '">', $txt['merge_double'], '</a></li>';

		// Maybe we can approve it, maybe we should?
		if ($message['can_approve'])
			echo '
									<li class="approve_button"><a href="', $scripturl, '?action=moderate;area=postmod;sa=approve;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_query'], '">', $txt['approve'], '</a></li>';

		// Can they reply? Have they turned on quick reply?
		if ($context['can_quote'] && !empty($options['display_quick_reply']))
			echo '
									<li class="quote_button"><a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';last_msg=', $context['topic_last_message'], '" id="quote_button_', $message['id'], '" onclick="return window.oQuickReply && oQuickReply.quote(this);">', $txt['quote'], '</a></li>';

		// So... quick reply is off, but they *can* reply?
		elseif ($context['can_quote'])
			echo '
									<li class="quote_button"><a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';last_msg=', $context['topic_last_message'], '">', $txt['quote'], '</a></li>';

		// Can the user modify the contents of this post?
		if ($message['can_modify'])
			echo '
									<li class="modify_button"><a href="', $scripturl, '?action=post;msg=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], '">', $txt['modify'], '</a></li>';

		// How about... even... remove it entirely?!
		if ($message['can_remove'])
			echo '
									<li class="remove_button"><a href="', $scripturl, '?action=deletemsg;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_query'], '" onclick="return confirm(', $remove_confirm, ');">', $txt['remove'], '</a></li>';

		// What about splitting it off the rest of the topic?
		if ($context['can_split'] && !empty($context['real_num_replies']))
			echo '
									<li class="split_button"><a href="', $scripturl, '?action=splittopics;topic=', $context['current_topic'], '.0;at=', $message['id'], '">', $txt['split'], '</a></li>';

		// Can we restore topics?
		if ($context['can_restore_msg'])
			echo '
									<li class="restore_button"><a href="', $scripturl, '?action=restoretopic;msgs=', $message['id'], ';', $context['session_query'], '">', $txt['restore_message'], '</a></li>';

		// Show a checkbox for quick moderation?
		if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $message['can_remove'])
			echo '
									<li class="inline_mod_check" style="display: none;" id="in_topic_mod_check_', $message['id'], '"></li>';

		if ($message['can_approve'] || $context['can_reply'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'])
			echo '
								</ul>';

		echo '
							</div>';

		// Ignoring this user? Hide the post.
		if ($ignoring)
			echo '
							<div id="msg_', $message['id'], '_ignored_prompt">
								', $txt['ignoring_user'], '
								<a href="#" id="msg_', $message['id'], '_ignored_link" style="display: none">', $txt['show_ignore_user_post'], '</a>
							</div>';

		// Show the post itself, finally!
		echo '
							<div class="post">';

		if (!$message['approved'] && $message['member']['id'] != 0 && $message['member']['id'] == $context['user']['id'])
			echo '
								<div class="approve_post">
									', $txt['post_awaiting_approval'], '
								</div>';
		echo '
								<div class="inner" id="msg_', $message['id'], '"', '>', $message['body'], '</div>
							</div>';

		// Can the user modify the contents of this post?  Show the modify inline image.
		if ($message['can_modify'])
			echo '
							<div class="modifybutton" id="modify_button_', $message['id'], '" title="', $txt['modify_msg'], '" onclick="if (window.oQuickModify) oQuickModify.modifyMsg(this);" onmousedown="return false;">&nbsp;</div>';

		// Assuming there are attachments...
		if (!empty($message['attachment']))
		{
			echo '
							<div id="msg_', $message['id'], '_footer" class="attachments smalltext">
								<div style="overflow: ', $context['browser']['is_firefox'] ? 'visible' : 'auto', ';">';

			$last_approved_state = 1;
			foreach ($message['attachment'] as $attachment)
			{
				// Show a special box for unapproved attachments...
				if ($attachment['is_approved'] != $last_approved_state)
				{
					$last_approved_state = 0;
					echo '
									<fieldset>
										<legend>', $txt['attach_awaiting_approve'];

					if ($context['can_approve'])
						echo '&nbsp;[<a href="', $scripturl, '?action=attachapprove;sa=all;mid=', $message['id'], ';', $context['session_query'], '">', $txt['approve_all'], '</a>]';

					echo '</legend>';
				}

				if ($attachment['is_image'])
				{
					if ($attachment['thumbnail']['has_thumb'])
						echo '
										<a href="', $attachment['href'], ';image" id="link_', $attachment['id'], '" onclick="', $attachment['thumbnail']['javascript'], '"><img src="', $attachment['thumbnail']['href'], '" id="thumb_', $attachment['id'], '"></a><br>';
					else
						echo '
										<img src="' . $attachment['href'] . ';image" width="' . $attachment['width'] . '" height="' . $attachment['height'] . '"><br>';
				}
				echo '
										<a href="' . $attachment['href'] . '"><img src="' . $settings['images_url'] . '/icons/clip.gif" class="middle">&nbsp;' . $attachment['name'] . '</a> ';

				if (!$attachment['is_approved'] && $context['can_approve'])
					echo '
										[<a href="', $scripturl, '?action=attachapprove;sa=approve;aid=', $attachment['id'], ';', $context['session_query'], '">', $txt['approve'], '</a>]&nbsp;|&nbsp;[<a href="', $scripturl, '?action=attachapprove;sa=reject;aid=', $attachment['id'], ';', $context['session_query'], '">', $txt['delete'], '</a>] ';
				echo '
										(', $attachment['size'], ($attachment['is_image'] ? ', ' . $attachment['real_width'] . 'x' . $attachment['real_height'] . ' - ' . $txt['attach_viewed'] : ' - ' . $txt['attach_downloaded']) . ' ' . $attachment['downloads'] . ' ' . $txt['attach_times'] . '.)<br>';
			}

			// If we had unapproved attachments clean up.
			if ($last_approved_state == 0)
				echo '
									</fieldset>';

			echo '
								</div>
							</div>';
		}

		echo '
						</div>
						<div class="moderatorbar">
							<div class="smalltext modified" id="modified_', $message['id'], '">';

		// Show "� Last Edit: Time by Person �" if this post was edited.
		if ($settings['show_modify'] && !empty($message['modified']['name']))
			echo '
								&#171; <em>', $txt['last_edit'], ' ', $message['modified']['time'], $message['modified']['name'] !== $message['member']['name'] ? ' ' . $txt['by'] . ' ' . $message['modified']['name'] : '', '</em> &#187;';

		echo '
							</div>
							<div class="smalltext reportlinks">';

		// Maybe they want to report this post to the moderator(s)?
		if ($context['can_report_moderator'])
			echo '
								<a href="', $scripturl, '?action=reporttm;topic=', $context['current_topic'], '.0;msg=', $message['id'], '">', $txt['report_to_mod'], '</a> &nbsp;';

		// Can we issue a warning because of this post?  Remember, we can't give guests warnings.
		if ($context['can_issue_warning'] && !$message['is_message_author'] && !$message['member']['is_guest'])
			echo '
								<a href="', $scripturl, '?action=profile;u=', $message['member']['id'], ';area=issuewarning;msg=', $message['id'], '"><img src="', $settings['images_url'], '/warn.gif" alt="', $txt['issue_warning_post'], '" title="', $txt['issue_warning_post'], '"></a>';

		echo '
							</div>';

		// Are there any custom profile fields for above the signature?
		if (!empty($message['member']['custom_fields']))
		{
			$shown = false;
			foreach ($message['member']['custom_fields'] as $custom)
			{
				if ($custom['placement'] != 2 || empty($custom['value']))
					continue;
				if (empty($shown))
				{
					$shown = true;
					echo '
							<div class="custom_fields_above_signature">
								<ul class="reset nolist">';
				}
				echo '
									<li>', $custom['value'], '</li>';
			}
			if ($shown)
				echo '
								</ul>
							</div>';
		}

		// Show the member's signature?
		if (!empty($message['member']['signature']) && empty($options['show_no_signatures']) && $context['signature_enabled'])
			echo '
							<div class="signature" id="msg_', $message['id'], '_signature">', $message['member']['signature'], '</div>';

		echo '
						</div>
					</div>
				</div>
				<hr class="post_separator">';
	}

	echo '
				</form>
			</div>
			<a id="lastPost"></a>';

	// Show the page index... "Pages: [1]".
	echo '
			<div class="pagesection">', template_button_strip($normal_buttons, 'right'), '
				<div class="pagelinks floatleft">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#top"><strong>' . $txt['go_up'] . '</strong></a>' : '', '</div>
			</div>';

	// Show the jumpto box, or... Actually let JavaScript do it.
	echo '
			<div class="posthead">', $context['prevnext_prev'], '
				<div id="display_jump_to"></div>', $context['prevnext_next'], '
			</div>';

	// Show the lower breadcrumbs.
	$context['bottom_linktree'] = true;

	$mod_buttons = array(
		'move' => array('test' => 'can_move', 'text' => 'move_topic', 'image' => 'admin_move.gif', 'lang' => true, 'url' => $scripturl . '?action=movetopic;topic=' . $context['current_topic'] . '.0'),
		'delete' => array('test' => 'can_delete', 'text' => 'remove_topic', 'image' => 'admin_rem.gif', 'lang' => true, 'custom' => 'onclick="return confirm(' . JavaScriptEscape($txt['are_sure_remove_topic']) . ');"', 'url' => $scripturl . '?action=removetopic2;topic=' . $context['current_topic'] . '.0;' . $context['session_query']),
		'lock' => array('test' => 'can_lock', 'text' => empty($context['is_locked']) ? 'set_lock' : 'set_unlock', 'image' => 'admin_lock.gif', 'lang' => true, 'url' => $scripturl . '?action=lock;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_query']),
		'sticky' => array('test' => 'can_sticky', 'text' => empty($context['is_sticky']) ? 'set_sticky' : 'set_nonsticky', 'image' => 'admin_sticky.gif', 'lang' => true, 'url' => $scripturl . '?action=sticky;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_query']),
		'merge' => array('test' => 'can_merge', 'text' => 'merge', 'image' => 'merge.gif', 'lang' => true, 'url' => $scripturl . '?action=mergetopics;board=' . $context['current_board'] . '.0;from=' . $context['current_topic']),
		'calendar' => array('test' => 'calendar_post', 'text' => 'calendar_link', 'image' => 'linktocal.gif', 'lang' => true, 'url' => $scripturl . '?action=post;calendar;msg=' . $context['topic_first_message'] . ';topic=' . $context['current_topic'] . '.0'),
		'add_poll' => array('test' => 'can_add_poll', 'text' => 'add_poll', 'image' => 'add_poll.gif', 'lang' => true, 'url' => $scripturl . '?action=poll;sa=editpoll;add;topic=' . $context['current_topic'] . '.' . $context['start']),
	);

	// Restore topic. Eh? No monkey business.
	if ($context['can_restore_topic'])
		$mod_buttons[] = array('text' => 'restore_topic', 'image' => '', 'lang' => true, 'url' => $scripturl . '?action=restoretopic;topics=' . $context['current_topic'] . ';' . $context['session_query']);

	// Allow adding new mod buttons easily.
	call_hook('mod_buttons', array(&$mod_buttons));

	echo '
			<div id="moderationbuttons">', template_button_strip($mod_buttons, 'left', array('id' => 'moderationbuttons_strip')), '</div>';

	if ($context['can_reply'] && !empty($options['display_quick_reply']))
		template_quick_reply();
	else
		echo '
			<br class="clear">';

	if ($context['show_spellchecking'] && (empty($context['footer']) || strpos($context['footer'], '"spell_form"') === false))
	{
		$context['footer'] .= '
<form action="' . $scripturl . '?action=spellcheck" method="post" accept-charset="UTF-8" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value=""></form>';
		add_js_file('scripts/spellcheck.js');
	}

	if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $context['can_remove_post'])
		add_js('
	var oInTopicModeration = new InTopicModeration({
		sSelf: \'oInTopicModeration\',
		sCheckboxContainerMask: \'in_topic_mod_check_\',
		aMessageIds: [' . implode(',', $removableMessageIDs) . '],
		sSessionId: \'' . $context['session_id'] . '\',
		sSessionVar: \'' . $context['session_var'] . '\',
		sButtonStrip: \'moderationbuttons\',
		sButtonStripDisplay: \'moderationbuttons_strip\',
		bUseImageButton: false,
		bCanRemove: ' . ($context['can_remove_post'] ? 'true' : 'false') . ',
		sRemoveButtonLabel: \'' . $txt['quickmod_delete_selected'] . '\',
		sRemoveButtonImage: \'delete_selected.gif\',
		sRemoveButtonConfirm: \'' . $txt['quickmod_confirm'] . '\',
		bCanRestore: ' . ($context['can_restore_msg'] ? 'true' : 'false') . ',
		sRestoreButtonLabel: \'' . $txt['quick_mod_restore'] . '\',
		sRestoreButtonImage: \'restore_selected.gif\',
		sRestoreButtonConfirm: \'' . $txt['quickmod_confirm'] . '\',
		sFormId: \'quickModForm\'
	});');

	add_js('
	if (can_ajax)
	{
		var oQuickModify = new QuickModify({
			sScriptUrl: smf_scripturl,
			bShowModify: ', $settings['show_modify'] ? 'true' : 'false', ',
			iTopicId: ' . $context['current_topic'] . ',
			sTemplateBodyEdit: ' . JavaScriptEscape('
				<div id="quick_edit_body_container" style="width: 90%">
					<div id="error_box" style="padding: 4px;" class="error"></div>
					<textarea class="editor" name="message" rows="12" style="' . ($context['browser']['is_ie8'] ? 'max-width: 100%; min-width: 100%' : 'width: 100%') . '; margin-bottom: 10px;" tabindex="' . $context['tabindex']++ . '">%body%</textarea><br>
					<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '">
					<input type="hidden" name="topic" value="' . $context['current_topic'] . '">
					<input type="hidden" name="msg" value="%msg_id%">
					<div class="righttext">
						<input type="submit" name="post" value="' . $txt['save'] . '" tabindex="' . $context['tabindex']++ . '" accesskey="s" onclick="return oQuickModify.modifySave(\'' . $context['session_id'] . '\', \'' . $context['session_var'] . '\');" class="save">&nbsp;&nbsp;' . ($context['show_spellchecking'] ? '<input type="button" value="' . $txt['spell_check'] . '" tabindex="' . $context['tabindex']++ . '" onclick="spellCheck(\'quickModForm\', \'message\');" class="spell">&nbsp;&nbsp;' : '') . '<input type="submit" name="cancel" value="' . $txt['modify_cancel'] . '" tabindex="' . $context['tabindex']++ . '" onclick="return oQuickModify.modifyCancel();" class="cancel">
					</div>
				</div>') . ',
			sTemplateSubjectEdit: ' . JavaScriptEscape('<input type="text" style="width: 90%" name="subject" value="%subject%" size="80" maxlength="80" tabindex="' . $context['tabindex']++ . '">') . ',
			sTemplateBodyNormal: ' . JavaScriptEscape('%body%') . ',
			sTemplateSubjectNormal: ' . JavaScriptEscape('<a href="' . $scripturl . '?topic=' . $context['current_topic'] . '.msg%msg_id%#msg%msg_id%" rel="nofollow">%subject%</a>') . ',
			sErrorBorderStyle: ' . JavaScriptEscape('1px solid red') . '
		});

		aJumpTo.push(new JumpTo({
			sContainerId: "display_jump_to",
			sJumpToTemplate: "<label for=\"%select_id%\">' . $context['jump_to']['label'] . ':<" + "/label> %dropdown_list%",
			iCurBoardId: ' . $context['current_board'] . ',
			iCurBoardChildLevel: ' . $context['jump_to']['child_level'] . ',
			sCurBoardName: "' . $context['jump_to']['board_name'] . '",
			sBoardChildLevelIndicator: "==",
			sBoardPrefix: "=> ",
			sCatSeparator: "-----------------------------",
			sCatPrefix: "",
			sGoButtonLabel: "' . $txt['go'] . '"
		}));

		aIconLists.push(new IconList({
			sBackReference: "aIconLists[" + aIconLists.length + "]",
			sIconIdPrefix: "msg_icon_",
			sScriptUrl: smf_scripturl,
			bShowModify: ', $settings['show_modify'] ? 'true' : 'false', ',
			iBoardId: ' . $context['current_board'] . ',
			iTopicId: ' . $context['current_topic'] . ',
			sSessionId: "' . $context['session_id'] . '",
			sSessionVar: "' . $context['session_var'] . '",
			sLabelIconList: "' . $txt['message_icon'] . '",
			sBoxBackground: "transparent",
			sBoxBackgroundHover: "#ffffff",
			iBoxBorderWidthHover: 1,
			sBoxBorderColorHover: "#adadad",
			sContainerBackground: "#ffffff",
			sContainerBorder: "1px solid #adadad",
			sItemBorder: "1px solid #ffffff",
			sItemBorderHover: "1px dotted gray",
			sItemBackground: "transparent",
			sItemBackgroundHover: "#e0e0f0"
		}));
	}');

	if (!empty($ignoredMsgs))
	{
		add_js('
	var aIgnoreToggles = [];');

		foreach ($ignoredMsgs as $msgid)
		{
			add_js('
	aIgnoreToggles[' . $msgid . '] = new smc_Toggle({
		bCurrentlyCollapsed: true,
		aSwappableContainers: [
			\'msg_' . $msgid . '_extra_info\',
			\'msg_' . $msgid . '\',
			\'msg_' . $msgid . '_footer\',
			\'msg_' . $msgid . '_quick_mod\',
			\'modify_button_' . $msgid . '\',
			\'msg_' . $msgid . '_signature\'
		],
		aSwapLinks: [
			{
				sId: \'msg_' . $msgid . '_ignored_link\',
				msgExpanded: \'\',
				msgCollapsed: ' . JavaScriptEscape($txt['show_ignore_user_post']) . '
			}
		]
	});');
		}
	}

	if (!empty($context['user_menu']))
	{
		add_js('
	var oUserMenuStrings = {
		pr: ', JavaScriptEscape($txt['usermenu_profile']), ',
		pm: ', JavaScriptEscape($txt['pm_menu_send']), ',
		we: ', JavaScriptEscape($txt['usermenu_website']), ',
		po: ', JavaScriptEscape($txt['usermenu_showposts']), ',
		ab: ', JavaScriptEscape($txt['usermenu_addbuddy']), ',
		rb: ', JavaScriptEscape($txt['usermenu_removebuddy']), ',
		re: ', JavaScriptEscape($txt['report_to_mod']), '
	};
	var oUserMenu = new UserMenu({');

		foreach ($context['user_menu'] as $user => $linklist)
			$context['footer_js'] .= '
		user' . $user . ': { ' . implode(', ', $linklist) . ' }, ';

		$context['footer_js'] = substr($context['footer_js'], 0, -2) . '
	});';
	}
}

function template_topic_poll()
{
	global $settings, $options, $context, $txt, $scripturl, $modSettings;

	// Build the poll moderation button array.
	$poll_buttons = array(
		'vote' => array('test' => 'allow_return_vote', 'text' => 'poll_return_vote', 'image' => 'poll_options.gif', 'lang' => true, 'url' => $scripturl . '?topic=' . $context['current_topic'] . '.' . $context['start']),
		'results' => array('test' => 'show_view_results_button', 'text' => 'poll_results', 'image' => 'poll_results.gif', 'lang' => true, 'url' => $scripturl . '?topic=' . $context['current_topic'] . '.' . $context['start'] . ';viewresults'),
		'change_vote' => array('test' => 'allow_change_vote', 'text' => 'poll_change_vote', 'image' => 'poll_change_vote.gif', 'lang' => true, 'url' => $scripturl . '?action=poll;sa=vote;topic=' . $context['current_topic'] . '.' . $context['start'] . ';poll=' . $context['poll']['id'] . ';' . $context['session_query']),
		'lock' => array('test' => 'allow_lock_poll', 'text' => (!$context['poll']['is_locked'] ? 'poll_lock' : 'poll_unlock'), 'image' => 'poll_lock.gif', 'lang' => true, 'url' => $scripturl . '?action=poll;sa=lockvoting;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_query']),
		'edit' => array('test' => 'allow_edit_poll', 'text' => 'poll_edit', 'image' => 'poll_edit.gif', 'lang' => true, 'url' => $scripturl . '?action=poll;sa=editpoll;topic=' . $context['current_topic'] . '.' . $context['start']),
		'remove_poll' => array('test' => 'can_remove_poll', 'text' => 'poll_remove', 'image' => 'admin_remove_poll.gif', 'lang' => true, 'custom' => 'onclick="return confirm(' . JavaScriptEscape($txt['poll_remove_warn']) . ');"', 'url' => $scripturl . '?action=poll;sa=removepoll;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_query']),
	);

	$show_voters = ($context['poll']['show_results'] || !$context['allow_vote']) && $context['allow_poll_view'];
	echo '
			<div id="poll_moderation">', template_button_strip($poll_buttons), '
			</div>
			<we:block id="poll" class="windowbg" header="', $txt['poll'], '" footer="', empty($context['poll']['expire_time']) ? '' :
				($context['poll']['is_expired'] ? $txt['poll_expired_on'] : $txt['poll_expires_on']) . ': ' . $context['poll']['expire_time'] . ($show_voters ? ' - ' : ''),
				$show_voters ? $txt['poll_total_voters'] . ': ' . $context['poll']['total_votes'] : '', '">
				<div id="poll_options">
					<h4 id="poll_question">
						<img src="', $settings['images_url'], '/topic/', $context['poll']['is_locked'] ? 'normal_poll_locked' : 'normal_poll', '.gif" style="vertical-align: -4px">
						', $context['poll']['question'], '
					</h4>';

	// Are they not allowed to vote but allowed to view the options?
	if ($context['poll']['show_results'] || !$context['allow_vote'])
	{
		echo '
					<dl class="options">';

		// Show each option with its corresponding percentage bar.
		foreach ($context['poll']['options'] as $option)
		{
			echo '
						<dt class="middletext', $option['voted_this'] ? ' voted' : '', '">', $option['option'], '</dt>
						<dd class="middletext statsbar', $option['voted_this'] ? ' voted' : '', '">';

			if ($context['allow_poll_view'])
				echo '
							', $option['bar_ndt'], '
							<span class="percentage">', $option['votes'], ' (', $option['percent'], '%)</span>';

			echo '
						</dd>';
		}

		echo '
					</dl>';
	}
	// They are allowed to vote! Go to it!
	else
	{
		echo '
					<form action="', $scripturl, '?action=poll;sa=vote;topic=', $context['current_topic'], '.', $context['start'], ';poll=', $context['poll']['id'], '" method="post" accept-charset="UTF-8">';

		// Show a warning if they are allowed more than one option.
		if ($context['poll']['allowed_warning'])
			echo '
						<p class="smallpadding">', $context['poll']['allowed_warning'], '</p>';

		echo '
						<ul class="reset options">';

		// Show each option with its button - a radio likely.
		foreach ($context['poll']['options'] as $option)
			echo '
							<li class="middletext"><label>', $option['vote_button'], ' ', $option['option'], '</label></li>';

		echo '
						</ul>
						<div class="submitbutton">
							<input type="submit" value="', $txt['poll_vote'], '">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
						</div>
					</form>';
	}

	echo '
				</div>
			</we:block>';
}

function template_quick_reply()
{
	global $settings, $options, $txt, $context, $scripturl, $modSettings;

	echo '
			<a id="quickreply"></a>
			<div id="quickreplybox">
				<we:cat>
					<a href="#" onclick="return window.oQuickReply && oQuickReply.swap();" onmousedown="return false;"><div id="quickReplyExpand"', $options['display_quick_reply'] == 2 ? ' class="fold"' : '', '></div></a>
					<a href="#" onclick="return window.oQuickReply && oQuickReply.swap();" onmousedown="return false;">', $txt['quick_reply'], '</a>
				</we:cat>
				<div id="quickReplyOptions"', $options['display_quick_reply'] == 2 ? '' : ' style="display: none"', '>
					<div class="roundframe">
						<p class="smalltext lefttext">', $txt['quick_reply_desc'], '</p>', $context['is_locked'] ? '
						<p class="alert smalltext">' . $txt['quick_reply_warning'] . '</p>' : '', !empty($context['oldTopicError']) ? '
						<p class="alert smalltext">' . sprintf($txt['error_old_topic'], $modSettings['oldTopicDays']) . '</p>' : '', $context['can_reply_approved'] ? '' : '
						<em>' . $txt['wait_for_approval'] . '</em>', !$context['can_reply_approved'] && $context['require_verification'] ? '
						<br>' : '', '
						<form action="', $scripturl, '?board=', $context['current_board'], ';action=post2" method="post" accept-charset="UTF-8" name="postmodify" id="postmodify" onsubmit="submitonce(this);">
							<input type="hidden" name="topic" value="', $context['current_topic'], '">
							<input type="hidden" name="subject" value="', $context['response_prefix'], $context['subject'], '">
							<input type="hidden" name="icon" value="xx">
							<input type="hidden" name="from_qr" value="1">
							<input type="hidden" name="notify" value="', $context['is_marked_notify'] || !empty($options['auto_notify']) ? '1' : '0', '">
							<input type="hidden" name="not_approved" value="', !$context['can_reply_approved'], '">
							<input type="hidden" name="goback" value="', empty($options['return_to_post']) ? '0' : '1', '">
							<input type="hidden" name="last_msg" value="', $context['topic_last_message'], '">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
							<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">';

	// Guests just need more.
	if ($context['user']['is_guest'])
			echo '
							<strong>', $txt['name'], ':</strong> <input type="text" name="guestname" value="', $context['name'], '" size="25" tabindex="', $context['tabindex']++, '" required>
							<strong>', $txt['email'], ':</strong> <input type="email" name="email" value="', $context['email'], '" size="25" tabindex="', $context['tabindex']++, '" required><br>';

	// Is visual verification enabled?
	if ($context['require_verification'])
		echo '
							<strong>', $txt['verification'], ':</strong>', template_control_verification($context['visual_verification_id'], 'quick_reply'), '<br>';

	echo '
							<div class="quickReplyContent">
								<div id="bbcBox_message" style="display: none"></div>
								<div id="smileyBox_message" style="display: none"></div>',
								$context['postbox']->outputEditor(), '
							</div>
							<div class="floatleft padding">
								<input type="button" name="switch_mode" id="switch_mode" value="', $txt['switch_mode'], '" style="display: none" onclick="if (window.oQuickReply) oQuickReply.switchMode();">
							</div>
							<div class="righttext padding">',
								$context['postbox']->outputButtons(), '
							</div>
						</form>
					</div>
				</div>
			</div>';

	add_js('
	var oQuickReply = new QuickReply({
		bDefaultCollapsed: ', !empty($options['display_quick_reply']) && $options['display_quick_reply'] == 2 ? 'false' : 'true', ',
		iTopicId: ' . $context['current_topic'] . ',
		iStart: ' . $context['start'] . ',
		sScriptUrl: smf_scripturl,
		sImagesUrl: "' . $settings['images_url'] . '",
		sContainerId: "quickReplyOptions",
		sImageId: "quickReplyExpand",
		sJumpAnchor: "quickreply",
		sBbcDiv: "', $context['postbox']->show_bbc ? 'bbcBox_message' : '', '",
		sSmileyDiv: "', !empty($context['postbox']->smileys['postform']) || !empty($context['postbox']->smileys['popup']) ? 'smileyBox_message' : '', '",
		sSwitchMode: "switch_mode",
		bUsingWysiwyg: ', $context['postbox']->rich_active ? 'true' : 'false', '
	});');
}

function template_display_whoviewing()
{
	global $txt, $context, $settings;

	echo '
			<we:title2>
				<img src="', $settings['images_url'], '/icons/online.gif" alt="', $txt['online_users'], '">', $txt['who_title'], '
			</we:title2>
			<p>';

	// Show just numbers...?
	if ($settings['display_who_viewing'] == 1)
		echo count($context['view_members']), ' ', count($context['view_members']) == 1 ? $txt['who_member'] : $txt['members'];
	// Or show the actual people viewing the topic?
	else
		echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) || $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');

	// Now show how many guests are here too.
	echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_topic'], '
			</p>';
}

function template_display_draft()
{
	global $context, $txt, $scripturl;

	if ($context['draft_saved'])
		echo '
	<div class="windowbg" id="profile_success">
		', str_replace('{draft_link}', $scripturl . '?action=profile;area=showdrafts', $txt['draft_saved']), '
	</div>';
}

// Show statistical style information...
function template_display_statistics()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	if (!$settings['show_stats_index'])
		return;

	echo '
				<we:title2>
					<img src="', $settings['images_url'], '/icons/info.gif" alt="', $txt['topic_stats'], '">
					', $txt['topic_stats'], '
				</we:title2>
				<p>
					', $txt['read'], ' ', $context['num_views'], ' ', $txt['times'], '
					<br>', $context['num_replies'], ' ', $txt['replies'], '
				</p>';
}

?>