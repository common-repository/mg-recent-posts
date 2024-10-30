<?php
/*
Plugin Name: mg Recent Posts
Plugin URI: http://mgiulio.info/projects/mg-recent-posts
Description: An improvement of the default Recent Posts widget to select posts by a category
Version: 1.0
Author: Giulio 'mgiulio' Mainardi
Author URI: http://mgiulio.info
License: GPL2
*/

if (!defined('ABSPATH')) exit;

class mg_Recent_Posts {

	public function __construct() {
		add_action('widgets_init', array($this, 'register_widgets'));
	}
	
	public function register_widgets() {
		register_widget('mg_Widget_Recent_Posts');
	}

}

new mg_Recent_Posts();

require_once 'includes/class-mg-widget-recent-posts.php';
 