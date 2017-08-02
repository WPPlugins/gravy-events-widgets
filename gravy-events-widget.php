<?php
/*
Plugin Name: Gravy Events
Plugin URI: http://findgravy.com
Description: Display local events based on channels or moods.
Version: 1.0
Author: Edward Ritter
Author URI: http://findgravy.com
Author Email: eritter@findgravy.com
Text Domain: gravy-events-locale
Domain Path: /lang/
Network: false
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2013 Gravy (eritter@findgravy.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Gravy_Events_Mood_Widget extends WP_Widget {

    /*--------------------------------------------------*/
    /* Constructor
    /*--------------------------------------------------*/

    /**
     * Specifies the classname and description, instantiates the widget,
     * loads localization files, and includes necessary stylesheets and JavaScript.
     */
    public function __construct() {
        // load plugin text domain
        add_action( 'init', array( $this, 'widget_textdomain' ) );

        // Hooks fired when the Widget is activated and deactivated
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        parent::__construct(
            'gravy-events-mood',
            __( 'Gravy Events by Mood', 'gravy-events-locale' ),
            array(
                'classname'     =>  'gravy_events-mood',
                'description'   =>  __( 'Display local events by mood.', 'gravy-events-locale' )
            )
        );

        // Register admin styles and scripts
        add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

        // Register site styles and scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );
    }

    /*--------------------------------------------------*/
    /* Widget API Functions
    /*--------------------------------------------------*/

    /**
     * Outputs the content of the widget.
     *
     * @param   array   args        The array of form elements
     * @param   array   instance    The current instance of the widget
     */
    public function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        extract($instance);
        list($lat,$lng) = $this->geocode_location($location);
        $data = $this->fetch_events($mood, $lat, $lng, $timerange);

        if ( false !== $data && isset($data->events)) {
          $events = json_decode($data->events);
          echo $before_widget;
          include( plugin_dir_path( __FILE__ ) . '/views/widget-mood.php' );
          echo $after_widget;
        }
    }

   /**
     * Processes the widget's options to be saved.
     *
     * @param   array   new_instance    The previous instance of values before the update.
     * @param   array   old_instance    The new instance of values to be generated via the update.
     */
   public function update( $new_instance, $old_instance ) {
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['location'] = strip_tags($new_instance['location']);
      $instance = $new_instance;
      return $instance;
   }

    /**
     * Generates the administration form for the widget.
     *
     * @param   array   instance    The array of keys and values for the widget.
     */
    public function form( $instance ) {
      $defaults = array( 'mood' => '0', 'timerange' => 'WEEKEND', 'location' => 'Washington, DC', 'layout' => 'list');
      $instance = wp_parse_args((array) $instance, $defaults);
      extract($instance, EXTR_SKIP);
      // Display the admin form
      include( plugin_dir_path(__FILE__) . '/views/admin-mood.php' );
    }

   /*--------------------------------------------------*/
   /* Private Functions
   /*--------------------------------------------------*/

   private function fetch_events($mood, $lat, $lng, $timerange) {

    $events = get_transient('gravy_events_mood');

    if (!$events
        || $events->mood !== $mood || $events->timerange !== $timerange
        || $events->latitude !== $lat || $events->longitude !== $lng) {
      $url = 'http://alertme.findgravy.com/event/'
         . $lat . '/' . $lng . '/' . $mood . '/' . $timerange;
      $response = wp_remote_get($url);

      $data = new stdClass();
      $data->mood = $mood;
      $data->latitude = $lat;
      $data->longitude = $lng;
      $data->timerange = $timerange;
      $data->events = $response['body'];

      set_transient('gravy_events_mood', $data, 20);
      return $data;
    }

    return $events;

   }

   private function fetch_moods() {
      $url = 'http://alertme.findgravy.com/event/moods';
      $moods = wp_remote_get($url);
      return json_decode($moods['body']);
   }

   private function geocode_location($location) {
      try {
         $response = wp_remote_get("http://maps.google.com/maps/api/geocode/json?address="
         . urlencode($location) . "&sensor=false");
         $json = json_decode($response['body']);
         $lat = $json->results[0]->geometry->location->lat;
         $lng = $json->results[0]->geometry->location->lng;
         return array($lat,$lng);
      } catch( Exception $ex) {
         $json = null;
         return $json;
      }
   }

   /*--------------------------------------------------*/
   /* Public Functions
   /*--------------------------------------------------*/

    /**
     * Loads the Widget's text domain for localization and translation.
     */
    public function widget_textdomain() {
        load_plugin_textdomain( 'gravy-events-locale', false, plugin_dir_path( __FILE__ ) . '/lang/' );
    }

    /**
     * Fired when the plugin is activated.
     *
     * @param       boolean $network_wide   True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public function activate( $network_wide ) {
        // TODO define activation functionality here
    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @param   boolean $network_wide   True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
     */
    public function deactivate( $network_wide ) {
        // TODO define deactivation functionality here
    }

    /**
     * Registers and enqueues admin-specific styles.
     */
    public function register_admin_styles() {
        $plugin_url = plugins_url('/css/admin.css', __FILE__);
        wp_enqueue_style( 'gravy-events-admin-styles', $plugin_url);
    }

    /**
     * Registers and enqueues admin-specific JavaScript.
     */
    public function register_admin_scripts() {
        $plugin_url = plugins_url('/js/admin.js', __FILE__);
        wp_enqueue_script( 'gravy-events-admin-script', $plugin_url, array('jquery'), false, true );
    }

    /**
     * Registers and enqueues widget-specific styles.
     */
    public function register_widget_styles() {
        $plugin_url = plugins_url( 'css/widget.css' , __FILE__ );
        wp_enqueue_style( 'gravy-events-widget-styles', $plugin_url);
    }

    /**
     * Registers and enqueues widget-specific scripts.
     */
    public function register_widget_scripts() {
        $plugin_url = plugins_url( 'js/widget.js' , __FILE__ );
        wp_enqueue_script( 'gravy-events-script', $plugin_url, array('jquery'), false, true );
    }
}

class Gravy_Events_Channel_Widget extends WP_Widget {

    /*--------------------------------------------------*/
    /* Constructor
    /*--------------------------------------------------*/

    /**
     * Specifies the classname and description, instantiates the widget,
     * loads localization files, and includes necessary stylesheets and JavaScript.
     */
    public function __construct() {
        // load plugin text domain
        add_action( 'init', array( $this, 'widget_textdomain' ) );

        // Hooks fired when the Widget is activated and deactivated
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        parent::__construct(
            'gravy-events-channel',
            __( 'Gravy Events by Channel', 'gravy-events-locale' ),
            array(
                'classname'     =>  'gravy_events-channel',
                'description'   =>  __( 'Display local events by channel.', 'gravy-events-locale' )
            )
        );

        // Register admin styles and scripts
        add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

        // Register site styles and scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );
    }

    /*--------------------------------------------------*/
    /* Widget API Functions
    /*--------------------------------------------------*/

    /**
     * Outputs the content of the widget.
     *
     * @param   array   args        The array of form elements
     * @param   array   instance    The current instance of the widget
     */
    public function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        extract($instance);

        list($lat,$lng) = $this->geocode_location($location);
        $data = $this->fetch_events($channel, $lat, $lng);

        if ( false !== $data && isset($data->events)) {
          $events = json_decode($data->events);
          echo $before_widget;
          include( plugin_dir_path( __FILE__ ) . '/views/widget-channel.php' );
          echo $after_widget;
        }
    }

   /**
   * Processes the widget's options to be saved.
   *
   * @param   array   new_instance    The previous instance of values before the update.
   * @param   array   old_instance    The new instance of values to be generated via the update.
   */
   public function update( $new_instance, $old_instance ) {
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['location'] = strip_tags($new_instance['location']);
      $instance = $new_instance;
      return $instance;
   }

   /**
   * Generates the administration form for the widget.
   *
   * @param   array   instance    The array of keys and values for the widget.
   */
   public function form( $instance ) {
      $defaults = array( 'location' => 'Washington, DC', 'layout' => 'list');
      $instance = wp_parse_args((array) $instance, $defaults);
      $channels = $this->fetch_channels();
      foreach ($channels->featuredChannels as $channel) {
        $featuredChannels .= '<option value="' . $channel->alertMeCampaignID . '" '
          . (($channel->alertMeCampaignID == $instance['channel'])?'selected="selected"':'')
          . '>' . $channel->name . "</option>\n";
      }
      foreach ($channels->genericChannels as $channel) {
        $genericChannels .= '<optgroup label="' . $channel->channelGroupName . '">';
        foreach ($channel->alertMeCampaign as $campaign) {
          $genericChannels .= '<option value="' . $campaign->alertMeCampaignID . '" '
            . (($campaign->alertMeCampaignID == $instance['channel'])?'selected="selected"':'')
            . '>' . $campaign->name . "</option>\n";
        }
        $genericChannels .= "</optgroup>\n";
      }
      extract($instance, EXTR_SKIP);
      // Display the admin form
      include( plugin_dir_path(__FILE__) . '/views/admin-channel.php' );
   }

   /*--------------------------------------------------*/
   /* Private Functions
   /*--------------------------------------------------*/

  private function fetch_events($channel, $lat, $lng) {

    $events = get_transient('gravy_events_channel');

    if (!$events
        || $events->channel !== $channel
        || $events->latitude !== $lat || $events->longitude !== $lng) {
      $url = 'http://alertme.findgravy.com/channel/' . $channel
         . '/' . $lat . '/' . $lng;
      $response = wp_remote_get($url);

      $data = new stdClass();
      $data->channel = $channel;
      $data->latitude = $lat;
      $data->longitude = $lng;
      $data->events = $response['body'];

      set_transient('gravy_events_channel', $data, 20);
      return $data;
    }
    return $events;
  }

   private function fetch_channels() {
      $url = 'http://alertme.findgravy.com/channel';
      $channels = wp_remote_get($url);
      $featured = array();
      return json_decode($channels['body']);
   }

   private function geocode_location($location) {
      try {
         $response = wp_remote_get("http://maps.google.com/maps/api/geocode/json?address="
         . urlencode($location) . "&sensor=false");
         $json = json_decode($response['body']);
         $lat = $json->results[0]->geometry->location->lat;
         $lng = $json->results[0]->geometry->location->lng;
         return array($lat,$lng);
      } catch( Exception $ex) {
         $json = null;
         return $json;
      }
   }


    /*--------------------------------------------------*/
    /* Public Functions
    /*--------------------------------------------------*/

    /**
     * Loads the Widget's text domain for localization and translation.
     */
    public function widget_textdomain() {
        load_plugin_textdomain( 'gravy-events-locale', false, plugin_dir_path( __FILE__ ) . '/lang/' );
    }

    /**
     * Fired when the plugin is activated.
     *
     * @param       boolean $network_wide   True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public function activate( $network_wide ) {
        // TODO define activation functionality here
    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @param   boolean $network_wide   True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
     */
    public function deactivate( $network_wide ) {
        // TODO define deactivation functionality here
    }

    /**
     * Registers and enqueues admin-specific styles.
     */
    public function register_admin_styles() {
        $plugin_url = plugins_url( 'css/admin.css' , __FILE__ );
        wp_enqueue_style( 'gravy-events-admin-styles', $plugin_url);
    }

    /**
     * Registers and enqueues admin-specific JavaScript.
     */
    public function register_admin_scripts() {
        $plugin_url = plugins_url( 'js/admin.js' , __FILE__ );
        wp_enqueue_script( 'gravy-events-admin-script', $plugin_url, array('jquery'), false, true );
    }

    /**
     * Registers and enqueues widget-specific styles.
     */
    public function register_widget_styles() {
        $plugin_url = plugins_url( 'css/widget.css' , __FILE__ );
        wp_enqueue_style( 'gravy-events-widget-styles', $plugin_url);
    }

    /**
     * Registers and enqueues widget-specific scripts.
     */
    public function register_widget_scripts() {
        $plugin_url = plugins_url( 'js/widget.js' , __FILE__ );
        wp_enqueue_script( 'gravy-events-script', $plugin_url, array('jquery'), false, true );
    }
}

function add_all_gravy_widgets() {
    register_widget("Gravy_Events_Mood_Widget");
    register_widget("Gravy_Events_Channel_Widget");
}

add_action( 'widgets_init', 'add_all_gravy_widgets');

