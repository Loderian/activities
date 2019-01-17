<?php

if ( !defined( 'WPINC' ) ) {
  die;
}

/**
 * Checks version number and updates
 *
 * @since      1.0.5
 * @package    Activities
 * @subpackage Activities/includes
 * @author     Mikal Naustdal <miknau94@gmail.com>
 */
class Activities_Updater {
  /**
   * List of updates
   *
   * @var array
   */
  static $db_updates = array(
    '1.0.1' => array( __CLASS__, 'db_update_1_0_1' ),
    '1.1.0' => array( __CLASS__, 'db_update_1_1_0' )
  );

  static function init() {
    add_action( 'plugins_loaded', array( __CLASS__, 'update' ) );
  }

  /**
   * Update to the newset version
   */
  static function update() {
    require_once dirname( __FILE__ ) . '/class-activities-installer.php';

    $installed_ver = get_option( 'activities_db_version' );
    if ( version_compare( $installed_ver, ACTIVITIES_DB_VERSION ) >= 0 ) {
      return;
    }

    foreach (self::$db_updates as $update_ver => $callback) {
      if ( version_compare( $update_ver, $installed_ver ) > 0 ) {
        if ( call_user_func( $callback ) ) {
          update_option( 'activities_db_version', $update_ver );
          $installed_ver = $update_ver;
        }
        else {
          //If an update was unsuccessful, try again later
          return;
        }
      }
    }
  }

  /**
   * Update db to version 1.0.1
   *
   * @return bool Returns true on successful update
   */
  static function db_update_1_0_1() {
    return Activities_Category::add_uncategorized();
  }

  /**
   * Update db to version 1.1.0
   *
   * @return bool Returns true on successful update
   */
  static function db_update_1_1_0() {
    global $wpdb;

    $acts_table = Activities::get_table_name( 'activity' );
    $wpdb->query( "ALTER TABLE $acts_table MODIFY name VARCHAR(200);" );
    $wpdb->query( "ALTER TABLE $acts_table ADD plan_id bigint(20);" );

    $locs_table = Activities::get_table_name( 'location' );
    $wpdb->query( "ALTER TABLE $locs_table MODIFY name VARCHAR(200);" );

    $installer = new Activities_Installer();
    $installer->install_plans_table();
    $installer->install_plans_slots_table();

    return true;
  }
}
Activities_Updater::init();