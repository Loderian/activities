<?php
/*
* Installs DB tables for activities
*/
class Activities_Installer {

  /**
   * Setup before installing
   */
  public function __construct() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  }

  /**
   * Installs all required tables for this plugin
   */
  public function install_all_default_tables() {
    $installed_ver = get_option( 'activities_db_version' );
    if ( !$installed_ver ) {
      $installed_ver = '0.0.0';
    }
    if ( version_compare( $installed_ver, PLUGIN_DB_VERSION ) < 0 ) {
      $this->install_location_table();
      $this->install_activity_table();
      $this->install_user_activity_table();
      $this->install_activity_meta_table();

      update_option( 'activities_db_version', PLUGIN_DB_VERSION );
    }
  }

  /**
   * Adds default capabilities to page admin
   */
  public function add_capabilities() {
    global $wp_roles;
    
		$wp_roles->add_cap( 'administrator', ACTIVITIES_ACCESS_ACTIVITIES );
		$wp_roles->add_cap( 'administrator', ACTIVITIES_ADMINISTER_ACTIVITIES );
		$wp_roles->add_cap( 'administrator', ACTIVITIES_ADMINISTER_OPTIONS );
  }

  /**
   * Installs activity table
   */
  public function install_activity_table() {
    global $wpdb;

    $table_name = Activities::get_table_name( 'activity' );

    $charset_collate = $wpdb->get_charset_collate();

    $sql_activity = "CREATE TABLE $table_name (
      activity_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      name varchar(100) NOT NULL UNIQUE,
      short_desc tinytext DEFAULT '' NOT NULL,
      long_desc text DEFAULT '' NOT NULL,
      location_id bigint(20) UNSIGNED,
      start datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      end datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      responsible_id bigint(20) UNSIGNED,
      archive boolean DEFAULT 0 NOT NULL,
      PRIMARY KEY  (activity_id),
      KEY activity_res (responsible_id),
      KEY activity_loc (location_id),
      KEY activity_arc (archive)
    ) $charset_collate;";

    dbDelta( $sql_activity );
  }

  /**
   * Installs user activity table (members)
   */
  public function install_user_activity_table() {
    global $wpdb;

    $table_name = Activities::get_table_name( 'user_activity' );

    $charset_collate = $wpdb->get_charset_collate();

    $sql_user_activity = "CREATE TABLE $table_name (
      user_id bigint(20) UNSIGNED NOT NULL,
      activity_id bigint(20) UNSIGNED NOT NULL,
      PRIMARY KEY  (user_id,activity_id)
    ) $charset_collate;";

    dbDelta( $sql_user_activity );
  }

  /**
   * Installs location table
   */
  public function install_location_table() {
    global $wpdb;

    $table_name = Activities::get_table_name( 'location' );

    $charset_collate = $wpdb->get_charset_collate();

    $sql_location = "CREATE TABLE $table_name (
      location_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      name varchar(100) DEFAULT '' NOT NULL,
      address varchar(255) DEFAULT '' NOT NULL,
      postcode varchar(12) DEFAULT '' NOT NULL,
      city varchar(100) DEFAULT '' NOT NULL,
      description text DEFAULT '' NOT NULL,
      country varchar(2) DEFAULT '' NOT NULL,
      PRIMARY KEY  (location_id),
      KEY location_name (name),
      KEY location_add (address)
    ) $charset_collate;";

    dbDelta( $sql_location );
  }

  /**
   * Installs activity meta table
   */
  public function install_activity_meta_table() {
    global $wpdb;

    $table_name = Activities::get_table_name( 'activity_meta' );

    $charset_collate = $wpdb->get_charset_collate();

    $sql_activity_meta = "CREATE TABLE $table_name (
      ameta_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      activity_id bigint(20) UNSIGNED NOT NULL,
      meta_key varchar(255) DEFAULT NULL,
      meta_value longtext DEFAULT NULL,
      PRIMARY KEY  (ameta_id),
      KEY activity_id (activity_id),
      KEY meta_key (meta_key)
    ) $charset_collate;";

    dbDelta( $sql_activity_meta );
  }
}
