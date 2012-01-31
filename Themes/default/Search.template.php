<?php
/**
 * Wedge
 *
 * Displays the search interface and results.
 *
 * @package wedge
 * @copyright 2010-2012 Wedgeward, wedge.org
 * @license http://wedge.org/license/
 *
 * @version 0.1
 */

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<form action="', $scripturl, '?action=search2" method="post" accept-charset="UTF-8" name="searchform" id="searchform">
		<we:cat>
			', !empty($settings['use_buttons']) ? '<img src="' . $settings['images_url'] . '/buttons/search.gif">' : '', $txt['set_parameters'], '
		</we:cat>';

	if (!empty($context['search_errors']))
		echo '
		<p id="search_error" class="error">', implode('<br>', $context['search_errors']['messages']), '</p>';

	// Simple Search?
	if ($context['simple_search'])
	{
		echo '
		<fieldset id="simple_search">
			<div class="roundframe">
				<div id="search_term_input">
					<strong>', $txt['search_for'], ':</strong>
					<input type="search" name="search"', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' maxlength="', $context['search_string_limit'], '" size="40" class="search">
					', $context['require_verification'] ? '' : '&nbsp;<input type="submit" name="submit" value="' . $txt['search'] . '" class="submit">
				</div>';

		if (empty($modSettings['search_simple_fulltext']))
			echo '
				<p class="smalltext">', $txt['search_example'], '</p>';

		if ($context['require_verification'])
			echo '
				<div class="verification>
					<strong>', $txt['search_visual_verification_label'], ':</strong>
					<br>', template_control_verification($context['visual_verification_id'], 'all'), '<br>
					<input id="submit" type="submit" name="submit" value="' . $txt['search'] . '" class="submit">
				</div>';

		echo '
				<a href="', $scripturl, '?action=search;advanced" onclick="this.href += \';search=\' + encodeURIComponent(document.forms.searchform.search.value);">', $txt['search_advanced'], '</a>
				<input type="hidden" name="advanced" value="0">
			</div>
		</fieldset>';
	}

	// Advanced search!
	else
	{
		add_js_inline('
	if (document.forms.searchform.search.value.indexOf("%") != -1)
		document.forms.searchform.search.value = decodeURIComponent(document.forms.searchform.search.value);');

		echo '
		<div class="roundframe">
			<fieldset id="advanced_search">
				<input type="hidden" name="advanced" value="1">
				<span class="enhanced">
					<strong>', $txt['search_for'], ':</strong>
					<input type="search" name="search"', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' maxlength="', $context['search_string_limit'], '" size="40" class="search">
					<select name="searchtype">
						<option value="1"', empty($context['search_params']['searchtype']) ? ' selected' : '', '>', $txt['all_words'], '</option>
						<option value="2"', !empty($context['search_params']['searchtype']) ? ' selected' : '', '>', $txt['any_words'], '</option>
					</select>
				</span>', empty($modSettings['search_simple_fulltext']) ? '
				<em class="smalltext">' . $txt['search_example'] . '</em>' : '', '
				<dl id="search_options">
					<dt>', $txt['by_user'], ':</dt>
					<dd><input id="userspec" type="text" name="userspec" value="', empty($context['search_params']['userspec']) ? '*' : $context['search_params']['userspec'], '" size="40"></dd>
					<dt>', $txt['search_order'], ':</dt>
					<dd>
						<select id="sort" name="sort">
							<option value="relevance|desc">', $txt['search_orderby_relevant_first'], '</option>
							<option value="num_replies|desc">', $txt['search_orderby_large_first'], '</option>
							<option value="num_replies|asc">', $txt['search_orderby_small_first'], '</option>
							<option value="id_msg|desc">', $txt['search_orderby_recent_first'], '</option>
							<option value="id_msg|asc">', $txt['search_orderby_old_first'], '</option>
						</select>
					</dd>
					<dt class="options">', $txt['search_options'], ':</dt>
					<dd class="options">
						<label><input type="checkbox" name="show_complete" id="show_complete" value="1"', !empty($context['search_params']['show_complete']) ? ' checked' : '', '> ', $txt['search_show_complete_messages'], '</label><br>
						<label><input type="checkbox" name="subject_only" id="subject_only" value="1"', !empty($context['search_params']['subject_only']) ? ' checked' : '', '> ', $txt['search_subject_only'], '</label>
					</dd>
					<dt class="between">', $txt['search_post_age'], ': </dt>
					<dd>', $txt['search_between'], ' <input type="text" name="minage" value="', empty($context['search_params']['minage']) ? '0' : $context['search_params']['minage'], '" size="5" maxlength="4">&nbsp;', $txt['search_and'], '&nbsp;<input type="text" name="maxage" value="', empty($context['search_params']['maxage']) ? '9999' : $context['search_params']['maxage'], '" size="5" maxlength="4"> ', $txt['days_word'], '</dd>
				</dl>';

		// Require an image to be typed to save spamming?
		if ($context['require_verification'])
			echo '
				<p>
					<strong>', $txt['verification'], ':</strong>
					', template_control_verification($context['visual_verification_id'], 'all'), '
				</p>';

		// If $context['search_params']['topic'] is set, that means we're searching just one topic.
		if (!empty($context['search_params']['topic']))
			echo '
				<p>', $txt['search_specific_topic'], ' &quot;', $context['search_topic']['link'], '&quot;.</p>
				<input type="hidden" name="topic" value="', $context['search_topic']['id'], '">';

		echo '
			</fieldset>';

		if (empty($context['search_params']['topic']))
		{
			echo '
			<br>
			<fieldset class="flow_hidden">
				<we:title2>
					<a href="#" onclick="expandCollapseBoards(); return false;"><div class="foldable" id="expandBoardsIcon" style="margin: 2px 2px -2px 0"></div></a> <a href="#" onclick="expandCollapseBoards(); return false;"><strong>', $txt['choose_board'], '</strong></a>
				</we:title2>
				<div id="searchBoardsExpand" class="flow_auto', $context['boards_check_all'] ? ' hide' : '', '">
					<ul class="ignoreboards floatleft">';

			$i = 0;

			// Vanity card #342. I offered this code to SMF back in June 2010, and the other devs promptly rejected it. Their loss!
			// Categories MUST be taken into account by $i, in case they each have very different numbers of boards. -- Nao

			$limit = max(12, ceil(($context['num_boards'] + count($context['categories'])) / 2));
			foreach ($context['categories'] as $category)
			{
				if ($i++ == $limit)
					echo '
					</ul>
					<ul class="ignoreboards floatright">';

				echo '
						<li class="category">
							<a href="#" onclick="selectBoards([', implode(', ', $category['child_ids']), ']); return false;">', $category['name'], '</a>
							<ul>';

				foreach ($category['boards'] as $board)
				{
					if ($i++ == $limit)
						echo '
							</ul>
						</li>
					</ul>
					<ul class="ignoreboards floatright">
						<li class="category">
							<ul>';

					echo '
								<li class="board" style="margin-', $context['right_to_left'] ? 'right' : 'left', ': ', $board['child_level'], 'em;">
									<label><input type="checkbox" id="brd', $board['id'], '" name="brd[', $board['id'], ']" value="', $board['id'], '"', $board['selected'] ? ' checked' : '', '> ', $board['name'], '</label>
								</li>';
				}

				echo '
							</ul>
						</li>';
			}

			echo '
					</ul>
				</div>
				<br class="clear">
				<div class="padding">
					<input type="checkbox" name="all" id="check_all" value=""', $context['boards_check_all'] ? ' checked' : '', ' onclick="invertAll(this, this.form, \'brd\');" class="floatleft">
					<label for="check_all" class="floatleft">', $txt['check_all'], '</label>
					<input type="submit" name="submit" value="', $txt['search'], '" class="submit floatright">
				</div>
				<br class="clear">
			</fieldset>
		</div>';
		}
	}

	echo '
	</form>';

	add_js('
	function selectBoards(ids)
	{
		var toggle = true;

		for (i = 0; i < ids.length; i++)
			toggle &= document.forms.searchform["brd" + ids[i]].checked;

		for (i = 0; i < ids.length; i++)
			document.forms.searchform["brd" + ids[i]].checked = !toggle;
	}

	function expandCollapseBoards()
	{
		var current = $("#searchBoardsExpand").is(":visible");

		$("#searchBoardsExpand").slideToggle(!current);
		$("#expandBoardsIcon").toggleClass("fold", !current);
	}');
}

function template_results()
{
	global $context, $settings, $options, $txt, $scripturl, $message;

	if (isset($context['did_you_mean']) || empty($context['topics']))
	{
		echo '
	<div id="search_results">
		<we:cat>
			', $txt['search_adjust_query'], '
		</we:cat>
		<div class="roundframe">';

		// Did they make any typos or mistakes, perhaps?
		if (isset($context['did_you_mean']))
			echo '
			<p>', $txt['search_did_you_mean'], ' <a href="', $scripturl, '?action=search2;params=', $context['did_you_mean_params'], '">', $context['did_you_mean'], '</a>.</p>';

		echo '
			<form action="', $scripturl, '?action=search2" method="post" accept-charset="UTF-8">
				<strong>', $txt['search_for'], ':</strong>
				<input type="search" name="search"', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' maxlength="', $context['search_string_limit'], '" size="40" class="search">
				<input type="submit" name="submit" value="', $txt['search_adjust_submit'], '">
				<input type="hidden" name="searchtype" value="', !empty($context['search_params']['searchtype']) ? $context['search_params']['searchtype'] : 0, '">
				<input type="hidden" name="userspec" value="', !empty($context['search_params']['userspec']) ? $context['search_params']['userspec'] : '', '">
				<input type="hidden" name="show_complete" value="', !empty($context['search_params']['show_complete']) ? 1 : 0, '">
				<input type="hidden" name="subject_only" value="', !empty($context['search_params']['subject_only']) ? 1 : 0, '">
				<input type="hidden" name="minage" value="', !empty($context['search_params']['minage']) ? $context['search_params']['minage'] : '0', '">
				<input type="hidden" name="maxage" value="', !empty($context['search_params']['maxage']) ? $context['search_params']['maxage'] : '9999', '">
				<input type="hidden" name="sort" value="', !empty($context['search_params']['sort']) ? $context['search_params']['sort'] : 'relevance', '">';

		if (!empty($context['search_params']['brd']))
			foreach ($context['search_params']['brd'] as $board_id)
				echo '
				<input type="hidden" name="brd[', $board_id, ']" value="', $board_id, '">';

		echo '
			</form>
		</div>
	</div>
	<br>';
	}

	if ($context['compact'])
	{
		// Quick moderation set to checkboxes? Oh, how fun... :/
		$show_checkboxes = !empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']);

		if ($show_checkboxes)
			echo '
	<form action="', $scripturl, '?action=quickmod" method="post" accept-charset="UTF-8" name="topicForm">';

		echo '
		<we:cat>
			<img src="', $settings['images_url'], '/buttons/search.gif">', $txt['mlist_search_results'], ':&nbsp;', $context['search_params']['search'], '
		</we:cat>
		<div class="pagesection">
			<nav>', $txt['pages'], ': ', $context['page_index'], '</nav>
		</div>';

		$quickmod = JavaScriptEscape($txt['quickmod_confirm']);

		while ($topic = $context['get_topics']())
		{
			$color_class = '';
			if ($topic['is_pinned'])
				$color_class .= ' pinned';
			if ($topic['is_locked'])
				$color_class .= ' locked';

			echo '
		<div class="search_results_posts">
			<div class="windowbg', $message['alternate'] == 0 ? '' : '2', ' wrc core_posts', $color_class, '">
				<div class="flow_auto">';

			foreach ($topic['matches'] as $message)
			{
				echo '
					<div class="topic_details floatleft" style="width: 94%">
						<div class="counter">', $message['counter'], '</div>
						<h5>', $topic['board']['link'], ' / <a href="', $scripturl, '?topic=', $topic['id'], '.msg', $message['id'], '#msg', $message['id'], '">', $message['subject_highlighted'], '</a></h5>
						<span class="smalltext">&#171;&nbsp;', $message['time'], ' ', $txt['by'], ' <strong>', $message['member']['link'], '</strong>&nbsp;&#187;</span>
					</div>';

				if (!empty($options['display_quick_mod']))
				{
					echo '
					<div class="floatright">';

					if ($show_checkboxes)
						echo '
						<input type="checkbox" name="topics[]" value="', $topic['id'], '">';
					else
					{
						if ($topic['quick_mod']['remove'])
							echo '
						<a href="', $scripturl, '?action=quickmod;actions[', $topic['id'], ']=remove;', $context['session_query'], '" onclick="return confirm(', $quickmod, ');"><img src="', $settings['images_url'], '/icons/quick_remove.gif" width="16" alt="', $txt['remove_topic'], '" title="', $txt['remove_topic'], '"></a>';

						if ($topic['quick_mod']['lock'])
							echo '
						<a href="', $scripturl, '?action=quickmod;actions[', $topic['id'], ']=lock;', $context['session_query'], '" onclick="return confirm(', $quickmod, ');"><img src="', $settings['images_url'], '/icons/quick_lock.gif" width="16" alt="', $txt['set_lock'], '" title="', $txt['set_lock'], '"></a>';

						if ($topic['quick_mod']['lock'] || $topic['quick_mod']['remove'])
							echo '
						<br>';

						if ($topic['quick_mod']['pin'])
							echo '
						<a href="', $scripturl, '?action=quickmod;actions[', $topic['id'], ']=pin;', $context['session_query'], '" onclick="return confirm(', $quickmod, ');"><img src="', $settings['images_url'], '/icons/quick_pin.gif" width="16" alt="', $txt['set_pin'], '" title="', $txt['set_pin'], '"></a>';

						if ($topic['quick_mod']['move'])
							echo '
						<a href="', $scripturl, '?action=movetopic;topic=', $topic['id'], '.0"><img src="', $settings['images_url'], '/icons/quick_move.gif" width="16" alt="', $txt['move_topic'], '" title="', $txt['move_topic'], '"></a>';
					}

					echo '
					</div>';
				}

				if ($message['body_highlighted'] != '')
					echo '
					<br class="clear">
					<div class="list_posts double_height">', $message['body_highlighted'], '</div>';
			}

			echo '
				</div>
			</div>
		</div>';
		}

		if (!empty($context['topics']))
			echo '
		<div class="pagesection">
			<nav>', $txt['pages'], ': ', $context['page_index'], '</nav>
		</div>';

		if ($show_checkboxes)
		{
			echo '
		<div class="roundframe">
			<div class="floatright">
				<select class="fixed" name="qaction"', $context['can_move'] ? ' onchange="$(\'#sbmoveItTo\').toggleClass(\'hide\', $(this).val() != \'move\');"' : '', '>
					<option value="">--- ', $txt['moderate'], ' ---</option>', $context['can_remove'] ? '
					<option value="remove">' . $txt['quick_mod_remove'] . '</option>' : '', $context['can_lock'] ? '
					<option value="lock">' . $txt['quick_mod_lock'] . '</option>' : '', $context['can_pin'] ? '
					<option value="pin">' . $txt['quick_mod_pin'] . '</option>' : '', $context['can_move'] ? '
					<option value="move">' . $txt['quick_mod_move'] . ': </option>' : '', $context['can_merge'] ? '
					<option value="merge">' . $txt['quick_mod_merge'] . '</option>' : '', '
					<option value="markread">', $txt['quick_mod_markread'], '</option>
				</select>';

			if ($context['can_move'])
			{
					echo '
				<select id="moveItTo" name="move_to" class="hide">';

					foreach ($context['move_to_boards'] as $category)
					{
						echo '
					<optgroup label="', $category['name'], '">';

						foreach ($category['boards'] as $board)
								echo '
					<option value="', $board['id'], '"', $board['selected'] ? ' selected' : '', '>', $board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '', ' ', $board['name'], '</option>';

						echo '
					</optgroup>';
					}
					echo '
				</select>';
			}

			echo '
				<input type="hidden" name="redirect_url" value="', $scripturl . '?action=search2;params=' . $context['params'], '">
				<input type="submit" style="font-size: 0.8em" value="', $txt['quick_mod_go'], '" onclick="return this.form.qaction.value != \'\' && confirm(', $quickmod, ');">
			</div>
			<br class="clear">
		</div>
		<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '">
	</form>';
		}
	}
	else
	{
		echo '
		<we:cat>
			<img src="' . $settings['images_url'] . '/buttons/search.gif">&nbsp;', $txt['mlist_search_results'], ':&nbsp;', $context['search_params']['search'], '
		</we:cat>
		<div class="pagesection">
			<nav>', $txt['pages'], ': ', $context['page_index'], '</nav>
		</div>';

		if (empty($context['topics']))
			echo '
		<div class="information">(', $txt['search_no_results'], ')</div>';

		while ($topic = $context['get_topics']())
		{
			foreach ($topic['matches'] as $message)
			{
				echo '
		<div class="search_results_posts">
			<div class="', $message['alternate'] == 0 ? 'windowbg' : 'windowbg2', ' wrc core_posts">
				<div class="counter">', $message['counter'], '</div>
				<div class="topic_details">
					<h5>', $topic['board']['link'], ' / <a href="', $scripturl, '?topic=', $topic['id'], '.', $message['start'], ';topicseen#msg', $message['id'], '">', $message['subject_highlighted'], '</a></h5>
					<span class="smalltext">&#171;&nbsp;', $message['time'], ' ', $txt['by'], ' <strong>', $message['member']['link'], '</strong>&nbsp;&#187;</span>
				</div>
				<div class="list_posts">', $message['body_highlighted'], '</div>';

				if ($topic['can_reply'] || $topic['can_mark_notify'])
					echo '
				<div class="quickbuttons_wrap">
					<ul class="quickbuttons">';

				// If they *can* reply?
				if ($topic['can_reply'])
					echo '
						<li class="reply_button"><a href="', $scripturl . '?action=post;topic=' . $topic['id'] . '.' . $message['start'], '">', $txt['reply'], '</a></li>';

				// If they *can* quote?
				if ($topic['can_quote'])
					echo '
						<li class="quote_button"><a href="', $scripturl . '?action=post;topic=' . $topic['id'] . '.' . $message['start'] . ';quote=' . $message['id'] . '">', $txt['quote'], '</a></li>';

				// Can we request notification of topics?
				if ($topic['can_mark_notify'])
					echo '
						<li class="notify_button"><a href="', $scripturl . '?action=notify;topic=' . $topic['id'] . '.' . $message['start'], '">', $txt['notify'], '</a></li>';

				if ($topic['can_reply'] || $topic['can_mark_notify'])
					echo '
					</ul>
				</div>';

				echo '
				<br class="clear">
			</div>
		</div>';
			}
		}

		echo '
		<div class="pagesection">
			<nav>', $txt['pages'], ': ', $context['page_index'], '</nav>
		</div>';
	}
}

?>