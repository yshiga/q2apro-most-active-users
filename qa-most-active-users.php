<?php

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
		/* Settings */
		$doWeek = false;  				// here you can switch the interval: false - current month and true - current week
		$maxusers = 5; 					// max users to display 
		$adminName = "echteinfachtv";	// if you want to ignore the admin, define his name here 
		$localcode = "de_DE"; 			// displays the month name in your defined language, e.g. en_US
		$langActUsers = "Aktivste Mitglieder";	// your language string for 'most active users'
		$langThisWeek = "diese Woche";			// your language string for 'this week'
		
		/* 	CSS: you can style the most active user box by css: #mostactiveusers
			define height and width of images in: #mostactiveusers img
			
			For instance, for my template I used the following (add these lines to qa-styles.css): 
			#mostactiveusers { padding-top:30px; }
			#mostactiveusers img { width:30px; height:30px; border:1px solid #CCCCCC; vertical-align:middle; margin-bottom:5px; }
			#mostactiveusers ol { margin:0; padding-left:20px; }
		*/

		
		/*  Events that should be regarded for activity points were: badge_awarded, q_post, a_post, c_post, in_a_question, in_c_question, in_c_answer, q_vote_up, in_q_vote_up, in_a_vote_up.
			Problem: in event_log e.g. "in_q_vote_up" registers the user who RECEIVED the vote, not the one how voted!
			So we can only take the basic events that originate from the user: q_post, a_post, c_post
		*/
		$activityEvents = array("q_post", "a_post", "c_post");
		$users = array();
		$events = array(); 
		$avatarImages = array();
		
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
		
		/* get avatar images */
		foreach ($users as $username => $val) {
			$user = qa_db_select_with_pending( qa_db_user_account_selectspec($username, false) );
			$avatarImages[$username] = qa_get_user_avatar_html($user['flags'], $user['email'], $user['handle'], $user['avatarblobid'], $user['avatarwidth'], $user['avatarheight'], qa_opt('avatar_users_size'), true);
        }

		// initiate output string
		$topusers = "<ol>";
		// display maximum of maxusers
		$nrUsers = 0;
		foreach ($users as $key => $val) {
			$nrUsers++;
			// $topusers .= "$key ($val points)<br />";
			$topusers .= "<li>".$avatarImages[$key]." ".qa_get_one_user_html($key, false).'</li>';
			// max users to display 
			if($nrUsers>=$maxusers) break;
		}
		$topusers .= "</ol>";
		
		$themeobject->output('<div id="mostactiveusers">');
		if($doWeek) {
			$themeobject->output('<div class="qa-nav-cat-list qa-nav-cat-link">'.$langActUsers.'<br />'.$langThisWeek.':</div>'); // todo: qa_lang_html('misc/most_active_users')
		}
		else {
			// get month name
			setlocale (LC_TIME, $localcode); 
			$monthName = strftime("%B %G", strtotime( date('F')) );
			$themeobject->output('<div class="qa-nav-cat-list qa-nav-cat-link">'.$langActUsers.'<br />('.$monthName.'):</div>'); 
		}
		$themeobject->output( $topusers );
		$themeobject->output('</div>');
	}

}

/*
	Omit PHP closing tag to help avoid accidental output
*/