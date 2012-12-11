<?php
/**
 * @package Simpul
 */
/*
Plugin Name: Simpul Tweets by Esotech
Plugin URI: http://www.esotech.org
Description: This plugin is designed to access a twitter feed and display it in a Wordpress Widget.
Version: 1.8.3
Author: Alexander Conroy
Author URI: http://www.esotech.org/people/alexander-conroy/
License: This code is released under the GPL licence version 3 or later, available here http://www.gnu.org/licenses/gpl.txt
*/


class SimpulTweets extends WP_Widget 
{
	# The ID of the twitter feed we are trying to read	
	public function __construct()
	{
		$widget_ops = array('classname' => 'simpul-tweets', 
							'description' => 'A Simpul Twitter Widget' );
							
		parent::__construct('simpul_tweets', // Base ID
							'Twitter', // Name
							$widget_ops // Args  
							);
							
	}
	public function widget( $args, $instance )
	{
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		if($instance['title_element']):
			$before_title = '<' . $instance['title_element'] . ' class="widgettitle">';
			$after_title = '</' . $instance['title_element'] . '>';
		else:
			$before_title = '<h3 class="widgettitle">';
			$after_title = '</h3>';
		endif;
		
		if ( !empty( $instance['title']) ) { echo $before_title . $instance['title']. $after_title; };
		
		// Solution for caching.
		if($instance['cache_enabled']):
			if(!$instance['cache'] || current_time('timestamp') > strtotime($instance['cache_interval'] . ' hours', $instance['last_cache_time'])):
				$instance['cache'] = self::twitterStatus( $instance['account'], $instance );
				$instance['last_cache_time'] = current_time('timestamp');
			endif;
			
			self::updateWidgetArray( $args, $instance );
			
		else:
			
			unset($instance['cache'], $instance['last_cache_time']);
			self::updateWidgetArray( $args, $instance );
			
		endif;
		if( $instance['tweet_element'] == 'li'): 
		echo '<ul class="' . $instance['tweet_class'] . '">';
		else:
			echo '<div class="' . $instance['tweet_class'] . '">';
		endif;
		
		echo self::getTweets( $instance );
		
		if( $instance['tweet_element'] == 'li'): 
			echo '</ul>';
		else:
			echo '</div>';
		endif;
		
		echo $after_widget;
		
	}	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance 							= $old_instance;
		$instance['title'] 					= strip_tags($new_instance['title']);
		$instance['title_element'] 			= strip_tags($new_instance['title_element']);
		$instance['account'] 				= str_replace("@", "", strip_tags($new_instance['account'] ) );
		$instance['number'] 				= strip_tags($new_instance['number']);
		$instance['tweet_class'] 			= strip_tags($new_instance['tweet_class']);
		$instance['tweet_element'] 			= strip_tags($new_instance['tweet_element']);
		$instance['tweet_element_link'] 	= strip_tags($new_instance['tweet_element_link']);
		$instance['tweet_date'] 			= strip_tags($new_instance['tweet_date']);
		$instance['tweet_date_format']		= strip_tags($new_instance['tweet_date_format']);
		$instance['tweet_date_link'] 		= strip_tags($new_instance['tweet_date_link']);
		
		$instance['cache_enabled']			= strip_tags($new_instance['cache_enabled']);
		$instance['cache_interval']			= strip_tags($new_instance['cache_interval']);
		
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$instance 					= wp_parse_args( (array) $instance, array( 'title' => '', 'account' => 'esotech', 'number' => '3' ) );
		$title 						= strip_tags($instance['title']);
		$title_element				= strip_tags($instance['title_element']);
		$account 					= strip_tags($instance['account']);
		$number 					= strip_tags($instance['number']);
		$tweet_class				= strip_tags($instance['tweet_class']);
		$tweet_element				= strip_tags($instance['tweet_element']);
		$tweet_element_link			= strip_tags($instance['tweet_element_link']);
		$tweet_date					= strip_tags($instance['tweet_date']);
		$tweet_date_format			= strip_tags($instance['tweet_date_format']);
		$tweet_date_link			= strip_tags($instance['tweet_date_link']);
		$cache_enabled 				= strip_tags($instance['cache_enabled']);
		$cache_interval 			= strip_tags($instance['cache_interval']);
		?>

		<?php
		echo self::formatField($this->get_field_name('title'), $this->get_field_id('title'), $title, "Title" );
		echo self::formatField($this->get_field_name('title_element'), $this->get_field_id('title_element'), $title_element, "Title Element(default h3)" );
		echo self::formatField($this->get_field_name('account'), $this->get_field_id('account'), $account, "Twitter Account Name" );
		echo self::formatField($this->get_field_name('number'), $this->get_field_id('number'), $number, "Amount of tweets to be displayed: " );
		echo self::formatField($this->get_field_name('tweet_class'), $this->get_field_id('tweet_class'), $tweet_class, "Tweet Container Class" );
		echo self::formatField($this->get_field_name('tweet_element'), $this->get_field_id('tweet_element'), $tweet_element, "Tweet Element(default p)" );
		echo self::formatField($this->get_field_name('tweet_element_link'), $this->get_field_id('tweet_element_link'), $tweet_element_link, "Link Element to Tweet", 'checkbox' );
		echo self::formatField($this->get_field_name('tweet_date'), $this->get_field_id('tweet_date'), $tweet_date, "Show Tweet Date", 'checkbox' );
		echo self::formatField($this->get_field_name('tweet_date_format'), $this->get_field_id('tweet_date_format'), $tweet_date_format, "Date Format" );
		echo self::formatField($this->get_field_name('tweet_date_link'), $this->get_field_id('tweet_date_link'), $tweet_date_link, "Link Tweet Date", 'checkbox' );
		echo "<h3>Cache Options</h3>";
		echo self::formatField($this->get_field_name('cache_enabled'), $this->get_field_id('cache_enabled'), $cache_enabled, "Use Cache?", 'checkbox' );
		echo self::formatField($this->get_field_name('cache_interval'), $this->get_field_id('cache_interval'),  $cache_interval, "Cache Interval (hours)" );
	}

	# -----------------------------------------------------------------------------#
	# End Standard Wordpress Widget Section
	# -----------------------------------------------------------------------------#
	
	# -----------------------------------------------------------------------------#
	# Get the twitter feed via CURL according to the Twitter ID
	# -----------------------------------------------------------------------------#
	public function twitterStatus($twitter_id, $instance = null)
	{
		$ch = curl_init();
		$header = array();
		$header[] = 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
		$header[] = 'Cache-Control: max-age=0';
		$header[] = 'Connection: keep-alive';
		$header[] = 'Keep-Alive: 300';
		$header[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
		$header[] = 'Accept-Language: en-us,en;q=0.5';
		$header[] = 'Pragma: ';
		//curl_setopt($c, CURLOPT_URL, "https://twitter.com/statuses/user_timeline/".$twitter_id.".json");
		curl_setopt($ch, CURLOPT_URL, "https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name=" . $twitter_id . "&count=" . $instance['number'] );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		$response = curl_exec($ch);
		$responseInfo = curl_getinfo($ch);
		curl_close($ch);

		return json_decode($response);
	}
	# -----------------------------------------------------------------------------#
	# This is the mthod we will call, which sets everything up and calls the
	# twitter_status method
	# -----------------------------------------------------------------------------#
	public function getTweets($instance)
	{
		$result = null;
		$i = null;

		$twitter_id = $instance['account'];
		$instance['number'] = $instance['number'] ? $instance['number'] : 20;
		$instance['tweet_date_format'] = $instance['tweet_date_format'] ? $instance['tweet_date_format'] : "Y-m-d H:i:s";
		$instance['tweet_element'] = $instance['tweet_element'] ? $instance['tweet_element'] : "p";
		
		# get the actual feed
		if($instance['cache_enabled']):
			$tweep = $instance['cache'];
		else:
			$tweep = self::twitterStatus($twitter_id, $instance);
		endif;
		//print_r($tweep);
	
		# Make sure we have something to work with
		if(!empty($tweep)):	
			foreach($tweep as $tweet):
				$tweet_link = "https://twitter.com/" . $twitter_id . "/status/" . $tweet->id_str;
				
				if( !empty( $instance['tweet_date'] ) ):	
					$tweet_date = date($instance['tweet_date_format'], strtotime($tweet->created_at . " -5 Hours"));
					if( !empty($instance['tweet_date_link'] ) ):
						$tweet_date ='<a href="' . $tweet_link . '" target="_blank">' . $tweet_date . '</a>';	
					endif;
				endif;  
				
				$content = $tweet->text;
				
				$content = self::textToLink($content);
				if($instance['tweet_element_link']):
					$link_start = '<a href="' . $tweet_link . '">';
					$link_end = '</a>';
				else:
					$link_start = '';
					$link_end = '';
				endif;
				$tweets .= '<' . $instance['tweet_element'] . '>' . $link_start . $content." - " . $tweet_date  . $link_end . '</' . $instance['tweet_element'] . '>';
				
			endforeach;
			return $tweets;
		endif;
	return false;
	}
	public function textToLink($text)
	{
		$text  = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)','<a href="\\1">\\1</a>', $text ); 
		$text  = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '\\1<a href="http://\\2">\\2</a>', $text ); 
		$text  = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})', '<a href="mailto:\\1">\\1</a>', $text );
		
		return $text;
	}
	public function formatField($field, $id, $value, $description, $type = "text", $args = array(), $options = array() )	{
		if($type == "text"):
			return '<p>
					<label for="' . $id . '">
						' . $description . ': 
						<input class="widefat" id="' . $id . '" name="' . $field. '" type="text" value="' . attribute_escape($value) . '" />
					</label>
					</p>';
		elseif($type == "checkbox"):
			if( $value ) $checked = "checked";
			return '<p>
					<label for="' . $field . '">
						
						<input id="' . $field. '" name="' . $field . '" type="checkbox" value="1" ' . $checked . ' /> ' . $description . ' 
					</label>
					</p>';
		elseif($type == "radio"):
			$radio = '<p>
					<label for="' . $field . '">' . $description . '<br />';
					foreach($options as $option):
						if( $value == $option ): $checked = "checked"; else: $checked = ""; endif;						
						$radio .= '<input id="' . $field. '" name="' . $field . '" type="radio" value="' . $option . '" ' . $checked . ' /> ' . SimpulEvents::getLabel($option) . '<br />';
					endforeach; 
			$radio .= '</label>
					</p>';
			return $radio;
		endif;
	}
	public function updateWidgetArray( $args, $instance ) {
		
		$widget_class = explode('-', $args['widget_id']);
		$widget_id = array_pop($widget_class);
		$widget_name = implode('-', $widget_class);
		$widget_array = get_option('widget_' . $widget_name);
		
		$widget_array[$widget_id] = $instance;
		update_option('widget_' . $widget_name, $widget_array);
		
	}
}
//Register the Widget
function simpul_tweets_widget() {
	register_widget( 'SimpulTweets' );
}
//Add Widget to wordpress
add_action( 'widgets_init', 'simpul_tweets_widget' );	
