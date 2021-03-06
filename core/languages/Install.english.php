<?php
// Version: 2.0; Install

// This should be the same as in index.language.php
$txt['lang_rtl'] = false;

$txt['install_step_welcome'] = 'Welcome';
$txt['install_step_writable'] = 'Writable Check';
$txt['install_step_forum'] = 'Forum Settings';
$txt['install_step_databaseset'] = 'Database Settings';
$txt['install_step_databasechange'] = 'Database Population';
$txt['install_step_admin'] = 'Admin Account';
$txt['install_step_delete'] = 'Finalize Install';

$txt['wedge_installer'] = 'Wedge Installer';
$txt['installer_language'] = 'Language';
$txt['installer_language_set'] = 'Set';
$txt['congratulations'] = 'Congratulations, the installation process is complete!';
$txt['congratulations_help'] = 'If at any time you need support, or Wedge fails to work properly, please remember that <a href="http://wedge.org/" target="_blank">help is available</a> if you need it.';
$txt['still_writable'] = 'Your installation directory is still writable. It\'s a good idea to chmod it so that it is not writable for security reasons.';
$txt['delete_installer'] = 'Click here to delete this install.php file now.';
$txt['delete_installer_maybe'] = '<em>(Doesn\'t work on all servers!)</em>';
$txt['go_to_your_forum'] = 'Now you can see <a href="%1$s">your newly installed forum</a> and begin to use it. You should first make sure you are logged in, after which you will be able to access the administration center.';
$txt['good_luck'] = 'Good luck!<br>The Wedge Team.';

$txt['install_welcome'] = 'Welcome';
$txt['install_welcome_desc'] = 'Welcome to Wedge. This script will guide you through the process for installing %1$s. We\'ll gather a few details about your forum over the next few steps, and after a couple of minutes your forum will be ready for use.';
$txt['install_all_lovely'] = 'We\'ve completed some initial tests on your server and everything appears to be in order. Simply click the &quot;Continue&quot; button below to get started.';

$txt['user_refresh_install'] = 'Forum Refreshed';
$txt['user_refresh_install_desc'] = 'While installing, the installer found that (with the details you provided) one or more of the tables this installer might create already existed.<br>Any missing tables in your installation have been recreated with the default data, but no data was deleted from existing tables.';

$txt['default_topic_subject'] = 'Welcome to Wedge!';
$txt['default_topic_message'] = 'Welcome to the Wedge forum software!<br><br>We hope you enjoy using your forum.&nbsp; If you have any problems, please feel free to <a href="http://wedge.org/">ask us for assistance</a>.<br><br>Thanks!<br>The Wedge Team.';
$txt['default_board_name'] = 'General Discussion';
$txt['default_board_description'] = 'Feel free to talk about anything and everything in this board.';
$txt['default_board_url'] = 'general';
$txt['default_recycling_board_name'] = 'Recycle Bin';
$txt['default_recycling_board_description'] = 'Posts and topics that get deleted end up here.';
$txt['default_recycling_board_url'] = 'recycle';
$txt['default_category_name'] = 'General Category';
$txt['default_news'] = 'Wedge - Just Installed!';
$txt['default_reserved_names'] = 'Admin\nWebmaster\nGuest\nroot';
$txt['default_smileyset_name'] = 'Alienine\'s Set';
$txt['default_aaron_smileyset_name'] = 'Aaron\'s Set';

$txt['default_administrator_group'] = 'Administrator';
$txt['default_global_moderator_group'] = 'Global Moderator';
$txt['default_moderator_group'] = 'Moderator';
$txt['default_newbie_group'] = 'Newbie';
$txt['default_junior_group'] = 'Jr. Member';
$txt['default_full_group'] = 'Full Member';
$txt['default_senior_group'] = 'Sr. Member';
$txt['default_hero_group'] = 'Hero Member';

$txt['default_smiley_smiley'] = 'Smile';
$txt['default_wink_smiley'] = 'Wink';
$txt['default_cheesy_smiley'] = 'Cheesy';
$txt['default_grin_smiley'] = 'Grin';
$txt['default_angry_smiley'] = 'Angry';
$txt['default_sad_smiley'] = 'Sad';
$txt['default_shocked_smiley'] = 'Shocked';
$txt['default_cool_smiley'] = 'Cool!';
$txt['default_huh_smiley'] = 'Huh?';
$txt['default_roll_eyes_smiley'] = 'Roll eyes';
$txt['default_tongue_smiley'] = 'Tongue';
$txt['default_embarrassed_smiley'] = 'Embarrassed';
$txt['default_lips_sealed_smiley'] = 'Lips sealed';
$txt['default_undecided_smiley'] = 'Undecided';
$txt['default_kiss_smiley'] = 'Kiss';
$txt['default_cry_smiley'] = 'Cry';
$txt['default_evil_smiley'] = 'Evil';
$txt['default_azn_smiley'] = 'Asian smile';
$txt['default_afro_smiley'] = 'Afro';
$txt['default_laugh_smiley'] = 'Laugh';
$txt['default_police_smiley'] = 'Police';
$txt['default_angel_smiley'] = 'Angel';
$txt['default_edit_smiley'] = 'Edit';

$txt['error_message_click'] = 'Click here';
$txt['error_message_try_again'] = 'to try this step again.';
$txt['error_message_bad_try_again'] = 'to try installing anyway, but note that this is <em>strongly</em> discouraged.';

$txt['install_settings'] = 'Forum Settings';
$txt['install_settings_info'] = 'This page requires you to define a few key settings for your forum. Wedge has automatically detected key settings for you.';
$txt['install_settings_name'] = 'Forum name';
$txt['install_settings_name_info'] = 'This is the name of your forum, e.g. &quot;Wedge Community&quot;.';
$txt['install_settings_url'] = 'Forum URL';
$txt['install_settings_url_info'] = 'This is the URL to your forum, <strong>without the trailing slash! (/)</strong><br>In most cases, you can leave the default value in this box alone - it is usually right.';
$txt['install_settings_compress'] = 'Gzip Output';
$txt['install_settings_compress_title'] = 'Compress output to save bandwidth.';
// In this string, you can translate 'pass_string' to change what it says when the test passes. Ensure that the sentence is URL-encoded like below.
$txt['install_settings_compress_info'] = 'This feature does not work properly on all servers, but can save you a lot of bandwidth.<br>Click <a href="install.php?obgz=1&amp;pass_string=This%20feature%20is%20available!" onclick="return reqWin(this, 200);" target="_blank">here</a> to test it. If it says "<strong>This feature is available</strong>", you\'re good to go.';
$txt['install_settings_dbsession'] = 'Database Sessions';
$txt['install_settings_dbsession_title'] = 'Use the database for sessions instead of using files.';
$txt['install_settings_dbsession_info1'] = 'This feature is almost always for the best, as it makes sessions more dependable.';
$txt['install_settings_dbsession_info2'] = 'This feature is generally a good idea, but may not work properly on this server.';
$txt['install_settings_proceed'] = 'Proceed';

$txt['db_settings'] = 'Database Server Settings';
$txt['db_settings_info'] = 'These are the settings to use for your database server. If you don\'t know the values, you should ask your host what they are.';
$txt['db_settings_server'] = 'Server name';
$txt['db_settings_server_info'] = 'This is nearly always localhost - so if you don\'t know, try localhost.';
$txt['db_settings_username'] = 'Username';
$txt['db_settings_username_info'] = 'Fill in the username you need to connect to your database here.<br>If you don\'t know what it is, try the username of your FTP account, most of the time they are the same.';
$txt['db_settings_password'] = 'Password';
$txt['db_settings_password_info'] = 'Here, put the password you need to connect to your database.<br>If you don\'t know this, you should try the password to your FTP account.';
$txt['db_settings_database'] = 'Database name';
$txt['db_settings_database_info'] = 'Fill in the name of the database you want to use for Wedge to store its data in.';
$txt['db_settings_database_info_note'] = 'If this database does not exist, this installer will try to create it.';
$txt['db_settings_prefix'] = 'Table prefix';
$txt['db_settings_prefix_info'] = 'The prefix for every table in the database. <strong>Do not install two forums with the same prefix!</strong><br>This value allows for multiple installations in one database.';
$txt['db_populate'] = 'Populated Database';
$txt['db_populate_info'] = 'Your settings have now been saved and the database has been populated with all the data required to get your forum up and running. Summary of population:';
$txt['db_populate_info2'] = 'Click &quot;Continue&quot; to progress to the admin account creation page.';
$txt['db_populate_inserts'] = 'Inserted %1$d rows.';
$txt['db_populate_tables'] = 'Created %1$d tables.';
$txt['db_populate_insert_dups'] = 'Ignored %1$d duplicated inserts.';
$txt['db_populate_table_dups'] = 'Ignored %1$d duplicated tables.';

$txt['user_settings'] = 'Create Your Account';
$txt['user_settings_info'] = 'The installer will now create a new administrator account for you.';
$txt['user_settings_username'] = 'Your username';
$txt['user_settings_username_info'] = 'Choose the name you want to login with.';
$txt['user_settings_password'] = 'Password';
$txt['user_settings_password_info'] = 'Fill in your preferred password here, and remember it well!';
$txt['user_settings_again'] = 'Password';
$txt['user_settings_again_info'] = '(just for verification.)';
$txt['user_settings_email'] = 'Email Address';
$txt['user_settings_email_info'] = 'Provide your email address as well. <strong>This must be a valid email address.</strong>';
$txt['user_settings_database'] = 'Database Password';
$txt['user_settings_database_info'] = 'The installer requires that you supply the database password to create an administrator account, for security reasons.';

$txt['ftp_checking_writable'] = 'Checking Files are Writable';
$txt['ftp_setup'] = 'FTP Connection Information';
$txt['ftp_setup_info'] = 'This installer can connect via FTP to fix the files that need to be writable and are not. If this doesn\'t work for you, you will have to go in manually and make the files writable. Please note that this doesn\'t support SSL right now.';
$txt['ftp_server'] = 'Server';
$txt['ftp_server_info'] = 'This should be the server and port for your FTP server.';
$txt['ftp_port'] = 'Port';
$txt['ftp_username'] = 'Username';
$txt['ftp_username_info'] = 'The username to login with. <em>This will not be saved anywhere.</em>';
$txt['ftp_password'] = 'Password';
$txt['ftp_password_info'] = 'The password to login with. <em>This will not be saved anywhere.</em>';
$txt['ftp_path'] = 'Install Path';
$txt['ftp_path_info'] = 'This is the <em>relative</em> path you use in your FTP server.';
$txt['ftp_path_found_info'] = 'The path in the box above was automatically detected.';
$txt['ftp_connect'] = 'Connect';
$txt['ftp_setup_why'] = 'What is this step for?';
$txt['ftp_setup_why_info'] = 'Some files need to be writable for Wedge to work properly. This step allows you to let the installer make them writable for you. However, in some cases it won\'t work - in that case, please make the following files 777 (writable, 755 on some hosts):';
$txt['ftp_setup_again'] = 'to test if these files are writable again.';

$txt['error_php_too_low'] = 'Warning! You do not appear to have a version of PHP installed on your webserver that meets Wedge\'s <strong>minimum installations requirements</strong>.<br>If you are not the host, you will need to ask your host to upgrade, or use a different host - otherwise, please upgrade PHP to a recent version.<br><br>If you know for a fact that your PHP version is high enough you may continue, although this is strongly discouraged.';
$txt['error_missing_files'] = 'Unable to find crucial installation files in the directory of this script!<br><br>Please make sure you uploaded the entire installation pack, including the sql file, and then try again.';
$txt['error_session_save_path'] = 'Please inform your host that the <strong>session.save_path specified in php.ini</strong> is not valid! It needs to be changed to a directory that <strong>exists</strong>, and is <strong>writable</strong> by the user PHP is running under.<br>';
$txt['error_no_gd_library'] = 'The version of PHP configured on your webserver requires the GD2 library to be installed, for resizing attachments and avatars, and for CAPTCHA support.<br><br>You will need to install GD2 support in PHP in order to continue. If you are not the host, you will need to ask your host to upgrade, or use a different host - otherwise, please install and/or configure the GD2 library for use with PHP.';
$txt['error_no_sxml'] = 'Your server does not have SimpleXML enabled, and several key functions for Wedge require this PHP extension to be available and enabled.<br><br>You will need to install SimpleXML support in order to continue. If you are not the host, you will need to ask your host to upgrade, or use a different host - otherwise, please install and/or configure SimpleXML for use with PHP.';
$txt['error_windows_chmod'] = 'You\'re on a Windows server, and some crucial files are not writable. Please ask your host to give <strong>write permissions</strong> to the user PHP is running under for the files in your Wedge installation. The following files or directories need to be writable:';
$txt['error_ftp_no_connect'] = 'Unable to connect to FTP server with this combination of details.';
$txt['error_db_file'] = 'Cannot find database source script! Please check file %1$s is within your forum source directory.';
$txt['error_db_connect'] = 'Cannot connect to the database server with the supplied data.<br><br>If you are not sure about what to type in, please contact your host.';
$txt['error_db_too_low'] = 'The version of your database server (%1$s) is very old, and does not meet Wedge\'s minimum requirements.<br><br>Please ask your host to either upgrade it or supply a new one, and if they won\'t, please try a different host.';
$txt['error_db_database'] = 'The installer was unable to access the &quot;<em>%1$s</em>&quot; database. With some hosts, you have to create the database in your administration panel before Wedge can use it. Some also add prefixes - like your username - to your database names.';
$txt['error_db_queries'] = 'Some of the queries were not executed properly. This could be caused by an unsupported (development or old) version of your database software.<br><br>Technical information about the queries:';
$txt['error_db_queries_line'] = 'Line #';
$txt['error_db_missing'] = 'The installer was unable to detect any database support in PHP. Please ask your host to ensure that PHP was compiled with the desired database, or that the proper extension (<strong>mysqli</strong>, MySQL Improved) is being loaded.';
$txt['error_db_script_missing'] = 'The installer could not find any install script files for the detected databases. Please check you have uploaded the necessary install script files to your forum directory, for example &quot;%1$s&quot;';
$txt['error_session_missing'] = 'The installer was unable to detect sessions support in your server\'s installation of PHP. Please ask your host to ensure that PHP was compiled with session support (in fact, it has to be explicitly compiled without it.)';
$txt['error_user_settings_again_match'] = 'You typed in two completely different passwords!';
$txt['error_user_settings_no_password'] = 'Your password must be at least four characters long.';
$txt['error_user_settings_taken'] = 'Sorry, a member is already registered with that username and/or email address.<br><br>A new account has not been created.';
$txt['error_user_settings_query'] = 'A database error occurred while trying to create an administrator. This error was:';
$txt['error_subs_missing'] = 'Unable to find the core/app/Subs.php file. Please make sure it was uploaded properly, and then try again.';
$txt['error_db_alter_priv'] = 'The database account you specified does not have permission to ALTER, CREATE, and/or DROP tables in the database; this is necessary for Wedge to function properly.';
$txt['error_mod_security'] = 'The installer has detected the mod_security module is installed on your web server. Mod_security will block submitted forms even before Wedge gets a say in anything. Wedge has a built-in security scanner that will work more effectively than mod_security and that won\'t block submitted forms.';
$txt['error_mod_security_no_write'] = 'The installer has detected the mod_security module is installed on your web server. Mod_security will block submitted forms even before Wedge gets a say in anything. Wedge has a built-in security scanner that will work more effectively than mod_security and that won\'t block submitted forms.<br><br>Alternatively, you may wish to use your FTP client to chmod .htaccess in the forum directory to be writable (777), and then refresh this page.';
$txt['error_valid_email_needed'] = 'You have not entered a valid email address.';
$txt['error_already_installed'] = 'The installer has detected that you already have Wedge installed. Continuing with installation <strong>would result in the loss or corruption of existing data</strong>.<br><br>If you don\'t mind and wish to start anew, please delete the existing database tables, remove Settings.php from your root folder, and try again.';
$txt['error_warning_notice'] = 'Warning!';
$txt['error_script_outdated'] = 'This install script is out of date! The current version of Wedge is %1$s but this install script is for %2$s.<br><br>
	It is recommended that you visit the <a href="http://wedge.org/">Wedge</a> website to ensure you are installing the latest version.';
$txt['error_invalid_characters_username'] = 'Invalid character used in Username.';
$txt['error_username_too_long'] = 'Username must be less than 25 characters long.';
$txt['error_username_left_empty'] = 'Username field was left empty.';
$txt['error_db_filename_exists'] = 'The database that you are trying to create exists. Please delete the current database file or enter another name.';
$txt['error_db_prefix_invalid'] = 'The prefix that you entered is invalid. Please stick to alphanumeric characters.';

$txt['upgrade_upgrade_utility'] = 'Wedge Upgrade Utility';
$txt['upgrade_warning'] = 'Warning!';
$txt['upgrade_critical_error'] = 'Critical Error!';
$txt['upgrade_continue'] = 'Continue';
$txt['upgrade_skip'] = 'Skip';
$txt['upgrade_note'] = 'Note!';
$txt['upgrade_step'] = 'Step';
$txt['upgrade_steps'] = 'Steps';
$txt['upgrade_progress'] = 'Progress';
$txt['upgrade_overall_progress'] = 'Overall Progress';
$txt['upgrade_step_progress'] = 'Step Progress';
$txt['upgrade_time_elapsed'] = 'Time Elapsed';
$txt['upgrade_time_mins'] = 'mins';
$txt['upgrade_time_secs'] = 'seconds';

$txt['upgrade_incomplete'] = 'Incomplete';
$txt['upgrade_not_quite_done'] = 'Not quite done yet!';
$txt['upgrade_paused_overload'] = 'This upgrade has been paused to avoid overloading your server. Don\'t worry, nothing\'s wrong - simply click the <label for="contbutt">continue button</label> below to keep going.';

$txt['upgrade_ready_proceed'] = 'Thank you for choosing to upgrade to Wedge %1$s. All files appear to be in place, and we\'re ready to proceed.';
$txt['upgrade_error_script_js'] = 'The upgrade script cannot find script.js or it is out of date. Make sure your theme paths are correct. You can download a setting checker tool from the community.';
$txt['upgrade_warning_out_of_date'] = 'This upgrade script is out of date! The current version of Wedge is <em id="wedgeVersion" class="nowrap">??</em> but this upgrade script is for <em id="yourVersion" style="white-space: nowrap">%1$s</em>.<br><br>It is recommended that you visit the <a href="http://wedge.org/">Wedge</a> website to ensure you are upgrading to the latest version.';
