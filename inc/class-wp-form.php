<?php
/**
 * Admin functions and tweaks
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}

/*
$fields = array(
	'name' => array(
		'label' 	=> '',
		'type'		=> 'text',
		'tabindex'	=> '',
		'disabled' 	=> false,
		'class'		=> 'regular-text', // code
		'style'		=> ''
		'value'		=> '',
		'checked'	=> false,
		'accept'	=> '', // audio/* video/* image/*
		'maxlength'	=> 80,
		'max'		=> null,
		'min'		=> null,
		'pattern'	=> '',
		'placeholder'=> '',
		'readonly'	=> false,
		'required'	=> false,
		'step'		=> null,
		'start'		=> null,
		'data'		=> array()
	)
);


/**
 * Helper to construct administrator fields
 *
 * @since 3.6
 */

final class WP_Form {
	
	/**
	 * The fields
	 * 
	 * @since 3.6
	 *
	 * @var array $fields
	 */
	public $fields = array();

	/**
	 * Rendered string
	 * 
	 * @since 3.6
	 *
	 * @var string $html
	 */
	public $html = '';

	/**
	 * Defined field types
	 * 
	 * @since 3.6
	 *
	 * @var array $types
	 */
	public $types = array();
	
	/**
	 * Constructed all the fields array.
	 * Calls proper functions.
	 * 
	 * @since 1.1
	 * 
	 * @param array $args Array of args
	 * 
	 * @return void
	 * 
	 */
	public function __construct() {

		/* Field types can be overwritten */

		$this->types = apply_filters( 'wp_admin_form_types', 
			array(
				// General formats
				'display', 'yesorno', 'custom', 'progressbar',

				// Supported input format types
				'hidden', 'text', 'number', 'email', 'url', 'color', 'name', 'code',
				'date', 'datetime', 'time', 'currency',

				// Connected to Google Maps API or other API
				//'address', 'postalcode', 'city', 'country', 'coordinates',

				// Selectable
				'checkbox', 'inlinebox', 'radio', 'select', 'sortable', 'selectable', //'tags',

				// Long text and HTML
				'textarea', 'editor', 'tinyeditor', 

				// Uploads
				'file', 'image', 'audio', 'video',

				// Dynamic
				'oembed', 'taxonomy', 'user', 'post', /*'googlemap',*/ 'iframe',
			)
		);

		foreach ( $this->types as $type ) {
			add_action( "wp_admin_form_{$type}", array( $this, "add_field_{$type}" ), 10, 2 );

		}

		
	}
	
	/**
	 * Add fields via array
	 *
	 * @since 3.6
	 *
	 * @param array $fields Array of fields
	 * @return bool|WP_Error
	 */
	public function add_fields( $fields ) {

		if ( ! is_array( $fields ) )
			return new WP_Error( 'no_fields', __( 'No fields added', '_wp' ) );


		$index = 1;// Sets the tabindex attribute

		foreach ( $fields as $name => $attrs ) {

			if ( is_object( $attrs ) )
				$attrs = (array) $attrs;

			// Set name to string
			$attrs = wp_parse_args( $attrs, array(
				'id' => $name,
				'name' => $name,
				'type' => 'text',
				'label' => '',
				'value' => ''
				)
			);

			// Check if value is a callable function instead
			if ( is_callable( $attrs['value'] ) )
				$attrs['value'] = call_user_func( $attrs['value'], $name, $attrs );

			
			// Organize this field
			$field = new WP_Form_Field();

			// Set some
			if ( isset( $attrs['checked'] ) && false == $attrs['checked'] )
				unset( $attrs['checked'] );

			if ( isset( $attrs['readonly'] ) && false == $attrs['readonly'] )
				unset( $attrs['readonly'] );

			if ( isset( $attrs['required'] ) && false == $attrs['required'] )
				unset( $attrs['required'] );

			if ( isset( $attrs['options'] ) && empty( $attrs['options'] ) )
				unset( $attrs['options'] );

			if ( isset( $attrs['data'] ) && empty( $attrs['data'] ) )
				unset( $attrs['data'] );

			// Set all attrs
			$field->set_attrs( $attrs );
			
			do_action( "wp_admin_form_".$field->get_attr( 'type' ), $field, $index );

			$index++;
			
		}
		
	}
	
	/**
	 * Get the fields as array of HTML fields
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	public function get_fields_array() {
		return $this->fields;
		
	}

	/**
	 * Get fields as HTML
	 *
	 * @since 3.6
	 *
	 * @param string $wrapper A wrapper with %type%, %label% and %field% as tags inside
	 * @return bool|WP_Error
	 */
	public function get_fields( $wrapper = '' ) {

		if ( empty( $wrapper ) )
			$wrapper = "<p class=\"field-type-%type%\">\n\t%label%'\n\t%field\n</p>";


		foreach ( (array) $this->fields as $field )
			if ( 'hidden' == $field->type )
				$html .= $field->field;

			else
				$html .= str_replace( 
					array( '%type%', '%label%', '%field%' ), 
					array( $field->type, $field->label, $field->field ),
					$wrapper 
				);
		
		return $html;
		
	}
	
	/**
	 * Renders the label for a input
	 *
	 * @since 3.6
	 */
	public function _get_label( $id, $label ) {
		return sprintf( '<label for="%1$s">%2$s</label>', $id, $label );
		
	}

	/**
	 * Generic construction of an <input> field
	 *
	 * @since 3.6
	 */
	public function add_field_input( $field, $index = '' ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// If any index set, add tabindex attr
		if ( !empty( $index ) && is_numeric( $index ) )
			$field->set_attr( 'tabindex', $index );

		// Format the HTML of the input and parse every variable to it
		$html = sprintf( '<input id="%1$s" name="%2$s" type="%3$s" value="%4$s" %5$s %6$s /> %7$s', 
			$field->get_attr( 'id' ), $field->get_attr( 'name' ), $field->get_attr( 'type' ), $field->get_value(),
			wp_parse_attrs( $field->get_attributes() ), $field->get_data( 'html' ), $description );


		$this->fields[] = (object) (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html, $field ),
			'object' => $field,
		);
		
	}

	/**
	 * Generic construction of an <textarea> field
	 *
	 * @since 3.6
	 */
	public function add_field_textarea( $field, $index = '' ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// If any index set, add tabindex attr
		if ( !empty( $index ) && is_numeric( $index ) )
			$field->set_attr( 'tabindex', $index );

		// Set defaults
		if ( !$field->get_attr( 'rows' ) )
			$field->set_attr( 'rows', 3 );

		if ( !$field->get_attr( 'cols' ) )
			$field->set_attr( 'cols', 40 );

		// Format the HTML of the input and parse every variable to it
		$html = sprintf( '<textarea id="%1$s" name="%2$s" %3$s %4$s>%5$s</textarea> %6$s', 
			$field->get_attr( 'id' ), $field->get_attr( 'name' ), wp_parse_attrs( $field->get_attributes() ), 
			$field->get_data( 'html' ), $field->get_value(), $description );


		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html, $field ),
			'object' => $field,
		);
		
	}

	/**
	 * Generic construction of an <textarea> field
	 *
	 * @since 3.6
	 */
	public function add_field_select( $field, $index = '' ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// If any index set, add tabindex attr
		if ( !empty( $index ) && is_numeric( $index ) )
			$field->set_attr( 'tabindex', $index );

		// Get options and format HTML
		$options_html = '';
		foreach ( $field->get_options() as $value => $label ) {
			$options_html .= "\n\t".'<option value="'.$value.'"'.selected( $value, $field->get_value(), false ).'>'.esc_html( $label ).'</option>';

		}

		// Format the HTML of the input and parse every variable to it
		$html = sprintf( '<select id="%1$s" name="%2$s" %3$s %4$s>%5$s</select> %6$s', 
			$field->get_attr( 'id' ), $field->get_attr( 'name' ), wp_parse_attrs( $field->get_attributes() ), 
			$field->get_data( 'html' ), $options_html, $description );


		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html, $field ),
			'object' => $field,
		);
		
	}


	
	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_display( $field ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// Format the HTML of the input and parse every variable to it
		$html = sprintf( '<span id="%1$s" %2$s %3$s>%4$s</span> %5$s', 
			$field->get_attr( 'id' ), wp_parse_attrs( $field->get_attributes() ), $field->get_data( 'html' ), 
			$field->get_value(), $description );


		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html, $field ),
			'object' => $field,
		);

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_custom( $field, $index = '' ) {
		
		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $field->get_attr( 'html' ), $field ),
			'object' => $field,
		);

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_yesorno( $field, $index = '' ) {
			
		$field->set_attr( 'class', sprintf( 'regular-text %s', $field->get_attr( 'class' ) ) );
		
		if ( ! $field->get_options() )
			$field->set_options( array(
				'' => __( '&mdash; Select &mdash;', '_wp' ),
				'yes' => __( 'Yes' ),
				'no' => __( 'No' ),
				) 
			);
		
		$this->add_field_select( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_progressbar( $field, $index = '' ) {

		$field->set_attr( 'class', sprintf( 'progressbar %s', $field->get_attr( 'class' ) ) );
		
		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// Format the HTML of the input and parse every variable to it
		$html = sprintf( '<div id="%1$s" value="%2$s" %3$s %4$s></div> %5$s', 
			$field->get_attr( 'id' ), $field->get_value(), wp_parse_attrs( $field->get_attributes() ), 
			$field->get_data( 'html' ), $description );

		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html, $field ),
			'object' => $field,
		);

	}
	
	
	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_hidden( $field ) {
		$this->add_field_input( $field );

	}
	
	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_text( $field, $index = '' ) {
			
		$field->set_attr( 'class', sprintf( 'regular-text %s', $field->get_attr( 'class' ) ) );
		
		if ( ! $field->get_attr( 'maxlength' ) )
			$field->set_attr( 'maxlength', '80' );

		if ( ! $field->get_attr( 'size' ) )
			$field->set_attr( 'size', '80' );
		
		$this->add_field_input( $field, $index );

	}
	
	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_number( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'code number %s', $field->get_attr( 'class' ) ) );
		
		if ( !( $field->get_attr( 'maxlength' ) ) )
			$field->set_attr( 'maxlength', '9' );

		if ( !( $field->get_attr( 'size' ) ) )
			$field->set_attr( 'size', '9' );

		if ( !( $field->get_attr( 'max' ) ) )
			$field->set_attr( 'size', '999999999' );

		if ( !( $field->get_attr( 'min' ) ) )
			$field->set_attr( 'size', '0' );

		if ( !( $field->get_attr( 'step' ) ) )
			$field->set_attr( 'size', '1' );

		if ( !( $field->get_attr( 'start' ) ) )
			$field->set_attr( 'size', '0' );

		$field->set_attr( 'type', 'text' );
		
		$this->add_field_input( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_email( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'regular-text code %s', $field->get_attr( 'class' ) ) );
		
		if ( !( $field->get_attr( 'maxlength' ) ) )
			$field->set_attr( 'maxlength', '80' );

		if ( !( $field->get_attr( 'size' ) ) )
			$field->set_attr( 'size', '80' );
		
		$this->add_field_input( $field, $index );

	}
	
	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_url( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'regular-text code %s', $field->get_attr( 'class' ) ) );
		
		if ( !( $field->get_attr( 'maxlength' ) ) )
			$field->set_attr( 'maxlength', '80' );

		if ( !( $field->get_attr( 'size' ) ) )
			$field->set_attr( 'size', '80' );
		
		$this->add_field_input( $field, $index );

	}
	
	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_color( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'regular-text color %s', $field->get_attr( 'class' ) ) );
		$field->set_attr( 'type', 'text' );	
		$this->add_field_input( $field, $index );		

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_name( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'regular-text name %s', $field->get_attr( 'class' ) ) );
		
		if ( !( $field->get_attr( 'maxlength' ) ) )
			$field->set_attr( 'maxlength', '80' );

		if ( !( $field->get_attr( 'size' ) ) )
			$field->set_attr( 'size', '80' );
		
		$field->set_attr( 'type', 'text' );
		$this->add_field_input( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_code( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'code %s', $field->get_attr( 'class' ) ) );
		
		if ( !( $field->get_attr( 'rows' ) ) )
			$field->set_attr( 'rows', '5' );

		if ( !( $field->get_attr( 'cols' ) ) )
			$field->set_attr( 'cols', '40' );
		
		$field->set_attr( 'type', 'text' );
		$this->add_field_textarea( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_date( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'code date %s', $field->get_attr( 'class' ) ) );

		if ( !( $field->get_data( 'dateFormat' ) ) )
			$field->set_data( 'dateFormat', 'yy-mm-dd' );

		if ( !( $field->get_data( 'maxDate' ) ) )
			$field->set_data( 'maxDate', '+100y' );

		if ( !( $field->get_data( 'minDate' ) ) )
			$field->set_data( 'minDate', '-100y' );
		
		$field->set_attr( 'type', 'text' );
		$this->add_field_input( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_datetime( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'code datetime %s', $field->get_attr( 'class' ) ) );
		
		if ( !( $field->get_data( 'dateFormat' ) ) )
			$field->set_data( 'dateFormat', 'yy-mm-dd' );

		if ( !( $field->get_data( 'maxDate' ) ) )
			$field->set_data( 'maxDate', '+100y' );

		if ( !( $field->get_data( 'minDate' ) ) )
			$field->set_data( 'minDate', '-100y' );

		if ( !( $field->get_data( 'hourMin' ) ) )
			$field->set_data( 'hourMin', '0' );

		if ( !( $field->get_data( 'hourMax' ) ) )
			$field->set_data( 'hourMax', '24' );

		if ( !( $field->get_data( 'stepHour' ) ) )
			$field->set_data( 'stepHour', '1' );

		if ( !( $field->get_data( 'stepMinute' ) ) )
			$field->set_data( 'stepMinute', '1' );

		if ( !( $field->get_data( 'stepSecond' ) ) )
			$field->set_data( 'stepSecond', '1' );
		
		$field->set_attr( 'type', 'text' );
		$this->add_field_input( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_time( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'code time %s', $field->get_attr( 'class' ) ) );
		
		if ( !( $field->get_data( 'hourMin' ) ) )
			$field->set_data( 'hourMin', '0' );

		if ( !( $field->get_data( 'hourMax' ) ) )
			$field->set_data( 'hourMax', '24' );

		if ( !( $field->get_data( 'stepHour' ) ) )
			$field->set_data( 'stepHour', '1' );

		if ( !( $field->get_data( 'stepMinute' ) ) )
			$field->set_data( 'stepMinute', '1' );

		if ( !( $field->get_data( 'stepSecond' ) ) )
			$field->set_data( 'stepSecond', '1' );
		
		$field->set_attr( 'type', 'text' );
		$this->add_field_input( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_currency( $field, $index = '' ) {
		$field->set_attr( 'class', sprintf( 'code currency %s', $field->get_attr( 'class' ) ) );
		
		if ( !( $field->get_data( 'max' ) ) )
			$field->set_data( 'max', '9999999.99' );

		if ( !( $field->get_data( 'min' ) ) )
			$field->set_data( 'min', '0.00' );

		if ( !( $field->get_data( 'start' ) ) )
			$field->set_data( 'start', '0.00' );

		if ( !( $field->get_data( 'step' ) ) )
			$field->set_data( 'step', '0.01' );

		if ( !( $field->get_data( 'culture' ) ) )
			$field->set_data( array( 'culture' => get_globalize_locale() ) );
		
		$field->set_attr( 'type', 'text' );
		$this->add_field_input( $field, $index );

	}
	
	/**
	 *
	 * @since 3.6
	 */
	public function add_field_checkbox( $field, $index = '' ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// If any index set, add tabindex attr
		if ( !empty( $index ) && is_numeric( $index ) )
			$field->set_attr( 'tabindex', $index );

		// Add checked
		if ( true == $field->checked || $field->get_value() == $field->checked )
			$field->set_attr( 'checked', 'true' );

		$this->add_field_input( $field, $index );

	}

	/**
	 *
	 * @since 3.6
	 */
	public function add_field_inlinebox( $field, $index = '' ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// If any index set, add tabindex attr
		if ( !empty( $index ) && is_numeric( $index ) )
			$field->set_attr( 'tabindex', $index );

		// Get options and format HTML
		$html = '';
		foreach ( $field->get_options() as $value => $label ) {
			$html .= sprintf( '<input id="%1$s" type="checkbox" name="%2$s" %3$s> <label for="%1$s>%4$s</label>',
				$field->get_attr( 'id' ), $field->get_attr( 'name' ), wp_parse_attrs( $field->get_attributes() ), esc_html( $label )
			);

		}

		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html . $description, $field ),
			'object' => $field,
		);
		
	}

	/**
	 *
	 * @since 3.6
	 */
	public function add_field_radio( $field, $index = '' ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// If any index set, add tabindex attr
		if ( !empty( $index ) && is_numeric( $index ) )
			$field->set_attr( 'tabindex', $index );

		// Get options and format HTML
		$html = '';
		foreach ( $field->get_options() as $value => $label ) {
			$html .= sprintf( '<input id="%1$s" type="radio" name="%2$s" %3$s> <label for="%1$s>%4$s</label>',
				$field->get_attr( 'id' ), $field->get_attr( 'name' ), wp_parse_attrs( $field->get_attributes() ), esc_html( $label )
			);

		}

		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html . $description, $field ),
			'object' => $field,
		);
		
	}

	/**
	 *
	 * @since 3.6
	 */
	public function add_field_sortable( $field ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// Get options and format HTML
		$html = sprintf( '<ul class="sortable" %1$s>', $field->get_data( 'html' ) );
		foreach ( $field->get_options() as $value => $label ) {
			$html .= sprintf( '<li id="%1$s" class="ui-state-default" value="%2$s" %3$s><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>%4$s</li>',
				$field->get_attr( 'id' ), $field->get_value(), wp_parse_attrs( $field->get_attributes() ), esc_html( $label )
			);

		}
		$html .= '</ul>';

		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html . $description, $field ),
			'object' => $field,
		);
		
	}

	/**
	 *
	 * @since 3.6
	 */
	public function add_field_selectable( $field ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// Get options and format HTML
		$html = sprintf( '<ol class="selectable">', $field->get_data( 'html' ) );
		foreach ( $field->get_options() as $value => $label ) {
			$html .= sprintf( '<li id="%1$s" class="ui-widget-content" value="%2$s" %3$s>%4$s</li>',
				$field->get_attr( 'id' ), $field->get_value(), wp_parse_attrs( $field->get_attributes() ), esc_html( $label )
			);

		}
		$html .= '</ol>';

		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html . $description, $field ),
			'object' => $field,
		);
		
	}

	/**
	 *
	 * @since 3.6
	 */
	public function add_field_editor( $field = '' ) {
				
		ob_start();

		// Get the HTML
		wp_editor( $field->get_value(), $field->get_attr( 'id' ), $field->get_attr( 'params' ) );

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// Build HTML
		$html = sprintf( '<div class="wp-editor-%1$s" %2$s>%3$s</div> %4$s', $field->get_attr( 'id' ), $field->get_data( 'html' ), 
				ob_get_contents(), $description );
				
		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html, $field ),
			'object' => $field
		);

		ob_end_clean();
		
	}

	/**
	 *
	 * @since 3.6
	 */
	public function add_field_tinyeditor( $field = '' ) {
				
		ob_start();

		// Set params
		$field->set_attr( 'params', array_merge( array( 'teeny' => true ), $field->get_attr( 'params' ) ) );
		

		// Get the HTML
		wp_editor( $field->get_value(), $field->get_attr( 'id' ), $field->get_attr( 'params' ) );

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// Build HTML
		$html = sprintf( '<div class="wp-editor-%1$s" %2$s>%3$s</div> %4$s', $field->get_attr( 'id' ), $field->get_data( 'html' ), 
				ob_get_contents(), $description );
				
		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html, $field ),
			'object' => $field
		);

		ob_end_clean();
		
	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_file( $field, $index = '' ) {		
		$this->add_field_input( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_image( $field, $index = '' ) {

		// Limits
		$field->set_attr( 'accept', 'image/*' );

		// Type fix
		$field->set_attr( 'type', 'file' );

		$this->add_field_input( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_audio( $field, $index = '' ) {

		// Limits
		$field->set_attr( 'accept', 'audio/*' );

		// Type fix
		$field->set_attr( 'type', 'file' );

		$this->add_field_input( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_video( $field, $index = '' ) {

		// Limits
		$field->set_attr( 'accept', 'video/*' );

		// Type fix
		$field->set_attr( 'type', 'file' );

		$this->add_field_input( $field, $index );

	}
	
	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_oembed( $field ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// Format the HTML of the input and parse every variable to it
		$html = sprintf( '<span id="%1$s" %2$s %3$s>%4$s</span> %5$s', 
			$field->get_attr( 'id' ), wp_parse_attrs( $field->get_attributes() ), $field->get_data( 'html' ), 
			wp_oembed_get( $field->get_value(), array( 'width' => $field->get_attr( 'width' ) ) ), $description );


		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html, $field ),
			'object' => $field,
		);

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_taxonomy( $field, $index = '' ) {
		
		// Get taxonomy based on query
		$terms = get_terms( $field->get_attr( 'taxonomies' ), $field->get_attr( 'query' ) );

		$options = array();
		foreach ( (array) $terms as $term ) {
			if ( $field->get_attr( 'use_slugs' ) )
				$term->term_id = $term->slug;

			$options[ $term->term_id ] = $term->name . ( $field->get_attr( 'display_count' ) ? ' ('.$term->count.')' : '' );

		}

		$field->set_options( $options );
		
		if ( true == $field->attr( 'multiple' ) )
			$this->add_field_multiplebox( $field, $index );		
		else
			$this->add_field_select( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_user( $field, $index = '' ) {
		
		// Get taxonomy based on query
		$users = get_users( $field->get_attr( 'query' ) );

		$options = array();
		foreach ( (array) $users as $user ) {
			if ( $field->get_attr( 'use_login' ) )
				$user->ID = $user->user_login;

			$options[ $user->ID ] = $user->display_name . ( $field->get_attr( 'display_email' ) ? ' <'.$user->user_email.'>' : '' );

		}

		$field->set_options( $options );
		
		if ( true == $field->attr( 'multiple' ) )
			$this->add_field_multiplebox( $field, $index );		
		else
			$this->add_field_select( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_post( $field, $index = '' ) {
		
		// Get taxonomy based on query
		$posts = get_posts( $field->get_attr( 'query' ) );

		$options = array();
		foreach ( (array) $posts as $post ) {
			if ( $field->get_attr( 'use_slug' ) )
				$post->ID = $post->post_name;

			$options[ $post->ID ] = $post->post_title;

		}

		$field->set_options( $options );
		
		if ( true == $field->attr( 'multiple' ) )
			$this->add_field_multiplebox( $field, $index );		
		else
			$this->add_field_select( $field, $index );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public function add_field_iframe( $field ) {

		// If any description is set, format it
		$description = '';
		if ( $field->get_attr( 'description' ) )
			$description = '<p class="description">' . $field->get_attr( 'description' ) . '</p>';

		// Format the HTML of the input and parse every variable to it
		$html = sprintf( '<iframe id="%1$s" src="%2$s" %3$s %4$s></iframe> %5$s', 
			$field->get_attr( 'id' ), $field->get_value(), wp_parse_attrs( $field->get_attributes() ), 
			$field->get_data( 'html' ), $description );


		$this->fields[] = (object) array(
			'type' => $field->get_attr( 'type' ),
			'label' => $this->_get_label( $field->get_attr( 'id' ), $field->get_attr( 'label' ) ),
			'html'=> apply_filters( 'wp_form_render_html', $html, $field ),
			'object' => $field,
		);

	}

}


/**
 * Form field class
 *
 * @since 3.6
 *
 * @package WordPress
 * @subpackage _WP
 *
 */
final class WP_Form_Field {

	/**
	 * @var $id
	 */
	private $id;

	/**
	 * @var $name
	 */
	private $name;

	/**
	 * @var $label
	 */
	private $label;

	/**
	 * @var $type
	 */
	private $type;

	/**
	 * @var $value
	 */
	private $value;

	/**
	 * @var $options
	 */
	private $options;

	/**
	 * @var $attributes
	 */
	private $attributes;

	/**
	 * @var $data
	 */
	private $data;

	/**
	 * @var $description
	 */
	private $description;

	/**
	 * @since 3.6
	 */
	public function __construct() {
		$this->name = '';
		$this->value = '';
		$this->type = 'text';
		$this->id = $this->name;

	}

	/**
	 * Get attribute
	 *
	 * @since 3.6
	 */
	public function get_attr( $attr ) {

		if ( 'id' == strtolower( $attr ) )
			return $this->id;

		elseif ( 'name' == strtolower( $attr ) )
			return $this->name;

		elseif ( 'type' == strtolower( $attr ) )
			return $this->type;

		elseif ( 'label' == strtolower( $attr ) )
			return $this->label;

		elseif ( 'description' == strtolower( $attr ) )
			return $this->description;

		elseif ( isset( $this->attributes[ strtolower( $attr ) ] ) )
			return isset( $this->attributes[ strtolower( $attr ) ] );

		else 
			return '';

	}

	/**
	 * Get attribute
	 *
	 * @since 3.6
	 */
	public function get_attributes() {
		return $this->attributes;

	}

	/**
	 * Set attributes
	 *
	 * @since 3.6
	 */
	public function set_attrs( $attrs ) {
		if ( !is_array( $attrs ) )
			return false;

		foreach ( $attrs as $attr => $value )
			$this->set_attr( $attr, $value );

	}


	/**
	 * Set attribute
	 *
	 * @since 3.6
	 */
	public function set_attr( $attr, $value ) {

		if ( 'id' == strtolower( $attr ) )
			$this->id = esc_attr( $value );

		elseif ( 'name' == strtolower( $attr ) ) {
			$this->name = esc_attr( $value );
			if ( empty( $this->id ) )
				$this->id = $value;

		}

		elseif ( 'type' == strtolower( $attr ) )
			$this->type = esc_attr( $value );

		elseif ( 'label' == strtolower( $attr ) )
			$this->label = esc_attr( $value );

		elseif ( 'value' == strtolower( $attr ) )
			$this->set_value( $value );

		elseif ( 'options' == strtolower( $attr ) )
			$this->set_options( $value );

		elseif ( 'data' == strtolower( $attr ) )
			$this->set_data( $value );

		elseif ( 'description' == strtolower( $attr ) )
			$this->description = esc_html( $value );

		else
			return $this->attributes[ strtolower( $attr ) ] = esc_attr( $value );

	}

	/**
	 * Get Value
	 *
	 * @since 3.6
	 */
	public function get_value() {
		return maybe_unserialize( $this->value );

	}

	/**
	 * Set Value
	 *
	 * @since 3.6
	 */
	public function set_value( $value ) {
		if ( !is_serialized( $value ) )
			$value = maybe_serialize( $value );

		$this->value = $value;

	}

	/**
	 * Get Options
	 *
	 * @since 3.6
	 */
	public function get_options() {
		return $this->options;

	}

	/**
	 * Set Options
	 *
	 * @since 3.6
	 */
	public function set_options( $options ) {
		foreach ( (array) $options as $value => $label ) {
			$this->options[ esc_attr( maybe_serialize( $value ) ) ] = esc_html( $label );

		}

	}

	/**
	 * Get Data
	 *
	 * @since 3.6
	 */
	public function get_data( $format = '' ) {
		if ( empty( $format ) )
			return $this->data;

		$html = array();
		if ( 'html' == $format )
			foreach ( (array) $this->data as $id => $value )
				$html['data-'.$id] = $value;

		return wp_parse_attrs( $html );

	}

	/**
	 * Set Data
	 *
	 * @since 3.6
	 */
	public function set_data( $data ) {
		foreach ( (array) $data as $key => $value ) {
			if ( !is_serialized( $value ) )
				$value = maybe_serialize( $value );

			$this->data[ $key ] = $value;

		}

	}

}