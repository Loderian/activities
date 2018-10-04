<?php

if ( !defined( 'WPINC' ) ) {
  die;
}

class Activities_Admin_Utility {
  /**
   * Get nice setting from options and post values
   *
   * @return array Nice settings
   */
  static function get_activity_nice_settings() {
    $nice_settings = Activities_Options::get_option( ACTIVITIES_NICE_SETTINGS_KEY );
    if ( !is_array( $nice_settings ) ) {
      $nice_settings = unserialize( $nice_settings );
    }

    if ( ( isset( $_POST['save_options']) || isset( $_POST['save_nice_settings'] ) ) && isset( $_POST['item_id'] ) ) {
      if ( isset( $_POST['save_nice_settings'] ) && isset( $_POST[ACTIVITIES_ADMIN_NICE_NONCE] ) && !wp_verify_nonce( $_POST[ACTIVITIES_ADMIN_NICE_NONCE], 'activities_nice' ) ) {
        die( esc_html__( 'Could not verify activity report data integrity.', 'activities' ) );
      }
      if ( is_numeric( $_POST['item_id'] ) ) {
        $nice_settings['activity_id'] = $_POST['item_id'];
      }
      if ( isset( $_POST['acts_nice_logo_id'] ) && is_numeric( $_POST['acts_nice_logo_id'] ) && $_POST['acts_nice_logo_id'] != '0' ) {
        $nice_settings['logo'] = intval( $_POST['acts_nice_logo_id'] );
      }
      else {
        $nice_settings['logo'] = 0;
      }
      if ( isset( $_POST['header'] ) ) {
        $nice_settings['header'] = sanitize_text_field( $_POST['header'] );
      }
      if ( isset( $_POST['time_slots'] ) && is_numeric( $_POST['time_slots'] ) && $_POST['time_slots'] >= 0 ) {
        $nice_settings['time_slots'] = intval( $_POST['time_slots'] );
      }
      $nice_settings['member_info'] = sanitize_text_field( $_POST['member_info'] );
      foreach (array('start', 'end', 'short_desc', 'location', 'responsible', 'long_desc') as $a_key) {
        $nice_settings[$a_key] = isset( $_POST[$a_key] );
      }
      $custom = array();
      if ( isset( $_POST['nice_custom'] ) && isset( $_POST['nice_custom_col'] ) && count( $_POST['nice_custom'] ) == count( $_POST['nice_custom_col'] ) ) {

        for ($index=0; $index < count( $_POST['nice_custom'] ); $index++) {
          $name = self::filter_meta_key_input( $_POST['nice_custom'][$index] );
          if ( $name !== '' ) {
            $custom[] = array( 'name' => $name, 'col' => intval( $_POST['nice_custom_col'][$index] ) );
          }
        }
      }
      $nice_settings['custom'] = $custom;
      $colors = array();
      if ( isset( $_POST['nice_color_key'] ) && isset( $_POST['nice_color'] ) && count( $_POST['nice_color_key'] ) == count( $_POST['nice_color'] ) ) {
        for ($index=0; $index < count( $_POST['nice_color_key'] ); $index++) {
          $name = self::filter_meta_key_input( $_POST['nice_color_key'][$index] );
          $color = sanitize_text_field( $_POST['nice_color'][$index] );
          if ( $name !== '' && $color !== '' && !isset( $color[$name] ) && preg_match('/#(?:[0-9a-fA-F]{3}){1,2}/', $color ) ) {
            $colors[$name] = $color;
          }
        }
      }
      $nice_settings['color'] = $colors;
    }

    return $nice_settings;
  }

  /**
   * Gets post values for activity
   *
   * @return array Activity info
   */
  static function get_activity_post_values() {
    $act_map = array(
      'name' => sanitize_text_field( $_POST['name'] ),
      'short_desc' => sanitize_text_field( $_POST['short_desc'] ),
      'long_desc' => sanitize_textarea_field( $_POST['long_desc'] ),
      'start' => $_POST['start'],
      'end' => $_POST['end'],
      'location_id' => ( is_numeric( $_POST['location'] ) ? $_POST['location'] : null ),
      'responsible_id' => ( is_numeric( $_POST['responsible'] ) ? $_POST['responsible'] : null ),
      'members' => sanitize_text_field( $_POST['member_list'] )
    );
    if ( isset( $_POST['item_id'] ) ) {
      $act_map['activity_id'] = intval( $_POST['item_id'] );
    }
    return $act_map;
  }

  /**
   * Gets post values for location
   *
   * @return array Location info
   */
  static function get_location_post_values() {
    $loc_map = array(
      'name' => sanitize_text_field( $_POST['name'] ),
      'address' => sanitize_text_field( $_POST['address'] ),
      'description' => sanitize_textarea_field( $_POST['description'] ),
      'city' => sanitize_text_field( $_POST['city'] ),
      'postcode' => sanitize_text_field( $_POST['postcode'] ),
      'country' => sanitize_text_field( $_POST['country'] )
    );

    if ( isset( $_POST['item_id'] ) ) {
      $loc_map['location_id'] = $_POST['item_id'];
    }

    return $loc_map;
  }

  /**
   * Gets columns for activities
   *
   * @param   string  $archive 'Archive' to get columns for archive display
   * @return  array   Columns info
   */
  static function get_activity_columns( $archive = '' ) {
    if ( $archive != 'archive' ) {
      $options = Activities_Options::get_user_option( 'activity', 'show_columns' );
    }
    else {
      $options = Activities_Options::get_user_option( 'activity_archive', 'show_columns' );
    }

    $columns = array(
      'cb' => array(
        'hidden' => false,
        'sortable' => false,
      ),
      'name' => array(
        'hidden' => false,
        'sortable' => true,
      ),
      'short_desc' => array(
        'hidden' => !$options['short_desc'],
        'sortable' => false,
      ),
      'long_desc' => array(
        'hidden' => !$options['long_desc'],
        'sortable' => false,
      ),
      'start' => array(
        'hidden' => !$options['start'],
        'sortable' => true,
      ),
      'end' => array(
        'hidden' => !$options['end'],
        'sortable' => true,
      ),
      'responsible' => array(
        'hidden' => !$options['responsible'],
        'sortable' => true,
      ),
      'location' => array(
        'hidden' => !$options['location'],
        'sortable' => true,
      )
    );

    return $columns;
  }

  /**
   * Generates random activities for testing
   */
  static function generate_random_activities( $num = 100000 ) {
    $chars = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for ($i=0; $i < $num; $i++) {
      $name = '';
      for ($c=0; $c < 10; $c++) {
        $name .= $chars[rand(0,strlen($chars)-1)];
      }
      Activities_Activity::insert( array( 'name' => $name, 'archive' => 1 ) );
    }

    echo "created $num activities";
  }

  /**
   * Checks if a user can access an activity
   *
   * @param   string  $action Action done by a user
   * @param   int     $act_id Activity to check for access
   * @return  bool    If the user can do this action for selected activity
   */
  static function can_access_act( $action, $act_id ) {
    if ( $action == 'view' ) {
      $access = current_user_can( ACTIVITIES_ACCESS_ACTIVITIES );
      if ( Activities_Responsible::current_user_restricted_view() ) {
        $act = new Activities_Activity( $act_id );
        $access = $access && get_current_user_id() == $act->responsible_id;
      }
    }
    elseif ( $action == 'edit' ) {
      $access = current_user_can( ACTIVITIES_ADMINISTER_ACTIVITIES );
      if ( !$access && Activities_Responsible::current_user_restricted_edit() ) {
        $act = new Activities_Activity( $act_id );
        $access = get_current_user_id() == $act->responsible_id;
      }
    }

    return $access;
  }

  /**
   * Gets user for responsible or member input/display
   *
   * @param   string  $role Activity role, 'responsible' or 'members'
   * @param   array   $current_value Current users stored, used i case they are filtered by options but still needs to be displayed
   * @return  array   'ID' for user id, 'display_name' for name to display
   */
  static function get_users( $role, $current_value = array() ) {
    global $wpdb, $wp_roles;

    switch ($role) {
      case 'responsible':
        $key = ACTIVITIES_CAN_BE_RESPONSIBLE_KEY;
        break;

      case 'member':
      case 'members':
        $key = ACTIVITIES_CAN_BE_MEMBER_KEY;
        break;
    }

    $users = get_users( array( 'role__in' => Activities_Options::get_option( $key ) ) );

    $user_names = array();

    if ( !is_array( $current_value ) ) {
      $current_value = array( $current_value );
    }

    foreach ( $users as $user ) {
      $user_names[] = array( 'ID' => $user->ID, 'display_name' => Activities_Utility::get_user_name( $user ) );
      if ( count( $current_value ) > 0 ) {
        $key = array_search( $user->ID, $current_value );
        if ( $key !== false ) {
          unset( $current_value[$key] );
        }
      }
    }

    if ( count( $current_value ) > 0 ) {
      foreach ($current_value as $user_id) {
        $user = get_user_by( 'ID', $user_id );
        if ( $user !== false ) {
          $user_names[] = array( 'ID' => $user_id, 'display_name' => Activities_Utility::get_user_name( $user ) );
        }
      }
    }

    return $user_names;
  }

  /**
   * Filters meta_key inputs from text fields
   *
   * @param   string  $input Text input
   * @return  string  Filtered text with only existing meta_keys
   */
  static function filter_meta_key_input( $input ) {
    global $wpdb;

    $input = sanitize_text_field( $input );

    $meta_fields = $wpdb->get_col(
      "SELECT DISTINCT meta_key
      FROM $wpdb->usermeta"
    );

    $input_list = explode( ',', $input );
    foreach ($input_list as $key => $single_input) {
      $single_input = trim( $single_input );
      if ( activities_nice_filter_custom_field( $single_input ) || !in_array( $single_input, $meta_fields ) ) {
        unset( $input_list[$key] );
      }
      else {
        $input_list[$key] = $single_input;
      }
    }
    $input = implode( ', ', $input_list );
    return $input;
  }

  /**
   * Echoes a scroll script for imports and other big data workloads
   */
  static function echo_scroll_script() {
    echo '<script>';
    echo 'var interval = setInterval( function() {
            jQuery("html, body").animate({ scrollTop: jQuery(".acts-progress-row").last().offset().top }, 50);
            if (jQuery("input[type=\'submit\'][name=\'return\']").length) {
              clearInterval(interval);
            }
          }, 100);';
    echo '</script>';
  }

  /**
    * Gets display name for data columns
    *
    * @param 	string $name Name of data column
    * @return string Display name
    */
  static function get_column_display( $name ) {
    switch ($name) {
      case 'name':
        return esc_html__( 'Name', 'activities' );
        break;

      case 'short_desc':
        return esc_html__( 'Short Description', 'activities' );
        break;

      case 'long_desc':
        return esc_html__( 'Long Description', 'activities' );
        break;

      case 'start':
        return esc_html__( 'Start Date', 'activities' );
        break;

      case 'end':
        return esc_html__( 'End Date', 'activities' );
        break;

      case 'responsible':
      case 'responsible_id':
        return esc_html__( 'Responsible', 'activities' );
        break;

      case 'location':
      case 'location_id':
        return esc_html__( 'Location', 'activities' );
        break;

      case 'address':
        return esc_html__( 'Address', 'activities' );
        break;

      case 'description':
        return esc_html__( 'Description', 'activities' );
        break;

      case 'city':
        return esc_html__( 'City', 'activities' );
        break;

      case 'postcode':
        return esc_html__( 'Postcode', 'activities' );
        break;

      case 'country':
        return esc_html__( 'Country', 'activities' );
        break;

      default:
        return 'undefined';
        break;
    }
  }
}
