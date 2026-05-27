<?php
/**
 * Main plugin class.
 *
 * @package Devgraphix\ElementorAddons
 */

namespace Devgraphix\ElementorAddons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 *
 * Singleton bootstrap. Verifies requirements, then wires up the widgets
 * manager and asset loading.
 */
final class Plugin {

	/**
	 * Single instance of the plugin.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Widgets manager.
	 *
	 * @var Widgets_Manager|null
	 */
	public $widgets_manager = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor. Checks requirements before booting.
	 */
	private function __construct() {
		if ( ! $this->is_compatible() ) {
			return;
		}

		$this->init();
	}

	/**
	 * Run all compatibility checks. Each failed check registers its own
	 * admin notice.
	 *
	 * @return bool True when every requirement is satisfied.
	 */
	private function is_compatible() {
		// Elementor installed and active.
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_missing_elementor' ) );
			return false;
		}

		// Minimum Elementor version.
		if ( ! version_compare( ELEMENTOR_VERSION, DGX_EA_MIN_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_minimum_elementor_version' ) );
			return false;
		}

		// Minimum PHP version.
		if ( version_compare( PHP_VERSION, DGX_EA_MIN_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_minimum_php_version' ) );
			return false;
		}

		return true;
	}

	/**
	 * Hook into Elementor once we know it's safe.
	 *
	 * @return void
	 */
	private function init() {
		// Load translations.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Set up the widgets manager (registers category + widgets).
		$this->widgets_manager = new Widgets_Manager();

		// Register frontend / editor assets.
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_frontend_styles' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_frontend_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_frontend_scripts' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );
	}

	/**
	 * Load the plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'devgraphix-elementor-addons', false, dirname( plugin_basename( DGX_EA_FILE ) ) . '/languages' );
	}

	// -----------------------------------------------------------------------
	// Assets
	// -----------------------------------------------------------------------

	/**
	 * Cache-busting version for an asset.
	 *
	 * Uses the file's modification time so any edit forces browsers and
	 * Elementor to fetch the fresh file (falls back to the plugin version).
	 *
	 * @param string $relative_path Path relative to the plugin root, e.g. 'assets/css/x.css'.
	 * @return string
	 */
	private function asset_version( $relative_path ) {
		$file = DGX_EA_PATH . ltrim( $relative_path, '/' );

		if ( file_exists( $file ) ) {
			return (string) filemtime( $file );
		}

		return DGX_EA_VERSION;
	}

	/**
	 * Register per-widget styles so they load only when their widget is on
	 * the page (via each widget's get_style_depends()).
	 *
	 * @return void
	 */
	public function register_frontend_styles() {
		wp_register_style(
			'dgx-ea-hover-card',
			DGX_EA_URL . 'assets/css/widgets/hover-card.css',
			array(),
			$this->asset_version( 'assets/css/widgets/hover-card.css' )
		);

		wp_register_style(
			'dgx-ea-featured-box',
			DGX_EA_URL . 'assets/css/widgets/featured-box.css',
			array(),
			$this->asset_version( 'assets/css/widgets/featured-box.css' )
		);

		wp_register_style(
			'dgx-ea-marquee-pills',
			DGX_EA_URL . 'assets/css/widgets/marquee-pills.css',
			array(),
			$this->asset_version( 'assets/css/widgets/marquee-pills.css' )
		);

		wp_register_style(
			'dgx-ea-product-cards',
			DGX_EA_URL . 'assets/css/widgets/product-cards.css',
			array(),
			$this->asset_version( 'assets/css/widgets/product-cards.css' )
		);

		wp_register_style(
			'dgx-ea-section-heading',
			DGX_EA_URL . 'assets/css/widgets/section-heading.css',
			array(),
			$this->asset_version( 'assets/css/widgets/section-heading.css' )
		);

		wp_register_style(
			'dgx-ea-image-showcase',
			DGX_EA_URL . 'assets/css/widgets/image-showcase.css',
			array(),
			$this->asset_version( 'assets/css/widgets/image-showcase.css' )
		);

		wp_register_style(
			'dgx-ea-comparison-table',
			DGX_EA_URL . 'assets/css/widgets/comparison-table.css',
			array(),
			$this->asset_version( 'assets/css/widgets/comparison-table.css' )
		);

		wp_register_style(
			'dgx-ea-testimonials',
			DGX_EA_URL . 'assets/css/widgets/testimonials.css',
			array(),
			$this->asset_version( 'assets/css/widgets/testimonials.css' )
		);

		wp_register_style(
			'dgx-ea-bmi-calculator',
			DGX_EA_URL . 'assets/css/widgets/bmi-calculator.css',
			array(),
			$this->asset_version( 'assets/css/widgets/bmi-calculator.css' )
		);

		wp_register_style(
			'dgx-ea-savings-calculator',
			DGX_EA_URL . 'assets/css/widgets/savings-calculator.css',
			array(),
			$this->asset_version( 'assets/css/widgets/savings-calculator.css' )
		);

		wp_register_style(
			'dgx-ea-marquee-text',
			DGX_EA_URL . 'assets/css/widgets/marquee-text.css',
			array(),
			$this->asset_version( 'assets/css/widgets/marquee-text.css' )
		);
	}

	/**
	 * Frontend styles (loaded on pages built with Elementor).
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles() {
		wp_enqueue_style(
			'dgx-ea-frontend',
			DGX_EA_URL . 'assets/css/frontend.css',
			array(),
			$this->asset_version( 'assets/css/frontend.css' )
		);
	}

	/**
	 * Register frontend scripts. Individual widgets enqueue what they need
	 * via get_script_depends().
	 *
	 * @return void
	 */
	public function register_frontend_scripts() {
		wp_register_script(
			'dgx-ea-frontend',
			DGX_EA_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			$this->asset_version( 'assets/js/frontend.js' ),
			true
		);

		wp_register_script(
			'dgx-ea-product-cards',
			DGX_EA_URL . 'assets/js/widgets/product-cards.js',
			array(),
			$this->asset_version( 'assets/js/widgets/product-cards.js' ),
			true
		);

		wp_register_script(
			'dgx-ea-marquee-pills',
			DGX_EA_URL . 'assets/js/widgets/marquee-pills.js',
			array(),
			$this->asset_version( 'assets/js/widgets/marquee-pills.js' ),
			true
		);

		wp_register_script(
			'dgx-ea-comparison-table',
			DGX_EA_URL . 'assets/js/widgets/comparison-table.js',
			array(),
			$this->asset_version( 'assets/js/widgets/comparison-table.js' ),
			true
		);

		wp_register_script(
			'dgx-ea-testimonials',
			DGX_EA_URL . 'assets/js/widgets/testimonials.js',
			array(),
			$this->asset_version( 'assets/js/widgets/testimonials.js' ),
			true
		);

		wp_register_script(
			'dgx-ea-bmi-calculator',
			DGX_EA_URL . 'assets/js/widgets/bmi-calculator.js',
			array(),
			$this->asset_version( 'assets/js/widgets/bmi-calculator.js' ),
			true
		);

		wp_register_script(
			'dgx-ea-savings-calculator',
			DGX_EA_URL . 'assets/js/widgets/savings-calculator.js',
			array(),
			$this->asset_version( 'assets/js/widgets/savings-calculator.js' ),
			true
		);

		wp_register_script(
			'dgx-ea-marquee-text',
			DGX_EA_URL . 'assets/js/widgets/marquee-text.js',
			array(),
			$this->asset_version( 'assets/js/widgets/marquee-text.js' ),
			true
		);
	}

	/**
	 * Editor-only styles.
	 *
	 * @return void
	 */
	public function enqueue_editor_styles() {
		wp_enqueue_style(
			'dgx-ea-editor',
			DGX_EA_URL . 'assets/css/editor.css',
			array(),
			$this->asset_version( 'assets/css/editor.css' )
		);
	}

	// -----------------------------------------------------------------------
	// Admin notices
	// -----------------------------------------------------------------------

	/**
	 * Notice: Elementor is not installed / active.
	 *
	 * @return void
	 */
	public function notice_missing_elementor() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name, 2: Elementor name. */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'devgraphix-elementor-addons' ),
			'<strong>' . esc_html__( 'Devgraphix Elementor Addons', 'devgraphix-elementor-addons' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'devgraphix-elementor-addons' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Notice: Elementor version too old.
	 *
	 * @return void
	 */
	public function notice_minimum_elementor_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name, 2: Elementor name, 3: Required version. */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'devgraphix-elementor-addons' ),
			'<strong>' . esc_html__( 'Devgraphix Elementor Addons', 'devgraphix-elementor-addons' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'devgraphix-elementor-addons' ) . '</strong>',
			DGX_EA_MIN_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Notice: PHP version too old.
	 *
	 * @return void
	 */
	public function notice_minimum_php_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name, 2: PHP, 3: Required version. */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'devgraphix-elementor-addons' ),
			'<strong>' . esc_html__( 'Devgraphix Elementor Addons', 'devgraphix-elementor-addons' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'devgraphix-elementor-addons' ) . '</strong>',
			DGX_EA_MIN_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}
