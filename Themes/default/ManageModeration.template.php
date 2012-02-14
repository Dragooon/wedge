<?php
/**
 * Wedge
 *
 * Displays the moderation filters system.
 *
 * @package wedge
 * @copyright 2010-2012 Wedgeward, wedge.org
 * @license http://wedge.org/license/
 *
 * @version 0.1
 */

function template_modfilter_home()
{
	global $context, $txt;

	if (!empty($context['approved_all']))
		echo '
	<div class="windowbg" id="profile_success">
		', $txt['modfilter_all_approved'], '
	</div>';

	echo '
	<we:cat>', $txt['admin_mod_filters'], '</we:cat>';

	if (empty($context['rules']))
	{
		echo '
	<p class="description">', $txt['modfilter_norules'], '</p>';
	}

	foreach ($context['rules'] as $type => $rule_block)
	{
		echo '
	<table class="table_grid cs0" style="width: 100%">
		<thead>
			<tr class="catbg">
				<th scope="col" class="first_th" style="text-align:left">', $txt['modfilter_rule_' . $type], '</th>
				<th style="text-align:left">', $txt['modfilter_conditions'], '</th>
				<th scope="col" class="last_th"></th>
			</tr>
		</thead>
		<tbody>';

		$use_bg2 = true;
		foreach ($rule_block as $id => $rules)
		{
			$use_bg2 = !$use_bg2;

			$action = $rules['action'];
			$rule_params = $rules['criteria'];

			echo '
			<tr class="windowbg', $use_bg2 ? '2' : '', '">
				<td style="width:30%">', isset($txt['modfilter_action_' . $action]) ? $txt['modfilter_action_' . $action] : $action, '</td>
				<td>';

			$print_criteria = array();
			foreach ($rules['criteria'] as $criteria)
			{
				switch ($criteria['name'])
				{
					case 'boards':
						$str = isset($criteria['id']) ? $txt['modfilter_cond_boards_in'] : $txt['modfilter_cond_boards_ex'];
						$list = isset($criteria['id']) ? $criteria['id'] : $criteria['except-id'];
						foreach ($list as $k => $v)
						{
							if (isset($context['boards'][$v]))
								$list[$k] = '<a href="<URL>?board=' . $v . '">' . $context['boards'][$v]['name'] . '</a>';
							else
								$list[$k] = '<em>???? (#' . $v . ')</em>';
						}
						$print_criteria[] = $str . ' ' . implode(', ', $list);
						break;
					case 'userid':
						$str = isset($criteria['id']) ? $txt['modfilter_cond_userid_in'] : $txt['modfilter_cond_userid_ex'];
						$list = isset($criteria['id']) ? $criteria['id'] : $criteria['except-id'];
						foreach ($list as $k => $v)
						{
							if (isset($context['users'][$v]))
								$list[$k] = '<a href="<URL>?action=profile;u=' . $v . '">' . $context['users'][$v]['member_name'] . '</a>';
							else
								$list[$k] = '<em>???? (#' . $v . ')</em>';
						}
						$print_criteria[] = $str . ' ' . implode(', ', $list);
						break;
					case 'postcount':
					case 'warning':
						$print_criteria[] = $txt['modfilter_cond_' . $criteria['name']] . ' ' . $txt['modfilter_range_' . $criteria['term']] . ' ' . $criteria['value'];
						break;
					case 'subject':
					case 'body':
						if (empty($criteria['apply']))
							$print_criteria[] = $txt['modfilter_cond_' . $criteria['name'] . '_regex'] . ' ' . htmlspecialchars($criteria['value']);
						else
							$print_criteria[] = $txt['modfilter_cond_' . $criteria['name'] . '_' . $criteria['apply']] . ' ' . htmlspecialchars($criteria['value']) . ' ' . $txt['modfilter_case_' . ($criteria['case-ins'] ? 'insensitive' : 'sensitive')];
						break;
					case 'groups':
						$str = isset($criteria['id']) ? $txt['modfilter_cond_groups_in'] : $txt['modfilter_cond_groups_ex'];
						$list = isset($criteria['id']) ? $criteria['id'] : $criteria['except-id'];
						foreach ($list as $k => $v)
						{
							if (isset($context['membergroups'][$v]))
								$list[$k] = '<a href="<URL>?action=admin;area=membergroups;sa=edit;group=' . $v . '"' . (!empty($context['membergroups'][$v]['online_color']) ? ' style="color:' . $context['membergroups'][$v]['online_color'] .'"' : '') . '>' . $context['membergroups'][$v]['group_name'] . '</a>';
							else
								$list[$k] = '<em>???? (#' . $v . ')</em>';
						}
						$print_criteria[] = $str . ' ' . implode(', ', $list);
						break;
						break;
					case 'permission':
						$str = isset($criteria['id']) ? $txt['modfilter_cond_permissions_in'] : $txt['modfilter_cond_permissions_ex'];
						$list = isset($criteria['id']) ? $criteria['id'] : $criteria['except-id'];
						$print_criteria[] = $str . ' ' . implode(', ', $list);
						break;
					default:
						if (!empty($rules['function']))
							$print_criteria[] = $rules['function']($criteria);
						else
							$print_criteria[] = $txt['modfilter_cond_unknownrule'] . ' ' . $criteria['name'];
				}
			}

			echo '
					<ul><li>', implode('</li><li>', $print_criteria), '</li></ul>';

			echo '</td>
				<td style="width:15%" class="centertext"><a href="<URL>?action=admin;area=modfilters;sa=edit;type=', $type, ';rule=', ($id+1), '">', $txt['modify'], '</td>
			</tr>';
		}
		echo '
		</tbody>
	</table>';
	}

	echo '
	<form action="<URL>?action=admin;area=modfilters;sa=add" method="post">
		<div class="pagesection">
			<div class="floatright">
				<div class="additional_row" style="text-align: right;"><input type="submit" name="new" value="', $txt['modfilter_addrule'], '" class="new"></div>
			</div>
		</div>
	</form>

	<we:cat>', $txt['modfilter_approve_title'], '</we:cat>
	<form action="<URL>?action=admin;area=modfilters;sa=approveall" method="post">
		<div class="windowbg wrc">
			<p>', $txt['modfilter_approve_desc'], '</p>
			<input type="submit" value="', $txt['modfilter_approve_title'], '" class="submit">
		</div>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';

	echo '
	<br class="clear">';
}

function template_modfilter_add()
{
	global $context, $txt;

	echo '
	<we:cat>', $txt['modfilter_addrule'], '</we:cat>
	<form action="<URL>?action=admin;area=modfilters;sa=add2" method="post">
		<div class="windowbg2 wrc">
			<fieldset>
				<legend>', $txt['modfilter_action_legend'], '</legend>
				<p>', $txt['modfilter_action_desc'], '</p>
				<dl class="settings">
					<dt>', $txt['modfilter_action_rule'], '</dt>
					<select id="action" name="action" onchange="updateForm();">
						<option value="">', $txt['modfilter_action_selectone'], '</option>
						<option value="" class="hr" disabled></option>';

	foreach ($context['modfilter_action_list'] as $item)
	{
		if (empty($item) || empty($txt['modfilter_actionlist_' . $item]))
			echo '
						<option value="" class="hr" disabled></option>';
		else
			echo '
						<option value="', $item, '"> ', $txt['modfilter_actionlist_' . $item], '</option>';
	}

	echo '
					</select>
				</dl>
			</fieldset>
			<fieldset id="fs_applies">
				<legend>', $txt['modfilter_applies_legend'], '</legend>
				<p>', $txt['modfilter_applies_desc'], '</p>
				<dl class="settings">
					<dt>', $txt['modfilter_applies_rule'], '</dt>
					<dd>
						<label><input type="radio" name="applies" value="posts" onchange="updateForm();"> ', $txt['modfilter_applies_posts'], '</label><br>
						<label><input type="radio" name="applies" value="topics" onchange="updateForm();"> ', $txt['modfilter_applies_topics'], '</label><br>
					</dd>
				</dl>
			</fieldset>
			<fieldset id="fs_conds">
				<legend>', $txt['modfilter_conds_legend'], '</legend>
				<p>', $txt['modfilter_conds_desc'], '</p>
				<table class="table_grid cs0" style="width: 100%" id="conds">
					<thead>
						<tr class="catbg">
							<th scope="col" class="first_th" style="text-align:left; width: 40%">', $txt['modfilter_conds_item'], '</th>
							<th scope="col" style="text-align:left">', $txt['modfilter_conds_criteria'], '</th>
							<th scope="col" class="last_th" style="width: 10%"></th>
						</tr>
					</thead>
					<tbody id="conds_empty">
						<tr class="windowbg2">
							<td colspan="3" class="centertext">', $txt['modfilter_conds_no_conditions'], '</td>
						</tr>
					</tbody>
					<tbody id="conds_notempty"></tbody>
				</table>
				<br>
				<div class="righttext">
				', $txt['modfilter_conds_new'], '
					<select name="condtype" id="condtype" onchange="setRuleContent();">
						<option value="0">', $txt['modfilter_conds_select'], '</option>
						<option value="" class="hr" disabled></option>';

	foreach ($context['modfilter_rule_types'] as $type)
		echo '
						<option value="', $type, '">', $txt['modfilter_condtype_' . $type], '</option>';

	echo '
					</select>
				</div>
				<div id="rulecontainer"></div>
			</fieldset>
			<div class="pagesection" id="btnSave">
				<div class="floatright">
					<div class="additional_row" style="text-align: right;"><input type="submit" class="save" value="', $txt['modfilter_save_this_rule'], '"></div>
				</div>
			</div>
		</div>
	</form>';

	add_js('
function updateForm()
{
	$("#btnSave").hide();

	if ($("#action").val() != "")
	{
		$("#fs_applies").show();

		var applies = $("input:radio[name=applies]:checked").val();
		if (applies == "posts" || applies == "topics")
		{
			$("#fs_conds").show();
		}
		else
		{
			$("#fs_conds").hide();
		}
	}
	else
	{
		$("#fs_applies, #fs_conds").hide();
	}
};
updateForm();

function setRuleContent()
{
	if ($("#condtype").val() != "")
	{
		$("#rulecontainer").html($("#container_" + $("#condtype").val()).html());
		$("#rulecontainer .ruleSave").hide();

		$("#rulecontainer input[data-eve]").each(function ()
		{
			var that = $(this);
			$.each(that.attr("data-eve").split(" "), function ()
			{
				that.bind(eves[this][0], eves[this][1]);
			});
		});
	}
	else
	{
		$("#rulecontainer").empty();
	}
};

rows_added = 0;
function addRow(rule, details, ruletype, rulevalue)
{
	rows_added++;
	$("#conds_empty").hide();
	$("#conds_notempty").append("<tr id=\"cond_row_" + rows_added + "\" class=\"windowbg\"><td>" + rule + "</td><td>" + details + "<input type=\"hidden\" name=\"rule[]\" rulevalue=\"" + ruletype + ";" + rulevalue + "\"></td><td class=\"centertext\"><a href=\"#\" onclick=\"removeRow(" + rows_added + "); return false;\">" + ' . JavaScriptEscape($txt['remove']) . ' + "</a></td></tr>");

	$("#condtype").val(0).sb();
	$("#rulecontainer").empty();
	$("#btnSave").show();
};

function removeRow(id)
{
	$("#cond_row_" + id).remove();
	if ($("#conds_notempty tr").length == 0)
	{
		$("#conds_empty").show();
		$("#btnSave").hide();
	}

	return false;
};');

	// Lastly before we go, make sure we dump all the containers for all the magic types.
	// It is not accidental that this is outside the core form.
	foreach ($context['modfilter_rule_types'] as $type)
	{
		$function = 'template_modfilter_' . $type;
		echo '
	<div id="container_', $type, '" style="display:none">', $function(), '
	</div>';
	}
}

function template_modfilter_groups()
{
	global $context, $txt;
	echo '
		<br>
		<label><input type="radio" name="appliesgroup" onchange="validateGroups();" value="id"> ', $txt['modfilter_applies_all'], '</label><br>
		<label><input type="radio" name="appliesgroup" onchange="validateGroups();" value="except-id"> ', $txt['modfilter_applies_allexcept'], '</label><br>
		<br>
		<div class="two-columns">
			<we:title>', $txt['membergroups_regular'], '</we:title>';

	foreach ($context['grouplist']['assign'] as $id => $group)
		echo '
			<label><input type="checkbox" class="groups" onchange="validateGroups();" name="groups[', $id, ']" value="', $id, '"> ', $group, '</label><br>';

		echo '
		</div>
		<div class="two-columns">
			<we:title>', $txt['membergroups_post'], '</we:title>';

	foreach ($context['grouplist']['post'] as $id => $group)
		echo '
			<label><input type="checkbox" class="groups" onchange="validateGroups();" name="groups[', $id, ']" value="', $id, '"> ', $group, '</label><br>';

		echo '
		</div>
		<div class="pagesection ruleSave">
			<div class="floatright">
				<input class="new" type="submit" value="', $txt['modfilter_condition_done'], '" onclick="addGroups(e);">
			</div>
		</div>';

	add_js('
function validateGroups()
{
	var applies_type = $("#rulecontainer input:radio[name=appliesgroup]:checked").val();
	if ((applies_type == "id" || applies_type == "except-id") && $("#rulecontainer input.groups:checked").length != 0)
	{
		$("#rulecontainer .ruleSave").show();
	}
	else
	{
		$("#rulecontainer .ruleSave").hide();
	}
};

function addGroups(e)
{
	e.preventDefault();
	var
		inGroups = ' . JavaScriptEscape($txt['modfilter_cond_groups_in']) . ',
		exGroups = ' . JavaScriptEscape($txt['modfilter_cond_groups_ex']) . ',
		groupStr = [],
		groupVal = [];

	var applies_type = $("#rulecontainer input:radio[name=appliesgroup]:checked").val();
	var groups = $("#rulecontainer input.groups:checked");
	if ((applies_type == "id" || applies_type == "except-id") && groups.length != 0)
	{
		$(groups).each(function() {
			groupVal.push($(this).val());
			var item = $(this).parent().children("span");
			var itemHtml = item.html();
			if (item.attr("style"))
			{
				itemHtml = "<span style=\"" + item.attr("style") + "\">" + itemHtml + "</span>";
			}
			groupStr.push(itemHtml);
		});
		addRow(applies_type == "id" ? inGroups : exGroups, groupStr.join(", "), "groups", applies_type + ";" + groupVal.join(","));
	}
}');
}

function template_modfilter_boards()
{
	global $context, $txt;
	echo '
		<br>
		<label><input type="radio" name="appliesboard" onchange="validateGroups();" value="id"> ', $txt['modfilter_applies_all'], '</label><br>
		<label><input type="radio" name="appliesboard" onchange="validateGroups();" value="except-id"> ', $txt['modfilter_applies_allexcept'], '</label><br>';

	foreach ($context['boardlist'] as $id_cat => $cat)
	{
		echo '
		<br>', $cat['name'], '<br>';
		foreach ($cat['boards'] as $id_board => $board)
		{
			echo '
			&nbsp; ', !empty($board['child_level']) ? str_repeat('&nbsp; ', $board['child_level']) : '', '<label><input type="checkbox" class="boards" onchange="validateBoards();" name="boards[', $id_board, ']" value="', $id_board, '"> <span>', $board['board_name'], '</span></label><br>';
		}
	}

	echo '
		<div class="pagesection ruleSave">
			<div class="floatright">
				<input class="new" type="submit" value="', $txt['modfilter_condition_done'], '" onclick="addBoards(e);">
			</div>
		</div>';

	add_js('
function validateBoards()
{
	var applies_type = $("#rulecontainer input:radio[name=appliesboard]:checked").val();
	if ((applies_type == "id" || applies_type == "except-id") && $("#rulecontainer input.boards:checked").length != 0)
	{
		$("#rulecontainer .ruleSave").show();
	}
	else
	{
		$("#rulecontainer .ruleSave").hide();
	}
};

function addBoards(e)
{
	e.preventDefault();
	var
		inBoards = ' . JavaScriptEscape($txt['modfilter_cond_boards_in']) . ',
		exBoards = ' . JavaScriptEscape($txt['modfilter_cond_boards_ex']) . ',
		boardStr = [],
		boardVal = [];

	var applies_type = $("#rulecontainer input:radio[name=appliesboard]:checked").val();
	var boards = $("#rulecontainer input.boards:checked");
	if ((applies_type == "id" || applies_type == "except-id") && boards.length != 0)
	{
		$(boards).each(function() {
			boardVal.push($(this).val());
			var item = $(this).parent().children("span");
			var itemHtml = item.html();
			if (item.attr("style"))
			{
				itemHtml = "<span style=\"" + item.attr("style") + "\">" + itemHtml + "</span>";
			}
			boardStr.push(itemHtml);
		});
		addRow(applies_type == "id" ? inBoards : exBoards, boardStr.join(", "), "boards", applies_type + ";" + boardVal.join(","));
	}
}');
}

?>