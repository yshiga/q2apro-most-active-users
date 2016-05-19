<?php

/*
	Question2Answer Plugin: Most active users (per time interval)
	License: http://www.gnu.org/licenses/gpl.html
*/

class qa_most_active_users {

	function allow_template($template)
	{
		$allow=false;

		switch ($template)
		{
			case 'activity':
			case 'qa':
			case 'questions':
			case 'hot':
			case 'ask':
			case 'categories':
			case 'question':
			case 'tag':
			case 'tags':
			case 'unanswered':
			case 'user':
			case 'users':
			case 'search':
			case 'admin':
				$allow=true;
				break;
		}

		return $allow;
	}

	function allow_region($region)
	{
		$allow=false;

		switch ($region)
		{
			case 'main':
			case 'side':
				$allow=true;
				break;
			case 'full':
				break;
		}

		return $allow;
	}

	function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
	{
		/* SETTINGS */
		$doWeek = false;  					// here you can switch the interval: false - current month and true - current week
		$displayMonthName = false;			// displays the name of the current month in widget headline
		$maxusers = 10; 						// max users to display
		$adminName = "theAdminUsername";	// if you want to ignore the admin, define his name here
		$avatarSize = qa_opt('avatar_users_size'); // if you want the avatars in another size, define a value here, e.g. 40
		$showActivityPoints = true; 		// show activity points behind username
		$showTotalPoints = false; 			// show total points behind activity points
		$creditDeveloper = true;			// say thank you to the developer, this adds a hidden link to the developer's forum

		/* TRANSFER LANGUAGE STRINGS */
		$localcode = qa_lang_html('qa_most_active_users_lang/localcode');	// displays the month name in your defined language, e.g. en_US
		$langActUsers = qa_lang_html('qa_most_active_users_lang/mostActiveUsers');
		$langInterval = $doWeek ? qa_lang_html('qa_most_active_users_lang/this_week') : qa_lang_html('qa_most_active_users_lang/this_month');
		$langPoints = qa_lang_html('qa_most_active_users_lang/points');

		/*  Events that should be regarded for activity points were: badge_awarded, q_post, a_post, c_post, in_a_question, in_c_question, in_c_answer, q_vote_up, in_q_vote_up, in_a_vote_up.
			Problem: in event_log e.g. "in_q_vote_up" registers the user who RECEIVED the vote, not the one how voted!
			So we only take the basic events that originate from the user: q_post, a_post, c_post */
		$activityEvents = array("q_post", "a_post", "c_post");
		$users = array();
		$events = array();
		$avatarImages = array();
		$totalPoints = array();

		// week or month query, 'handle' holds each username, ignore anonym users with handle = NULL
		if($doWeek) {
			// get week range from current date, week starts sunday
			$ts = strtotime( date('Y-m-d') );
			$start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
			$weekstart = date('Y-m-d', $start);
			$weekend = date('Y-m-d', strtotime('next saturday', $start));

			$events = qa_db_query_sub("SELECT handle,event from `^eventlog`
										WHERE `datetime`
										BETWEEN $ AND $
										AND `handle`!='NULL'", $weekstart, $weekend);
		}
		else {
			$events = qa_db_query_sub("SELECT handle,event from `^eventlog`
										WHERE YEAR(`datetime`) = YEAR(CURDATE())
										AND MONTH(`datetime`) = MONTH(CURDATE())
										AND `handle`!='NULL'");
		}

		while ( ($event=qa_db_read_one_assoc($events,true)) !== null ) {
			// collect the activity points for each user, ignore admin user
			if(in_array($event['event'], $activityEvents) && $event['handle']!=$adminName) {
				// if user/points do not exist in array yet, create entry
				if(empty($users[$event['handle']])) {
					$users[$event['handle']] = 0;
					$avatarImages[$event['handle']] = ""; // needed for asigning avatar images below
				}
				// count 1 point for each defined activity
				$users[$event['handle']]++;
			}
		}

		// sort users, highest points first
		arsort($users);

		// get avatar images
		foreach ($users as $username => $val) {
			$user = qa_db_select_with_pending( qa_db_user_account_selectspec($username, false) );
			$avatarImages[$username] = qa_get_user_avatar_html($user['flags'], $user['email'], $user['handle'], $user['avatarblobid'], $user['avatarwidth'], $user['avatarheight'], $avatarSize, true);
			$totalPoints[$username] = $user['points'];
        }

		// initiate output string
		$topusers = "<ol>";
		// display maximum of maxusers
		$nrUsers = 0;
		foreach ($users as $key => $val) {
			$nrUsers++;
			$pointString = $showActivityPoints ? '<span class="mau_points"> - '.$val.($showTotalPoints ? '/'.$totalPoints[$key] : '').' '.$langPoints.'</span>': '';
			$topusers .= '<li>'.$avatarImages[$key].' '.qa_get_one_user_html($key, false) . $pointString . '</li>';
			// max users to display
			if($nrUsers>=$maxusers) break;
		}
		$topusers .= "</ol>";

		if($displayMonthName) {
			setlocale (LC_TIME, $localcode);
			$monthName = strftime("%B %G", strtotime( date('F')) );
		}

		$themeobject->output('<div id="mostactiveusers">');
		$themeobject->output('<h2 class="qa-widget-side qa-nav-cat qa-nav-cat-list">'.$langActUsers.'<br />'.(!$doWeek && $displayMonthName ? $monthName : $langInterval).':</h2>'); // todo:
		$themeobject->output( $topusers );
		// as said, this is one chance to say thank you to the developer
		if($creditDeveloper) {
			$themeobject->output("<a style='display:none' href='http://www.gute-mathe-fragen.de/'>Gute Mathe-Fragen - Bestes Mathe-Forum</a>");
		}
		$themeobject->output('</div>');

		/* 	Tip: you can style the most active user box by css selector: #mostactiveusers
			Example below:
		*/

		$css = <<<EOT
<style type="text/css">
#mostactiveusers {
}
#mostactiveusers img {
	border:1px solid #CCC;
	vertical-align:middle;
	margin-bottom:5px;
}
#mostactiveusers ol {
	margin:0;
	padding-left:20px;
}
#mostactiveusers .qa-nav-cat-list {
	margin:5px 0 10px 0;
}
//.qa-widget-side h2, .qa-nav-cat h2
</style>
EOT;
		$themeobject->output($css);

	}

}

/*
	Omit PHP closing tag to help avoid accidental output
*/
