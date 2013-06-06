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




/**
 * Helper to construct administrator fields
 *
 * @since 3.6
 */

class WP_Admin_Form {
	
	/**
	 * Rendering string
	 * 
	 * @since 1.1
	 */
	public $html = '';
	
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
		foreach ( apply_filters( 'wp_admin_form_types', array(
			'hidden', 'input', 'text', 'number', 'email', 'url', 'date', 'checkbox', 'radio', 'select', 
			'textarea', 'editor', 'custom', 'display' )
			) as $type )
			add_action( "wp_admin_form_{$type}", array( &$this, "add_field_{$type}" ), 10, 2 );
		
	}
	
	public function add_fields( $fields ) {
		$index = 0;
		foreach ( (array) $fields as $name => $field ) {
			if ( ! is_object( $field ) )
				$field = (object) $field;
			
			$field->name = $name;
			
			if ( ! isset( $field->label ) )
				$field->label = '';
			
			if ( ! isset( $field->attributes ) )
				$field->attributes = array();
			
			if ( ! isset( $field->description ) )
				$field->description = '';
			
			do_action( "wp_admin_form_{$field->type}", $field );
			
		}
		
	}
	
	public function get_fields( $format = '' ) {
		if ( 'array' == strtolower( $format ) )
			return $this->fields;
		
		if ( 'p' == strtolower( $format ) ) {
			foreach ( (array) $this->fields as $field )
				$html .= "<p class=\"form-type-{$field->type}\">\n\t{$field->label}\n\t{$field->field}\n</p>";
			
			return $html;
			
		}
		
		if ( 'li' == strtolower( $format ) ) {
			foreach ( (array) $this->fields as $field )
				$html .= "<li class=\"form-type-{$field->type}\">\n\t{$field->label}\n\t{$field->field}\n</li>";
			
			return $html;
			
		}
		
		if ( in_array( $format, array( '', 'div' ) ) ) {
			foreach ( (array) $this->fields as $field )
				$html .= "<div class=\"form-type-{$field->type}\">\n\t{$field->label}\n\t{$field->field}\n</div>";
			
			return $html;
			
		}
		
	}
	
	public function _get_label( $id, $label ) {
		return sprintf( '<label for="%1$s">%2$s</label>', $id, $label );
		
	}
	
	public function add_field_hidden( $field = '' ) {
		$field = (object) wp_parse_args( 
			(array) $field, array(
				'id' => $field->name,
				'name' => '',
				'value' => '',
				'attributes' => '',
				'callback_value' => '',
			)
		);
		
		$this->fields[] = array(
			'type' => 'hidden',
			'label' => false,
			'field'=> sprintf( 
				'<input id="%1$s" type="hidden" name="%2$s" value="%3$s" %4$s>', 
				$field->id, 
				$field->name, 
				( $field->callback_value ? call_user_func( $field->callback_value, $field->value ) : $field->value ),
				_wp_convert_array_to_attrs( $field->attributes )
			),
		);
		
	}
	
	public function add_field_text( $field = '' ) {
		if ( ! isset( $field->type ) )
			$field->type = 'text';
		
		if ( ! isset( $field->attributes['class'] ) )
			$field->attributes['class'] = '';
		
		$field->attributes['class'] = sprintf( 'regular-text %s', $field->attributes['class'] );
		
		$field->attributes = wp_parse_args( $field->attributes, array(
			'maxlenght' => '80',
			'size' => '80',
			)
		);
		
		$this->add_field_input( $field );
	}
	
	public function add_field_number( $field = '' ) {
		if ( ! isset( $field->type ) )
			$field->type = 'number';
		
		if ( ! isset( $field->attributes['class'] ) )
			$field->attributes['class'] = '';
		
		$field->attributes['class'] = sprintf( 'code %s', $field->attributes['class'] );
		
		$this->add_field_input( $field );
	}
	
	public function add_field_email( $field = '' ) {
		if ( ! isset( $field->type ) )
			$field->type = 'email';
		
		if ( ! isset( $field->attributes['class'] ) )
			$field->attributes['class'] = '';
		
		$field->attributes['class'] = sprintf( 'regular-text code %s', $field->attributes['class'] );
		
		$this->add_field_input( $field );
	}
	
	public function add_field_url( $field = '' ) {
		if ( ! isset( $field->type ) )
			$field->type = 'url';
		
		if ( ! isset( $field->attributes['class'] ) )
			$field->attributes['class'] = '';
		
		$field->attributes['class'] = sprintf( 'regular-text code %s', $field->attributes['class'] );
		
		$this->add_field_input( $field );
	}

	public function add_field_date( $field = '' ) {
		if ( ! isset( $field->type ) )
			$field->type = 'date';
		
		if ( ! isset( $field->attributes['class'] ) )
			$field->attributes['class'] = '';
		
		$field->attributes['class'] = sprintf( 'code %s', $field->attributes['class'] );
		
		$this->add_field_input( $field );
		
	}
	
	public function add_field_time( $field = '' ) {
		if ( ! isset( $field->type ) )
			$field->type = 'time';
		
		if ( ! isset( $field->attributes['class'] ) )
			$field->attributes['class'] = '';
		
		$field->attributes['class'] = sprintf( 'code %s', $field->attributes['class'] );
		
		$this->add_field_input( $field );
		
	}
	
	
	public function add_field_input( $field = '' ) {
		$field = (object) wp_parse_args( 
			(array) $field, array(
				'label' => '',
				'id' => $field->name,
				'type' => 'text',
				'name' => '',
				'value' => '',
				'attributes' => array( 'class' => "regular-text" ),
				'description' => '',
				'callback_value' => '',
			)
		);
		
		$this->fields[] = array(
			'type' => $field->type,
			'label' => $this->_get_label( $field->id, $field->label ),
			'field'=> sprintf( 
				'<input id="%1$s" type="%2$s" name="%3$s" value="%4$s" %5$s> %6$s', 
				$field->id, 
				$field->type,
				$field->name, 
				( $field->callback_value ? call_user_func( $field->callback_value, $field->value ) : $field->value ),
				_wp_convert_array_to_attrs( $field->attributes ),
				$field->description
			),
		);
		
	}
	
	public function add_field_radio( $field = '' ) {
		$field = (object) wp_parse_args( 
			(array) $field, array(
				'label' => '',
				'id' => $field->name,
				'type' => 'radio',
				'name' => '',
				'value' => '',
				'attributes' => '',
				'description' => '',
				'options' => array(),
			)
		);
		
		foreach ( $field->options as $option_index => $option ) {
			if ( is_array( $option ) )
				$option = (object) $option;
			
			$html[] = $this->_get_label( $field->id.'-'.$option_index, 
				"\n\t" . sprintf( 
					'<input id="%1$s" type="%2$s" name="%3$s" value="%4$s" %5$s> %6$s', 
					$field->id.'-'.$option_index, 
					$field->type, 
					$field->name, 
					$option->value,
					_wp_convert_array_to_attrs( $field->attributes ).checked( $field->value, $option->value, false ), 
					$option->label
				) . "\n" 
			);
			
		}
		
		$this->fields[] = array(
			'type' => $field->type,
			'label' => $this->_get_label( $field->id, $field->label ),
			'field'=> implode( "\n", $html ) . $field->description
		);
		
	}
	
	/**
	 * $field [
	 * 	label, id, type, name, value, attributes, description, options
	 * ]
	 * $option [
	 * 	value, label
	 * ]
	 */
	public function add_field_checkbox( $field = '' ) {
		$field = (object) wp_parse_args( 
			(array) $field, array(
				'label' => '',
				'id' => $field->name,
				'type' => 'checkbox',
				'inline' => true,
				'name' => '',
				'value' => '',
				'attributes' => '',
				'description' => '',
				'options' => '',
			)
		);
		
		$html = '';
		foreach ( (array) $field->options as $option_index => $option ) {
			if ( is_array( $option ) || is_object( $option ) )
				$option = (object) $option;
			
			elseif ( is_string( $option ) )
				$option = (object) array(
					'value' => $option_index,
					'label' => $option,
				);
				
			
			
			$checked = 0;
			if ( in_array( $option->value, $field->value ) ) {
				$checked = 1;
				
			}
			
			$html .= $this->_get_label( $field->id.'-'.$option_index, 
				"\n\t" . sprintf( 
					( !$field->inline ? '<p>' : '' ) .
					'<input id="%1$s" type="%2$s" name="%3$s[]" value="%4$s" %5$s> %6$s' .
					( !$field->inline ? '</p>' : '' ), 
					$field->id.'-'.$option_index, 
					$field->type, 
					$field->name, 
					$option->value,
					_wp_convert_array_to_attrs( $field->attributes ).checked( $checked, 1, false ),
					$option->label 
				) . "\n" 
			);
			
		}
		
		$this->fields[] = array(
			'type'  => $field->type,
			'label' => $this->_get_label( '', $field->label ),
			'field' => $html . $field->description,
		);
		
	}
	
	public function add_field_select( $field = '' ) {
		$field = (object) wp_parse_args( 
			(array) $field, array(
				'label' => '',
				'id' => $field->name,
				'type' => 'select',
				'name' => '',
				'value' => '',
				'attributes' => '',
				'description' => '',
				'options' => '',
			)
		);
		
		$this->fields[] = array(
			'type'  => $field->type,
			'label' => $this->_get_label( $field->id, $field->label ),
			'field' => sprintf( 
				'<select id="%1$s" name="%2$s" %3$s>%4$s</select> %5$s', 
				$field->id, 
				$field->name, 
				_wp_convert_array_to_attrs( $field->attributes ),
				$this->_select_options( $field->options, $field ), 
				$field->description 
			),
		);
		
	}

	public function _select_options( $options, $field ) {
		$html = '';
		foreach ( (array) $options as $option_index => $option ) {
			if ( is_array( $option ) || is_object( $option ) )
				$option = (object) wp_parse_args( 
					$option, array( 'value' => '', 'label' =>'' ) 
				);
			
			elseif ( is_string( $option ) )
				$option = (object) array(
					'value' => $option_index,
					'label' => $option,
				);
			
			$html .= "\n\t<option value=\"{$option->value}\"".selected( $field->value, $option->value, false ).">{$option->label}</option>";
			
		}
		return $html;
		
	}
	
	public function add_field_textarea( $field = '' ) {
		$field = (object) wp_parse_args( 
			(array) $field, array(
				'label' => '',
				'id' => $field->name,
				'type' => 'textarea',
				'name' => '',
				'content' => $field->value,
				'attributes' => array( 'class' => 'regular-text' ),
				'description' => '',
				'callback_content' => '',
			)
		);
		
		//var_dump( $field );
		
		$this->fields[] = array(
			'type'  => $field->type,
			'label' => $this->_get_label( $field->id, $field->label ),
			'field' => sprintf( 
				'<textarea id="%1$s" name="%2$s" rows="5" cols="80" %3$s>%4$s</textarea> %5$s', 
				$field->id, 
				$field->name,
				_wp_convert_array_to_attrs( $field->attributes ),
				( $field->callback_content ? call_user_func( $field->callback_content, $field->content ) : $field->content ),
				$field->description
			)
		);
		
	}
	
	public function add_field_editor( $field = '' ) {
		$field = (object) wp_parse_args( 
			(array) $field, array(
				'label' => '',
				'id' => $field->name,
				'type' => 'editor',
				'name' => '',
				'content' => '',
				'params' => null,
				'description' => '',
			)
		);
		
		ob_start();
		wp_editor( $field->content, $field->id, $field->params );
				
		$this->fields[] = array(
			'type'  => $field->type,
			'label' => $this->_get_label( $field->id, $field->label ),
			'field' => sprintf( 
				'<div class="wp-editor-%1$s">%2$s</div> %3$s', 
				$field->id, 
				ob_get_contents(), 
				$field->description 
			),
		);
		ob_end_clean();
		
	}
	
	public function add_field_custom( $field = '' ) {
		$field = (object) wp_parse_args( 
			(array) $field, array(
				'label' => '',
				'id' => $field->name,
				'callback' => '',
			)
		);
		
		$this->fields[] = array(
			'type'  => $field->type,
			'label' => $this->_get_label( $field->id, $field->label ),
			'field' => call_user_func( $field->callback ),
		);
		
	}
	
	public function add_field_display( $field = '' ) {
		$field = (object) wp_parse_args( 
			(array) $field, array(
				'label' => '',
				'id' => $field->name,
				'type' => 'text',
				'value' => '',
				'attributes' => array( 'class' => "regular-text" ),
				'description' => '',
				'callback_value' => '',
			)
		);
		
		$this->fields[] = array(
			'type' => $field->type,
			'label' => sprintf( '<span>%s</span>', $field->label ),
			'field'=> sprintf( 
				'<span id="%1$s" %2$s>%3$s</span> %4$s', 
				$field->id, 
				_wp_convert_array_to_attrs( $field->attributes ),
				( $field->callback_value ? call_user_func( $field->callback_value, $field->value ) : $field->value ),
				$field->description
			),
		);
		
	}
	
}

