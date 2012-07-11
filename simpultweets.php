<?php
/**
 * @package Simpul
 */
/*
Plugin Name: Simpul Tweets by Esotech
Plugin URI: http://www.esotech.org
Description: This plugin is designed to access a twitter feed and display it in a Wordpress Widget.
Version: 1.5
Author: Alexander Conroy
Author URI: http://www.esotech.org/people/alexander-conroy/
License: Commercial
*/


class SimpulTweets extends WP_Widget 
{
	# The ID of the twitter feed we are trying to read	
	public function __construct()
	{
		$widget_ops = array('classname' => 'simpul_twitter', 
							'description' => 'A Simpul Twitter Widget' );
							
		parent::__construct('simpul_tweets', // Base ID
							'Twitter', // Name
							$widget_ops // Args  
							);
							
	}
	public function widget( $args, $instance )
	{
		extract($args);
		$widget = '<li class="simpul-twitter widget widget_text">';
		if( $instance['title'] ):
			$widget .= '<h2 class="widgettitle">' . $instance['title'] . '</h2>';
		endif;
		
		// Solution for caching.
		if($instance['cache_enabled']):
		
			$widget_class = explode('-', $args['widget_id']);
			$widget_id = $widget_class[count($widget_class) - 1];
			$widget_array = get_option('widget_simpul_tweets');
			
			if(!$instance['cache'] || current_time('timestamp') > strtotime($instance['cache_interval'] . ' hours', $instance['last_cache_time'])):
				$instance['cache'] = self::getTweets( $instance['account'], $instance['number'] );;
				$instance['last_cache_time'] = current_time('timestamp');
			endif;
			
			$widget_array[$widget_id] = $instance;
			update_option('widget_simpul_facebook', $widget_array);
			
		endif;
		
		$widget .= self::getTweets( $instance['account'], $instance['number'] );
		$widget .= "</li>";
		
		echo $widget;
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
		$instance 				= $old_instance;
		$instance['title'] 		= strip_tags($new_instance['title']);
		$instance['account'] 	= strip_tags($new_instance['account']);
		$instance['number'] 	= strip_tags($new_instance['number']);
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
		$instance 	= wp_parse_args( (array) $instance, array( 'title' => '', 'account' => 'esotech', 'number' => '3' ) );
		$title 		= strip_tags($instance['title']);
		$account 	= strip_tags($instance['account']);
		$number 	= strip_tags($instance['number']);
		$cache_enabled 				= strip_tags($instance['cache_enabled']);
		$cache_interval 			= strip_tags($instance['cache_interval']);
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					Title: 
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('account'); ?>">
					Twitter account name: 
					<input class="widefat" id="<?php echo $this->get_field_id('account'); ?>" name="<?php echo $this->get_field_name('account'); ?>" type="text" value="<?php echo attribute_escape($account); ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>">
					Amount of tweets to be displayed: 
					<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo attribute_escape($number); ?>" />
				</label>
			</p>
		<?php
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
	public function twitterStatus($twitter_id)
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, "https://twitter.com/statuses/user_timeline/".$twitter_id.".json");
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($c, CURLOPT_TIMEOUT, 5);
		$response = curl_exec($c);
		$responseInfo = curl_getinfo($c);
		curl_close($c);

		return json_decode($response);
	}
	# -----------------------------------------------------------------------------#
	# This is the mthod we will call, which sets everything up and calls the
	# twitter_status method
	# -----------------------------------------------------------------------------#
	public function getTweets($twitter_id, $total_tweets = 20)
	{
		$result = null;
		$i = null;

		# get the actual feed
		if($instance['cache_enabled']):
			$tweep = $instance['cache'];
		else:
			$tweep = self::twitterStatus($twitter_id);
		endif;
		//print_r($tweep);
	
		# Make sure we have something to work with
		if(!empty($tweep))
		{
			
			$tweet_count = 1;
			foreach($tweep as $tweet)
			{
				
				if($tweet_count > $total_tweets)
					break;
				$tweet_count++;

				$tweet_link = "https://twitter.com/" . $twitter_id . "/status/" . $tweet->id;

				$tweet_date = date ("Y-m-d H:i:s", strtotime($tweet->created_at . " -5 Hours"));
				
				$tweet_link_date ='<a href="' . $tweet_link . '">' . $tweet_date . '</a>';  
				
				$content = $tweet->text;
				
				$content = self::textToLink($content);
					
				$tweets .= "<p>".$content." - " . $tweet_link_date . "</p>";
	
			}
			return $tweets;
		}
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
}
//Register the Widget
function simpul_tweets_widget() {
	register_widget( 'SimpulTweets' );
}
//Add Widget to wordpress
add_action( 'widgets_init', 'simpul_tweets_widget' );	
