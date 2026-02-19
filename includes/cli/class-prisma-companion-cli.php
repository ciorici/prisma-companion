<?php
/**
 * Enables Prisma Companion, via the the command line.
 *
 * @package Prisma Companion
 * @author  Prisma Core Team
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prisma Companion CLI class.
 */
class Prisma_Companion_CLI {

	/**
	 * Load required files and hooks to make the CLI work.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Load command files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require_once dirname( __FILE__ ) . '/commands/class-cli-import.php';
	}

	/**
	 * Sets up and hooks WP CLI to our CLI code.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {
		WP_CLI::add_hook( 'after_wp_load', 'Prisma_Companion_CLI_Import::register_commands' );
	}
}
new Prisma_Companion_CLI();
