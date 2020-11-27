<?php
/**
 * Methods to repeatably construct admin form elements
 *
 * @package COE_AM
 * @subpackage Admin_UI
 * @author John Galyon
 * @since 1.0.0
 * @license GPL-2.0+
 */

/**
 *  <div class="row form-group">
 *      <label for="" class="col-sm-3 col-form-label">
 *          <strong></strong><span></span>
 *      </label>
 *      <div class="com-sm-9">
 *          <input type="text">
 *          <p></p>
 *          <span class="help-text"></span>
 *      </div>
 *  </div>
 */
class Coe_Am_Admin_UI {
	/**
	 * Create the opening row tag
	 *
	 * @since 1.0.0
	 * @return string opening div.row.form-group
	 */
	public function open_row_div() {
		return '<div class="row form-group">';
	}

	/**
	 * Create the closing row tag
	 *
	 * @since 1.0.0
	 * @return string closing div.row
	 */
	public function close_div() {
		return '</div>';
	}

	/**
	 * Create the opening col tag
	 *
	 * @since 1.0.0
	 * @return string opening div.col
	 */
	public function open_col_div() {
		return '<div class="col-sm-9">';
	}

	/**
	 * Create the opening label tag
	 */
	public function make_label( $label_for = '', $label_text = '', $required = false ) {
		$label  = '<label for="' . esc_attr( $label_for ) . '" class="col-sm-3 col-form-label" style="font-weight: bold;">';
		$label .= wp_strip_all_tags( $label_text );

		if ( $required ) {
			$label .= '<span style="color: #ff0000; padding-left: 2px;">*</span>';
		}

		$label .= '</label>';

		return $label;
	}

	public function make_required( $required = false ) {
		$attr = $required ? true : false;

		return ' aria-required="' . $attr . '" required="' . $attr . '"';
	}

	public function make_description( $name = '', $help_text = '', $additional_text = '' ) {
		$text  = '<span class="' . $name . '-help form-text text-muted" style="font-style: italic;';
		$text .= ( $additional_text ) ? ' margin-bottom: 10px;">' : '">';
		$text .= $help_text . '</span>';

		if ( $additional_text ) {
			$text .= '<span class="' . $name . '-additional-text">' . $additional_text . '</span>';
		}

		return $text;
	}

	public function make_placeholder( $placeholder = '' ) {
		return ' placeholder="' . $placeholder . '" ';
	}

	public function make_maxlength( $max_length = '' ) {
		return ' maxlength="' . $max_length . '"';
	}

	/**
	 * Create an array containing the default parameters for
	 * our inputs
	 *
	 * @since 1.0.0
	 * @a
	 */
	public function get_default_input_parameters( $additions = array() ) {
		return array_merge(
			array(
				'field_desc'  => '',
				'label_text'  => '',
				'name'        => '',
				'placeholder' => '',
				'required'    => false,
				'textvalue'   => '',
				'wrap'        => true,
			),
			(array) $additions
		);
	}

	/**
	 * Create the text input
	 *
	 * @since 1.0.0
	 * @param array $args
	 * @return string input element
	 */
	public function make_text_input( $args = array() ) {
		$defaults = $this->get_default_input_parameters(
			array(
				'maxlength' => '',
				'onblur'    => '',
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$val = '';
		if ( $args['wrap'] ) {
			$val .= $this->open_row_div();
			$val .= $this->make_label( $args['name'], $args['label_text'], $args['required'] );
			$val .= $this->open_col_div();
		}

		// And now we output the input itself.
		$val .= '<input type="text" class="form-control form-control-sm" id="' . $args['name'] . '" name="' . $args['name'] . '"';

		if ( $args['placeholder'] ) {
			$val .= $this->make_placeholder( $args['placeholder'] );
		}

		if ( $args['maxlength'] ) {
			$val .= $this->make_maxlength( $args['maxlength'] );
		}

		if ( $args['required'] ) {
			$val .= $this->make_required( $args['required'] );
		}

		$val .= '/>';

		if ( $args['field_desc'] ) {
			$val .= $this->make_description( $args['name'], $args['field_desc'], $args['additional_text'] );
		}

		if ( $args['wrap'] ) {
			$val .= $this->close_div();
			$val .= $this->close_div();
		}

		return $val;
	}

	/**
	 * Create and output a select field
	 *
	 * @since 1.0.0
	 * @param  array $args
	 * @return string select field
	 */
	public function make_select_input( $args = array() ) {
		$defaults = $this->get_default_input_parameters(
			array( 'options' => array() )
		);

		$args = wp_parse_args( $args, $defaults );

		$val = '';
		if ( $args['wrap'] ) {
			$val .= $this->open_row_div();
			$val .= $this->make_label( $args['name'], $args['label_text'], $args['required'] );
			$val .= $this->open_col_div();
		}

		$val .= '<select class="form-control" id="' . $args['name'] . '" name="' . $args['name'] . '">';

		foreach ( $args['options'] as $opt ) {
			$selected = ( isset( $opt['selected'] ) ) ? 'selected="selected"' : '';
			$val     .= '<option value="' . $opt['value'] . '"' . $selected . '>' . $opt['text'] . '</option>';
		}

		$val .= '</select>';
		if ( $args['field_desc'] ) {
			$val .= $this->make_description( $args['name'], $args['field_desc'], $args['additional_text'] );
		}

		if ( $args['wrap'] ) {
			$val .= $this->close_div();
			$val .= $this->close_div();
		}

		return $val;
	}

	/**
	 * Create and output a textarea field
	 *
	 * @since 1.0.0
	 * @param  array $args
	 * @return string textarea field
	 */
	public function make_textarea( $args = array() ) {
		$defaults = $this->get_default_input_parameters(
			array(
				'rows' => '',
				'cols' => '',
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$val = '';
		if ( $args['wrap'] ) {
			$val .= $this->open_row_div();
			$val .= $this->make_label( $args['name'], $args['label_text'], $args['required'] );
			$val .= $this->open_col_div();
		}

		// And now we output the input itself.
		$val .= '<textarea class="form-control form-control-sm" id="' . $args['name'] . '" name="' . $args['name'] . '"';

		if ( $args['placeholder'] ) {
			$val .= $this->make_placeholder( $args['placeholder'] );
		}

		if ( $args['required'] ) {
			$val .= $this->make_required( $args['required'] );
		}

		if ( $args['rows'] ) {
			$val .= 'rows=' . $args['rows'] . '" ';
		}

		if ( $args['cols'] ) {
			$val .= 'cols="' . $args['cols'] . '" ';
		}

		$val .= '/></textarea>';

		if ( $args['field_desc'] ) {
			$val .= $this->make_description( $args['name'], $args['field_desc'], $args['additional_text'] );
		}

		if ( $args['wrap'] ) {
			$val .= $this->close_div();
			$val .= $this->close_div();
		}

		return $val;
	}
}
