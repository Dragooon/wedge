<?php
/**
 * Wedge
 *
 * Displays the different kinds of administrative reports.
 *
 * @package wedge
 * @copyright 2010-2011 Wedgeward, wedge.org
 * @license http://wedge.org/license/
 *
 * @version 0.1
 */

// Choose which type of report to run?
function template_report_type()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<div id="admincenter">
		<form action="', $scripturl, '?action=admin;area=reports" method="post" accept-charset="UTF-8">
			<we:cat>
				', $txt['generate_reports'], '
			</we:cat>
			<div class="information">
				', $txt['generate_reports_desc'], '
			</div>
			<we:cat>
				', $txt['generate_reports_type'], '
			</we:cat>
			<div class="windowbg wrc">
				<dl class="generate_report">';

	// Go through each type of report they can run.
	foreach ($context['report_types'] as $type)
	{
		echo '
					<dt>
						<label><input type="radio" id="rt_', $type['id'], '" name="rt" value="', $type['id'], '"', $type['is_first'] ? ' checked' : '', '>
						<strong>', $type['title'], '</strong></label>
					</dt>';
		if (isset($type['description']))
			echo '
					<dd>', $type['description'], '</dd>';
	}

	echo '
				</dl>
				<div class="righttext">
					<input type="submit" name="continue" value="', $txt['generate_reports_continue'], '" class="submit">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				</div>
			</div>
		</form>
	</div>
	<br class="clear">';
}

// This is the standard template for showing reports in.
function template_main()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Build the reports button array.
	$report_buttons = array(
			'generate_reports' => array('text' => 'generate_reports', 'image' => 'print.gif', 'lang' => true, 'url' => $scripturl . '?action=admin;area=reports', 'active' => true),
			'print' => array('text' => 'print', 'image' => 'print.gif', 'lang' => true, 'url' => $scripturl . '?action=admin;area=reports;rt=' . $context['report_type']. ';st=print', 'custom' => 'target="_blank"'),
		);

	echo '
	<div id="admincenter">
		<we:title>
			', $txt['results'], '
		</we:title>
		<div id="report_buttons">';

	if (!empty($report_buttons))
		template_button_strip($report_buttons, 'right');

	echo '
		</div>';

	// Go through each table!
	foreach ($context['tables'] as $table)
	{
		echo '
		<table class="table_grid w100 cs0">';

		if (!empty($table['title']))
			echo '
			<thead>
				<tr class="catbg">
					<th scope="col" colspan="', $table['column_count'], '">', $table['title'], '</th>
				</tr>
			</thead>
			<tbody>';

		// Now do each row!
		$row_number = 0;
		$alternate = false;
		foreach ($table['data'] as $row)
		{
			if ($row_number == 0 && !empty($table['shading']['top']))
				echo '
				<tr class="windowbg table_caption">';
			else
				echo '
				<tr class="', !empty($row[0]['separator']) ? 'catbg' : ($alternate ? 'windowbg' : 'windowbg2'), ' top">';

			// Now do each column.
			$column_number = 0;

			foreach ($row as $key => $data)
			{
				// If this is a special separator, skip over!
				if (!empty($data['separator']) && $column_number == 0)
				{
					echo '
					<td colspan="', $table['column_count'], '" class="smalltext">
						', $data['v'], ':
					</td>';
					break;
				}

				// Shaded?
				if ($column_number == 0 && !empty($table['shading']['left']))
					echo '
					<td class="', $table['align']['shaded'], ' table_caption"', $table['width']['shaded'] != 'auto' ? ' style="width: ' . $table['width']['shaded'] . 'px"' : '', '>
						', $data['v'] == $table['default_value'] ? '' : ($data['v'] . (empty($data['v']) ? '' : ':')), '
					</td>';
				else
					echo '
					<td class="smalltext ', $table['align']['normal'], '"', $table['width']['normal'] != 'auto' ? ' style="width: ' . $table['width']['normal'] . 'px' . (!empty($data['style']) ? '; ' . $data['style'] : '') . '"' : (!empty($data['style']) ? ' style="' . $data['style'] . '"' : ''), '>
						', $data['v'], '
					</td>';

				$column_number++;
			}

			echo '
				</tr>';

			$row_number++;
			$alternate = !$alternate;
		}
		echo '
			</tbody>
		</table>';
	}
	echo '
	</div>
	<br class="clear">';
}

// Header of the print page!
function template_print_above()
{
	global $context, $settings, $options, $txt;

	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
<head>
	<meta charset="utf-8">
	<title>', $context['page_title'], '</title>
	<link rel="stylesheet" href="', add_css_file('report'), '">
</head>
<body>';
}

function template_print()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Go through each table!
	foreach ($context['tables'] as $table)
	{
		echo '
	<div style="overflow: visible', $table['max_width'] != 'auto' ? '; width: ' . $table['max_width'] . 'px' : '', '">
		<table class="printable">';

		if (!empty($table['title']))
			echo '
			<tr class="catback">
				<td colspan="', $table['column_count'], '">
					', $table['title'], '
				</td>
			</tr>';

		// Now do each row!
		$alternate = false;
		$row_number = 0;
		foreach ($table['data'] as $row)
		{
			if ($row_number == 0 && !empty($table['shading']['top']))
				echo '
			<tr class="titleback top">';
			else
				echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', ' top">';

			// Now do each column!!
			$column_number = 0;
			foreach ($row as $key => $data)
			{
				// If this is a special separator, skip over!
				if (!empty($data['separator']) && $column_number == 0)
				{
					echo '
				<td colspan="', $table['column_count'], '" class="catback">
					<strong>', $data['v'], ':</strong>
				</td>';
					break;
				}

				// Shaded?
				if ($column_number == 0 && !empty($table['shading']['left']))
					echo '
				<td class="', $table['align']['shaded'], ' titleback"', $table['width']['shaded'] != 'auto' ? ' style="width: ' . $table['width']['shaded'] . 'px"' : '', '>
					', $data['v'] == $table['default_value'] ? '' : ($data['v'] . (empty($data['v']) ? '' : ':')), '
				</td>';
				else
					echo '
				<td class="', $table['align']['normal'], '"', $table['width']['normal'] != 'auto' ? ' style="width: ' . $table['width']['normal'] . 'px' . (!empty($data['style']) ? '; ' . $data['style'] : '') . '"' : (!empty($data['style']) ? ' style="' . $data['style'] . '"' : ''), '>
					', $data['v'], '
				</td>';

				$column_number++;
			}

			echo '
			</tr>';

			$row_number++;
			$alternate = !$alternate;
		}
		echo '
		</table>
	</div>
	<br>';
	}
}

// Footer of the print page.
function template_print_below()
{
	global $context, $settings, $options;

	echo '
	<div class="copyright">', theme_copyright(), '</div>
</body>
</html>';
}

?>