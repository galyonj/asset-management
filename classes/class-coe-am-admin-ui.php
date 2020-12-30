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
	public function open_col_div( $offset = false ) {
		$offset = ( filter_var( $offset, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ) ? ' offset-sm-3' : '';
		return '<div class="col-sm-9' . $offset . '">';
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

	public function make_default_value( $value = '' ) {
		return ' value="' . $value . '"';
	}

	public function make_description( $name = '', $help_text = '', $additional_text = '' ) {
		$desc  = '<span class="' . $name . '-help form-text text-muted" style="font-style: italic; width: 100%;';
		$desc .= ( $additional_text ) ? ' margin-bottom: 10px;">' : '">';
		$desc .= $help_text . '</span>';

		if ( $additional_text ) {
			$desc .= '<span class="' . $name . '-additional-text">' . $additional_text . '</span>';
		}

		return $desc;
	}

	public function make_placeholder( $placeholder = '' ) {
		return ' placeholder="' . $placeholder . '" ';
	}

	public function make_maxlength( $max_length = '' ) {
		return ' maxlength="' . $max_length . '"';
	}

	public function make_hidden( $visible = true ) {
		if ( ! isset( $visible ) ) {
			return ' style="display: none;';
		}
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
				'btn'         => false,
				'field_desc'  => '',
				'label_text'  => '',
				'name'        => '',
				'placeholder' => '',
				'required'    => false,
				'textvalue'   => '',
				'wrap'        => true,
				'offset'      => false,
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
				'value'     => '',
				'visible'   => true,
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$val = '';
		if ( $args['wrap'] ) {
			$val .= $this->open_row_div();
			$val .= $this->make_label( $args['name'], $args['label_text'], $args['required'] );
			$val .= $this->open_col_div( $args['offset'] );
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

		if ( $args['textvalue'] ) {
			$val .= $this->make_default_value( $args['textvalue'] );
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
			array( 'selections' => array() )
		);

		$args = wp_parse_args( $args, $defaults );

		$val = '';
		if ( $args['wrap'] ) {
			$val .= $this->open_row_div();
			$val .= $this->make_label( $args['name'], $args['label_text'], $args['required'] );
			$val .= $this->open_col_div( $args['offset'] );
		}

		$val .= '<select class="form-control form-control-sm" id="' . $args['name'] . '" name="' . $args['name'] . '">';

		if ( ! empty( $args['selections']['options'] ) && is_array( $args['selections']['options'] ) ) {
			foreach ( $args['selections']['options'] as $opt ) {
				$selected_opt = $args['selections']['selected'];
				$result       = '';
				$is_bool      = coerce_bool( $opt['value'] );

				if ( is_numeric( $selected_opt ) ) {
					$selected = coerce_bool( $selected_opt );
				}

				if ( ! empty( $selected ) && is_bool( $selected ) ) {
					$result = 'selected="selected"';
				} else {
					if ( array_key_exists( 'default', $opt ) && ! empty( $opt['default'] ) ) {
						if ( empty( $selected ) ) {
							$result = 'selected="selected"';
						}
					}
				}

				if ( ! is_numeric( $selected_opt ) && ( ! empty( $selected_opt ) && $selected_opt === $opt['value'] ) ) {
					$result = 'selected="selected"';
				}

				$val .= '<option value="' . $opt['value'] . '" ' . $result . '>' . $opt['text'] . '</option>';
			}
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
			$val .= $this->open_col_div( $args['offset'] );
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

	public function make_checkbox( $args = array() ) {
		$defaults   = $this->get_default_input_parameters(
			array(
				'checkvalue' => '',
				'checked'    => false,
			)
		);
		$args       = wp_parse_args( $args, $defaults );
		$is_checked = empty( $args['checked'] ) ? ' checked' : '';
		$val        = '';

		if ( $args['wrap'] ) {
			$val .= $this->open_row_div();
			$val .= $this->open_col_div( $args['offset'] );
		}

		$val .= '<input type="checkbox" id="' . $args['name'] . '" name="' . $args['name'] . '" value="' . $args['checkvalue'] . '" />';
		$val .= '<label for="' . $args['name'] . '" style="margin: 0 0 0 5px;">' . $args['label_text'] . '</label>';

		if ( $args['field_desc'] ) {
			$val .= $this->make_description( $args['name'], $args['field_desc'], $args['additional_text'] );
		}

		if ( $args['wrap'] ) {
			$val .= $this->close_div();
			$val .= $this->close_div();
		}

		return $val;
	}

	public function make_btn( $args = array() ) {

	}
}
