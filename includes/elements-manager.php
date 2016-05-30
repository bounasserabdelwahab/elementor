<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Elements_Manager {

	/**
	 * @var Element_Base[]
	 */
	protected $_register_elements = [];

	public function init() {
		include( ELEMENTOR_PATH . 'includes/elements/base.php' );
		include( ELEMENTOR_PATH . 'includes/elements/column.php' );
		include( ELEMENTOR_PATH . 'includes/elements/section.php' );

		$this->register_element( __NAMESPACE__ . '\Element_Column' );
		$this->register_element( __NAMESPACE__ . '\Element_Section' );

		do_action( 'elementor/elements/elements_registered' );
	}

	public function get_categories() {
		// TODO: Need to filter
		return [
			'layout' => [
				'title' => __( 'Layout', 'elementor' ),
				'icon' => 'columns',
			],
			'basic' => [
				'title' => __( 'Basic', 'elementor' ),
				'icon' => 'font',
			],
			'media' => [
				'title' => __( 'Media', 'elementor' ),
				'icon' => 'picture-o',
			],
			'navigation' => [
				'title' => __( 'Navigation', 'elementor' ),
				'icon' => 'bars',
			],
			'social' => [
				'title' => __( 'Social', 'elementor' ),
				'icon' => 'share-alt',
			],
			'posts' => [
				'title' => __( 'Posts', 'elementor' ),
				'icon' => 'file-text-o',
			],
			'commerce' => [
				'title' => __( 'Commerce', 'elementor' ),
				'icon' => 'shopping-cart',
			],
			'marketing' => [
				'title' => __( 'Marketing', 'elementor' ),
				'icon' => 'briefcase',
			],
			'wordpress' => [
				'title' => __( 'WordPress', 'elementor' ),
				'icon' => 'wordpress',
			],
			'miscellaneous' => [
				'title' => __( 'Miscellaneous', 'elementor' ),
				'icon' => 'diamond',
			],
		];
	}

	public function register_element( $element_class ) {
		if ( ! class_exists( $element_class ) ) {
			return new \WP_Error( 'element_class_name_not_exists' );
		}

		$element_instance = new $element_class();

		if ( ! $element_instance instanceof Element_Base ) {
			return new \WP_Error( 'wrong_instance_element' );
		}

		$this->_register_elements[ $element_instance->get_id() ] = $element_instance;

		return true;
	}

	public function unregister_element( $id ) {
		if ( ! isset( $this->_register_elements[ $id ] ) ) {
			return false;
		}
		unset( $this->_register_elements[ $id ] );
		return true;
	}

	public function get_register_elements() {
		return $this->_register_elements;
	}

	public function get_element( $id ) {
		$elements = $this->get_register_elements();

		if ( ! isset( $elements[ $id ] ) ) {
			return false;
		}

		return $elements[ $id ];
	}

	public function get_register_elements_data() {
		$data = [];
		foreach ( $this->get_register_elements() as $element ) {
			$data[ $element->get_id() ] = $element->get_data();
		}

		return $data;
	}

	public function render_elements_content() {
		foreach ( $this->get_register_elements() as $element ) {
			$element->print_template();
		}
	}

	public function ajax_save_builder() {
		if ( isset( $_POST['revision'] ) && DB::REVISION_PUBLISH === $_POST['revision'] ) {
			$revision = DB::REVISION_PUBLISH;
		} else {
			$revision = DB::REVISION_DRAFT;
		}
		$posted = json_decode( stripslashes( html_entity_decode( $_POST['data'] ) ), true );

		Plugin::instance()->db->save_builder( $_POST['post_id'], $posted, $revision );
		die;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );

		add_action( 'wp_ajax_elementor_save_builder', [ $this, 'ajax_save_builder' ] );
	}
}