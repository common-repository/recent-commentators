<?php
/*
Plugin Name: Recent Commentators Widget
Plugin URI: http://www.vjcatkick.com/?page_id=4839
Description: Display recent commentators with its comment link.
Version: 0.0.4
Author: V.J.Catkick
Author URI: http://www.vjcatkick.com/
*/

/*
License: GPL
Compatibility: WordPress 2.6 with Widget-plugin.

Installation:
Place the widget_single_photo folder in your /wp-content/plugins/ directory
and activate through the administration panel, and then go to the widget panel and
drag it to where you would like to have it!
*/

/*  Copyright V.J.Catkick - http://www.vjcatkick.com/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/* Changelog
* Dec 29 2008 - v0.0.1
- Initial release
* Jan 06 2009 - v0.0.2
- compatibility fix
* Jan 11 2009 - v0.0.3
- added icon only mode, Gravatar link, bug fix
* Jan 29 2009 - v0.0.4
- support WordPress default icon system

*/


function widget_recent_commentators_init() {
	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_recent_commentators( $args ) {
		extract($args);

		$options = get_option('widget_recent_commentators');
		$recent_commentators_src_title = $options['recent_commentators_src_title'];
		$recent_commentators_max_commentators =  (int)$options['recent_commentators_max_commentators'];
		$recent_commentators_gravator =  (boolean)$options['recent_commentators_gravator'];
		$recent_commentators_gravator_only = (boolean)$options['recent_commentators_gravator_only'];
		$recent_commentators_gravator_size = (int)$options['recent_commentators_gravator_size'];
		$recent_commentators_gravator_link = (boolean)$options['recent_commentators_gravator_link'];

		$output = '<div id="widget_recent_commentators"><ul>';

		// section main logic from here 

	$maxCommentator = intval( $recent_commentators_max_commentators );
	$dispGravatar = $recent_commentators_gravator;
	$dispnameJumps2Site = false;
	global $wpdb;

	$myUsers = $wpdb->get_results( "
		SELECT * 
		FROM $wpdb->users 
	", ARRAY_A );

	$queryStr = "
		SELECT * 
		FROM $wpdb->comments 
		WHERE $wpdb->comments.comment_approved = '1'
	";

	foreach( $myUsers as $myUser ) {
		$queryStr .= "AND $wpdb->comments.comment_author != '";
		$queryStr .= $myUser[ 'display_name' ];
		$queryStr .= "' ";
	} /* foreach */

	$queryStr .= "
		AND $wpdb->comments.comment_type != 'trackback'
		AND $wpdb->comments.comment_type != 'pingback'
		ORDER BY $wpdb->comments.comment_date_gmt DESC 
	";

	$myResults = $wpdb->get_results( $queryStr, ARRAY_A );

	$theResults = array();
	foreach( $myResults as $myResult ) {
		$uniqf = true;
		for( $i=0; $i<count( $theResults ); $i++ ) {
			$tmpData = $theResults[$i];
			if( strcmp( $tmpData[ 'comment_author' ], $myResult[ 'comment_author' ] ) == 0 ) {
				$uniqf = false;
				break;
			} /* if */
		} /* for */
		if( $uniqf == true ) { $theResults[] = $myResult; } /* if */
		if( count( $theResults ) >= $maxCommentator ) break;
	} /* foreach */


if( $recent_commentators_gravator_only ) {
	function _rc_get_avatar( $emailaddr, $size ) {
		$avatar_default = get_option( 'avatar_default' );
		if ( empty($avatar_default) )
			$default = 'mystery';
		else
			$default = $avatar_default;

		if ( 'mystery' == $default )
			$default = "http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}";
		elseif ( 'blank' == $default )
			$default = includes_url('images/blank.gif');
		elseif ( !empty($emailaddr) && 'gravatar_default' == $default )
			$default = '';
		elseif ( 'gravatar_default' == $default )
			$default = "http://www.gravatar.com/avatar/s={$size}";
		elseif ( empty($emailaddr) )
			$default = "http://www.gravatar.com/avatar/?d=$default&amp;s={$size}";
		elseif ( strpos($default, 'http://') === 0 )
			$default = add_query_arg( 's', $size, $default );

		if ( !empty($emailaddr) ) {
			$out = 'http://www.gravatar.com/avatar/';
			$out .= md5( strtolower( $emailaddr ) );
			$out .= '?s='.$size;
			$out .= '&amp;d=' . urlencode( $default );
			$rating = get_option('avatar_rating');
			if ( !empty( $rating ) )
				$out .= "&amp;r={$rating}";
			return $out;
		} else {
			return $default;
		} /* if else */
//		return 'http://www.gravatar.com/avatar.php?gravatar_id=' . md5( $emailaddr ) . '&size=' . $size . '&default=';
	} /* _rc_get_avatar() */

	$output .= '<div class="recent_commentators_gravatar_box" style="text-align:center;">';
	foreach( $theResults as $myResult ) {

		if( $dispnameJumps2Site ) {
			if( $myResult[ 'comment_author_url' ] ) $output .= '<a href="' . $myResult[ 'comment_author_url' ]  . '" target="_blank">';
			$output .= '<img src="' . _rc_get_avatar(  $myResult[ 'comment_author_email' ], $recent_commentators_gravator_size ) . '" style="margin-bottom:2px; margin-right: 2px;" />';
			if( $myResult[ 'comment_author_url' ] ) $output .= '</a>';
		}else{
			$dstPost = wp_get_single_post( $myResult[ 'comment_post_ID' ], ARRAY_A );
			$mouseon_msg = $dstPost[ 'post_title' ] . ': ' . $myResult[ 'comment_author' ];
			$output .= '<a href="' . $dstPost[ 'guid' ]  .'#comment-'. $myResult[ 'comment_ID' ] . '" >';
			$output .= '<img src="' . _rc_get_avatar(  $myResult[ 'comment_author_email' ], $recent_commentators_gravator_size ) . '" style="margin-bottom:2px; margin-right: 2px;" alt="' . $mouseon_msg . '" title="' . $mouseon_msg . '"/>';
			$output .= '</a>';
		} /* ifelse */

	} /* foreach */
	$output .= '</div>';
}else{
	foreach( $theResults as $myResult ) {
		$output .= '<li>';

		if( $dispGravatar ) {
			$output .= get_avatar(  $myResult[ 'comment_author_email' ], $recent_commentators_gravator_size );
			$output .= '&nbsp;';
		} /* if */

		if( $dispnameJumps2Site ) {
			if( $myResult[ 'comment_author_url' ] ) $output .= '<a href="' . $myResult[ 'comment_author_url' ]  . '" target="_blank">';
			$output .= $myResult[ 'comment_author' ];
			if( $myResult[ 'comment_author_url' ] ) $output .= '</a>';
		}else{
			$dstPost = wp_get_single_post( $myResult[ 'comment_post_ID' ], ARRAY_A );
			$output .= '<a href="' . $dstPost[ 'guid' ]  .'#comment-'. $myResult[ 'comment_ID' ] . '" >';
			$output .= $myResult[ 'comment_author' ];
			$output .= '</a>';
		} /* ifelse */

		$output .= '</li>';
	} /* foreach */
} /* if else */
if( $recent_commentators_gravator_link ) {
	$output .= '<div class="recent_commentators_link" style="font-size:0.8em; text-align:center;">';
	$output .= '- register Gravatar icon <a href="http://en.gravatar.com/" target="_blank">here</a> -';
	$output .= '</div>';
} /* if */

		// These lines generate the output
		$output .= '</ul></div>';

		echo $before_widget . $before_title . $recent_commentators_src_title . $after_title;
		echo $output;
		echo $after_widget;
	} /* widget_recent_commentators() */

	function widget_recent_commentators_control() {
		$options = $newoptions = get_option('widget_recent_commentators');
		if ( $_POST["recent_commentators_submit"] ) {
			$newoptions['recent_commentators_src_title'] = strip_tags(stripslashes($_POST["recent_commentators_src_title"]));
			$newoptions['recent_commentators_max_commentators'] = (int) $_POST["recent_commentators_max_commentators"];
			$newoptions['recent_commentators_gravator'] = (boolean) $_POST["recent_commentators_gravator"];
			$newoptions['recent_commentators_gravator_only'] = (boolean) $_POST["recent_commentators_gravator_only"];
			$newoptions['recent_commentators_gravator_size'] = (int) $_POST["recent_commentators_gravator_size"];
			$newoptions['recent_commentators_gravator_link'] = (boolean) $_POST["recent_commentators_gravator_link"];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_recent_commentators', $options);
		}

		// those are default value
		if ( !$options['recent_commentators_max_commentators'] ) $options['recent_commentators_max_commentators'] = 7;
		if ( !$options['recent_commentators_gravator_size'] ) $options['recent_commentators_gravator_size'] = 12;

		$recent_commentators_max_commentators = (int)$options['recent_commentators_max_commentators'];
		$recent_commentators_gravator = (boolean)$options['recent_commentators_gravator'];
		$recent_commentators_gravator_only = (boolean)$options['recent_commentators_gravator_only'];
		$recent_commentators_gravator_size = (int)$options['recent_commentators_gravator_size'];
		$recent_commentators_gravator_link = (boolean)$options['recent_commentators_gravator_link'];


		$recent_commentators_src_title = htmlspecialchars($options['recent_commentators_src_title'], ENT_QUOTES);
?>

	    <?php _e('Title:'); ?> <input style="width: 170px;" id="recent_commentators_src_title" name="recent_commentators_src_title" type="text" value="<?php echo $recent_commentators_src_title; ?>" /><br />

        <?php _e('Max Commentators:'); ?> <input style="width: 75px;" id="recent_commentators_max_commentators" name="recent_commentators_max_commentators" type="text" value="<?php echo $recent_commentators_max_commentators; ?>" /><br />

        <input id="recent_commentators_gravator" name="recent_commentators_gravator" type="checkbox" value="1" <?php if( $recent_commentators_gravator ) echo 'checked';?>/> <?php _e('Use Gravator icon'); ?><br />
        &nbsp;&nbsp;<input id="recent_commentators_gravator_only" name="recent_commentators_gravator_only" type="checkbox" value="1" <?php if( $recent_commentators_gravator_only ) echo 'checked';?>/> <?php _e(' Display Gravator icon only'); ?><br />
        <?php _e('Icon Size:'); ?> <input style="width: 75px;" id="recent_commentators_gravator_size" name="recent_commentators_gravator_size" type="text" value="<?php echo $recent_commentators_gravator_size; ?>" /><br /><br />
        <input id="recent_commentators_gravator_link" name="recent_commentators_gravator_link" type="checkbox" value="1" <?php if( $recent_commentators_gravator_link ) echo 'checked';?>/> <?php _e('Link to  Gravator'); ?><br />

  	    <input type="hidden" id="recent_commentators_submit" name="recent_commentators_submit" value="1" />

<?php
	} /* widget_recent_commentators_control() */

	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget('Recent Commentators', 'widget_recent_commentators');
	register_widget_control('Recent Commentators', 'widget_recent_commentators_control' );
} /* widget_recent_commentators_init() */

// Run our code later in case this loads prior to any required plugins.
add_action('plugins_loaded', 'widget_recent_commentators_init');

?>