<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CR_Reminders_Log' ) ) :

class CR_Reminders_Log {
	const LOGS_TABLE = 'cr_reminders_log';
	private $logs_table = '';

	public function __construct() {

	}

	private function check_create_table() {
		// check if the reminders logs table exists
		global $wpdb;
		$table_name = $wpdb->prefix . self::LOGS_TABLE;
		$name_check = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		if ( $name_check !== $table_name ) {
			// check if the database converted the table name to lowercase
			$table_name_l = strtolower( $table_name );
			if ( $name_check !== $table_name_l ) {
				if ( true !== $wpdb->query(
						"CREATE TABLE `$table_name` (
							`id` bigint unsigned,
							`orderId` varchar(190) DEFAULT NULL,
							`customerEmail` varchar(1024) DEFAULT NULL,
							`customerName` varchar(1024) DEFAULT NULL,
							`status` varchar(20) DEFAULT NULL,
							`local` varchar(20) DEFAULT NULL,
							`type` varchar(20) DEFAULT NULL,
							`dateCreated` datetime DEFAULT NULL,
							`dateSent` datetime DEFAULT NULL,
							`dateEmailOpened` datetime DEFAULT NULL,
							`dateFormOpened` datetime DEFAULT NULL,
							`dateReviewPosted` datetime DEFAULT NULL,
							`language` varchar(10) DEFAULT NULL,
							`reminder` json DEFAULT NULL,
							PRIMARY KEY (`id`),
							KEY `orderId_index` (`orderId`),
							KEY `customerEmail_index` (`customerEmail`),
							KEY `dateCreated_index` (`dateCreated`),
							KEY `dateSent_index` (`dateSent`)
						) CHARACTER SET 'utf8mb4';" ) ) {
					// it is possible that Maria DB is used that does not support JSON type
					if( true !== $wpdb->query(
							"CREATE TABLE `$table_name` (
								`id` bigint unsigned,
								`orderId` varchar(190) DEFAULT NULL,
								`customerEmail` varchar(1024) DEFAULT NULL,
								`customerName` varchar(1024) DEFAULT NULL,
								`status` varchar(20) DEFAULT NULL,
								`local` varchar(20) DEFAULT NULL,
								`type` varchar(20) DEFAULT NULL,
								`dateCreated` datetime DEFAULT NULL,
								`dateSent` datetime DEFAULT NULL,
								`dateEmailOpened` datetime DEFAULT NULL,
								`dateFormOpened` datetime DEFAULT NULL,
								`dateReviewPosted` datetime DEFAULT NULL,
								`language` varchar(10) DEFAULT NULL,
								`reminder` text DEFAULT NULL,
								PRIMARY KEY (`id`),
								KEY `orderId_index` (`orderId`),
								KEY `customerEmail_index` (`customerEmail`),
								KEY `dateCreated_index` (`dateCreated`),
								KEY `dateSent_index` (`dateSent`)
							) CHARACTER SET 'utf8mb4';" ) ) {
						return array( 'code' => 1, 'text' => 'Table ' . $table_name . ' could not be created' );
					}
				}
			} else {
				$table_name = $name_check;
			}
		}
		return $table_name;
	}

	public function add() {
		
	}
}

endif;
