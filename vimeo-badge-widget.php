<?php
/**************************************************************************
 * Plugin Name: Vimeo Badge Widget
 * Plugin URI: http://tylercraft.com/portfolio/vimeo-badge-widget/
 * Description: Displays a badge of recent vimeo videos. Can pull recent videos from a user, group, album or channel.
 * Version: 1.2
 * Stable tag: 1.2
 * Tested up to: 2.9
 * Author: Tyler Craft
 * Author URI: http://tylercraft.com
 *
**************************************************************************

Copyright (C) 2008 tylercraft.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/
 
/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'load_vimeo_badge_widget' );

/**
 * Register our widget.
 * 'Example_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function load_vimeo_badge_widget() {
	register_widget( 'Vimeo_Badge_Widget' );
}

/**
 * Vimeo_Badge_Widget class.
 *
 * @since 0.1
 */
class Vimeo_Badge_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Vimeo_Badge_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'vimeo-badge-widget', 'description' => __('Vimeo Badge', 'vimeobadgewidget') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 400, 'height' => 350, 'id_base' => 'vimeo-badge-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'vimeo-badge-widget', __('Vimeo Badge', 'vimeobadgewidget'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$vimeo_type = $instance['vimeo_type'];
		$vimeo_id = $instance['vimeo_id'];
		$num_of_videos = $instance['num_of_videos'];
		$thumbnail_size = $instance['thumbnail_size'];
		$use_default_styles = isset( $instance['use_default_styles'] ) ? $instance['use_default_styles'] : false;

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;
		
		
		$vimeo_call = 'http://vimeo.com/api/v2/'.$vimeo_id.'/videos.json';
		
		switch($vimeo_type){
			case 'album':
					$vimeo_call = 'http://vimeo.com/api/v2/album/'.$vimeo_id.'/videos.json';
				break;
			case 'group':
					$vimeo_call = 'http://vimeo.com/api/v2/group/'.$vimeo_id.'/videos.json';
				break;
			case 'channel':
					$vimeo_call = 'http://vimeo.com/api/v2/channel/'.$vimeo_id.'/videos.json';
				break;
		}
		
		$response = wp_remote_get($vimeo_call, array('timeout' => 60));

		if (! is_wp_error($response) ) {
			$ret = json_decode($response['body'], true);
			
			if(sizeof($ret)){
				if($use_default_styles){
					echo '<ul style="list-style: none; margin-left: 0; padding-left: 0;">';
				}else{
					echo '<ul>';
				}
				for($i = 0; $i < sizeof($ret) && $i < $num_of_videos; $i++){
					echo '<li><a href="'.$ret[$i]['url'].'">';
					echo '<img src="'.$ret[$i][$thumbnail_size].'" alt="'.htmlspecialchars($ret[$i]['title']).'"/>';
					echo '</a></li>';
				}
				echo '</ul>';
			}
		}

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['vimeo_type'] = strip_tags( $new_instance['vimeo_type'] );
		$instance['vimeo_id'] = strip_tags( $new_instance['vimeo_id'] );
		$instance['num_of_videos'] = strip_tags( $new_instance['num_of_videos'] );
		$instance['thumbnail_size'] = strip_tags( $new_instance['thumbnail_size'] );
		$instance['use_default_styles'] = strip_tags( $new_instance['use_default_styles'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Videos', 'vimeo_type' => 'user', 'vimeo_id' => '', 'use_default_styles' => true, 'num_of_videos' => 3, 'thumbnail_size' => 'thumbnail_small' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'vimeobadgewidget'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:95%;" />
		</p>

		<!-- Type: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'vimeo_type' ); ?>"><?php _e('What type of videos?', 'vimeobadgewidget'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'vimeo_type' ); ?>" name="<?php echo $this->get_field_name( 'vimeo_type' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( 'user' == $instance['vimeo_type'] ) echo 'selected="selected"'; ?> value="user">User Videos</option>
				<option <?php if ( 'album' == $instance['vimeo_type'] ) echo 'selected="selected"'; ?> value="album" >User Album Videos</option>
				<option <?php if ( 'group' == $instance['vimeo_type'] ) echo 'selected="selected"'; ?> value="group">Group Videos</option>
				<option <?php if ( 'channel' == $instance['vimeo_type'] ) echo 'selected="selected"'; ?> value="channel">Channel Videos</option>
			</select>
		</p>
		
		<!-- ID: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'vimeo_id' ); ?>">
			<strong>If User:</strong> http://www.vimeo.com/ENTER_THIS<br/>
			<strong>If User Album:</strong> http://www.vimeo.com/album/ENTER_THIS<br/>
			<strong>If Group:</strong> http://www.vimeo.com/groups/ENTER_THIS/<br/>
			<strong>If Channel:</strong> http://vimeo.com/channels/ENTER_THIS<br/>
			</label>
			<input id="<?php echo $this->get_field_id( 'vimeo_id' ); ?>" name="<?php echo $this->get_field_name( 'vimeo_id' ); ?>" value="<?php echo $instance['vimeo_id']; ?>" style="width:95%;" />
		</p>
		
		<!-- Number: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'num_of_videos' ); ?>"><?php _e('Number of Videos?', 'vimeobadgewidget'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'num_of_videos' ); ?>" name="<?php echo $this->get_field_name( 'num_of_videos' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( '1' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="1">1</option>
				<option <?php if ( '2' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="2" >2</option>
				<option <?php if ( '3' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="3">3</option>
				<option <?php if ( '4' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="4">4</option>
				<option <?php if ( '5' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="5">5</option>
				<option <?php if ( '6' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="6" >6</option>
				<option <?php if ( '7' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="7">7</option>
				<option <?php if ( '8' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="8">8</option>
				<option <?php if ( '9' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="9">9</option>
				<option <?php if ( '10' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="10" >10</option>
				<option <?php if ( '11' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="11">11</option>
				<option <?php if ( '12' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="12">12</option>
				<option <?php if ( '13' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="13">13</option>
				<option <?php if ( '14' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="14" >14</option>
				<option <?php if ( '15' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="15">15</option>
				<option <?php if ( '16' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="16">16</option>
				<option <?php if ( '17' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="17">17</option>
				<option <?php if ( '18' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="18" >18</option>
				<option <?php if ( '19' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="19">19</option>
				<option <?php if ( '20' == $instance['num_of_videos'] ) echo 'selected="selected"'; ?> value="20">20</option>
			</select>
		</p>
		
		<!-- Size: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'thumbnail_size' ); ?>"><?php _e('Image Size', 'vimeobadgewidget'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'thumbnail_size' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail_size' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( 'thumbnail_small' == $instance['thumbnail_size'] ) echo 'selected="selected"'; ?> value="thumbnail_small">Small (100x75)</option>
				<option <?php if ( 'thumbnail_medium' == $instance['thumbnail_size'] ) echo 'selected="selected"'; ?> value="thumbnail_medium" >Medium (200x150)</option>
				<option <?php if ( 'thumbnail_large' == $instance['thumbnail_size'] ) echo 'selected="selected"'; ?> value="thumbnail_large">Large (640x363)</option>
			</select>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php if($instance['use_default_styles'] == true || $instance['use_default_styles'] == 'on'){echo 'checked';} ?> id="<?php echo $this->get_field_id( 'use_default_styles' ); ?>" name="<?php echo $this->get_field_name( 'use_default_styles' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'use_default_styles' ); ?>"><?php _e('Use Default Styles', 'vimeobadgewidget'); ?></label>
		</p>

	<?php
	}
}

?>