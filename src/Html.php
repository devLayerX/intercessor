<?php
/**
 * Intercessor HTML Template Helper
 *
 * @package     Intercessor
 * @subpackage  Classes/HTML
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0php GNU Public License
 * @since       1.0.0
 */

namespace Intercessor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Html {

	/**
	 * Outputs prayer request form fields.
	 *
	 * @param string $key Key.
	 * @param mixed $args Arguments.
	 * @param string $value Value (default: null).
	 *
	 * @return mixed|void
	 */
	public function form_fields( $key, $args, $value = null ) {
		$defaults = [
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'maxlength'         => false,
			'required'          => false,
			'autocomplete'      => false,
			'id'                => $key,
			'class'             => [],
			'label_class'       => [],
			'input_class'       => [],
			'return'            => false,
			'options'           => [],
			'custom_attributes' => [],
			'validate'          => [],
			'default'           => '',
			'autofocus'         => '',
			'priority'          => '',
		];

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'intercessor_form_field_args', $args, $key, $value );

		if ( $args['required'] ) {
			$required_class  = 'required="required"';
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_html__( 'required', 'intercessor' ) . '">*</abbr>';
		} else {
			$required_class  = 'required=""';
			$required        = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'intercessor' ) . ')</span>';
		}

		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = [ $args['label_class'] ];
		}

		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		// Custom attribute handling.
		$custom_attributes         = [];
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

		if ( $args['maxlength'] ) {
			$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
		}

		if ( ! empty( $args['autocomplete'] ) ) {
			$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
		}

		if ( true === $args['autofocus'] ) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( $args['description'] ) {
			$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
		}

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$field           = '';
		$label_id        = $args['id'];
		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '<div class="col %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</div>';

		switch ( $args['type'] ) {
			case 'textarea':
				$field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . esc_attr( $required_class ) . '>' . esc_textarea( $value ) . '</textarea>';

				break;
			case 'checkbox':
				$field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . esc_attr( $required_class ) . '>
						<input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> ' . $args['label'] . $required  . '</label>';

				break;
			case 'text':
			case 'password':
			case 'datetime':
			case 'datetime-local':
			case 'date':
			case 'month':
			case 'time':
			case 'week':
			case 'number':
			case 'email':
			case 'url':
			case 'tel':
				$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . esc_attr( $required_class ) . ' />';

				break;
			case 'select':
				$field   = '';
				$options = '';

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						if ( '' === $option_key ) {
							if ( empty( $args['placeholder'] ) ) {
								$args['placeholder'] = $option_text ? $option_text : esc_html__( 'Choose an option', 'intercessor' );
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}
						$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
					}

					$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="intercessor-select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . esc_attr( $required_class ) . '">
							' . $options . '
						</select>';
				}

				break;
			case 'radio':
				$label_id = current( array_keys( $args['options'] ) );

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						$field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . esc_attr( $required_class ) . ' />';
						$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . $option_text . '</label>';
					}
				}

				break;

		}

		if ( ! empty( $field ) ) {
			$field_html = '';

			if ( $args['label'] && 'checkbox' !== $args['type'] ) {
				$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			$field_html .= '<span class="intercessor-input-wrapper">' . $field;

			if ( $args['description'] ) {
				$field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
			}

			$field_html .= '</span>';

			$container_class = esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
		}

		/**
		 * Filter by type.
		 */
		$field = apply_filters( 'intercessor_form_field_' . $args['type'], $field, $key, $args, $value );

		/**
		 * General filter on form fields.
		 *
		 * @since 0.9.5
		 */
		$field = apply_filters( 'intercessor_form_field', $field, $key, $args, $value );

		if ( $args['return'] ) {
			return $field;
		} else {
			echo $field;
		}
	}

	/**
	 * Renders an HTML Dropdown of all the Prayers
	 *
	 * @since 0.9.5
	 * @param int    $prayer_id Name attribute of the dropdown.
	 * @param int    $selected  Prayer to select automatically.
	 * @param string $status    Prayer status to retrieve.
	 *
	 * @return string $output   Prayer dropdown.
	 */
	public function prayer_dropdown( $prayer_id, $selected = 0, $status = '' ) {
		$args = array( 'nopaging' => true );

		if ( ! empty( $status ) ) {
			$args['status'] = $status;
		}

		$prayers = intercessor_get_items( 'prayer', $args );
		$options = [];

		if ( $prayers ) {
			foreach ( $prayers as $prayer ) {
				$options[ absint( $prayer->id ) ] = esc_html( get_the_title( $prayer->id ) );
			}
		} else {
			$options[0] = esc_html__( 'No prayers found', 'intercessor' );
		}

		return $this->select(
			array(
				'name'             => 'Prayers',
				'selected'         => $selected,
				'options'          => $options,
				'show_option_all'  => false,
				'show_option_none' => esc_html__( 'Select a prayer', 'intercessor' ),
			)
		);
	}

	/**
	 * Renders an HTML Dropdown of all Requesters
	 *
	 * @param array $args Array of arguments.
	 *
	 * @access public
	 * @since 0.9.5
	 * @return string $output Requester dropdown
	 */
	public function requester_dropdown( array $args = [] ) {

		// Array of arguments.
		$defaults = array(
			'name'        => 'Requesters',
			'id'          => 'Requesters',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => true,
			'placeholder' => esc_html__( 'Select a Requester', 'intercessor' ),
			'number'      => 30,
			'data'        => array(
				'search-type'        => 'requester',
				'search-placeholder' => esc_html__( 'Type to search all Requesters', 'intercessor' )
			),
		);

		// Parse arguments.
		$args = wp_parse_args( $args, $defaults );

		// Get requesters.
		$requesters = intercessor_get_items( 'requester', ['number' => $args['number']] );

		$options = [];

		if ( $requesters ) {
			$options[0] = esc_html__( 'No requester attached', 'intercessor' );
			foreach ( $requesters as $requester ) {
				$options[ absint( $requester->id ) ] = esc_html( $requester->name . ' (' . $requester->email . ')' );
			}
		} else {
			$options[0] = esc_html__( 'No Requesters found', 'intercessor' );
		}

		if ( ! empty( $args['selected'] ) ) {

			// Ensure selected requester is in the initial list of Requesters displayed.

			if ( ! array_key_exists( $args['selected'], $options ) ) {

				$requester = new Requester( $args['selected'] );

				if ( $requester ) {

					$options[ absint( $args['selected'] ) ] = esc_html( $requester->name . ' (' . $requester->email . ')' );

				}

			}

		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'] . ' intercessor-requester-select',
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'chosen'           => $args['chosen'],
			'show_option_all'  => false,
			'show_option_none' => false,
			'data'             => $args['data'],
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Users
	 *
	 * @access public
	 * @since 0.9.5
	 * @param array $args
	 * @return string $output User dropdown
	 */
	public function user_dropdown( $args = [] ) {

		$defaults = array(
			'name'        => 'users',
			'id'          => 'users',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => true,
			'placeholder' => esc_html__( 'Select a User', 'intercessor' ),
			'number'      => 30,
			'data'        => array(
				'search-type'        => 'user',
				'search-placeholder' => esc_html__( 'Type to search all Users', 'intercessor' ),
			),
		);

		$args = wp_parse_args( $args, $defaults );


		$user_args = array(
			'number' => $args['number'],
		);
		$users     = \get_users( $user_args );
		$options   = [];

		if ( $users ) {
			foreach ( $users as $user ) {
				$options[ $user->ID ] = esc_html( $user->display_name );
			}
		} else {
			$options[0] = esc_html__( 'No users found', 'intercessor' );
		}

		// If a selected user has been specified, we need to ensure it's in the initial list of user displayed
		if ( ! empty( $args['selected'] ) ) {

			if ( ! array_key_exists( $args['selected'], $options ) ) {

				$user = \get_userdata( $args['selected'] );

				if ( $user ) {
					$options[ absint( $args['selected'] ) ] = esc_html( $user->display_name );
				}
			}
		}

		$output = $this->select(
			[
				'name'             => $args['name'],
				'selected'         => $args['selected'],
				'id'               => $args['id'],
				'class'            => $args['class'] . ' intercessor-user-select',
				'options'          => $options,
				'multiple'         => $args['multiple'],
				'placeholder'      => $args['placeholder'],
				'chosen'           => $args['chosen'],
				'show_option_all'  => false,
				'show_option_none' => false,
				'data'             => $args['data'],
			]
		);

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of years
	 *
	 * @access public
	 * @since 0.9.5
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Year to select automatically
	 * @param int    $years_before Number of years before the current year the dropdown should start with
	 * @param int    $years_after Number of years after the current year the dropdown should finish at
	 * @return string $output Year dropdown
	 */
	public function year_dropdown( $name = 'year', $selected = 0, $years_before = 5, $years_after = 0 ) {
		$current     = date( 'Y' );
		$start_year  = $current - absint( $years_before );
		$end_year    = $current + absint( $years_after );
		$selected    = empty( $selected ) ? date( 'Y' ) : $selected;
		$options     = [];

		while ( $start_year <= $end_year ) {
			$options[ absint( $start_year ) ] = $start_year;
			$start_year++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of months
	 *
	 * @access public
	 * @since 0.9.5
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Month to select automatically
	 * @return string $output Month dropdown
	 */
	public function month_dropdown( $name = 'month', $selected = 0 ) {
		$month   = 1;
		$options = [];
		$selected = empty( $selected ) ? date( 'n' ) : $selected;

		while ( $month <= 12 ) {
			$options[ absint( $month ) ] = \intercessor_get_month_name( $month );
			$month++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown or Select box
	 *
	 * @since 0.9.5
	 *
	 * @param array $args Array of arguments.
	 *
	 * @return string
	 */
	public function select( $args = [] ) {
		$defaults = array(
			'options'          => [],
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => [],
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'intercessor' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'intercessor' ),
			'data'             => [],
			'readonly'         => false,
			'disabled'         => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$data_elements = '';
		foreach ( $args['data'] as $key => $value ) {
			$data_elements .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		if ( $args['multiple'] ) {
			$multiple = ' MULTIPLE';
		} else {
			$multiple = '';
		}

		if ( $args['chosen'] ) {
			$args['class'] .= ' intercessor-select-chosen';
			if ( is_rtl() ) {
				$args['class'] .= ' chosen-rtl';
			}
		}

		if ( $args['placeholder'] ) {
			$placeholder = $args['placeholder'];
		} else {
			$placeholder = '';
		}

		if ( isset( $args['readonly'] ) && $args['readonly'] ) {
			$readonly = ' readonly="readonly"';
		} else {
			$readonly = '';
		}

		if ( isset( $args['disabled'] ) && $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		} else {
			$disabled = '';
		}

		$settings = new Admin\Settings();
		$class    = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$output   = '<select' . $disabled . $readonly . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $settings->sanitize_key( str_replace( '-', '_', $args['id'] ) ) ) . '" class="intercessor-select ' . $class . '"' . $multiple . ' data-placeholder="' . $placeholder . '"'. $data_elements . '>';
		$selected = '';

		if ( ! isset( $args['selected'] ) || ( is_array( $args['selected'] ) && empty( $args['selected'] ) ) || ! $args['selected'] ) {
			$selected = "";
		}

		if ( $args['show_option_all'] ) {
			if ( $args['multiple'] && ! empty( $args['selected'] ) ) {
				$selected = selected( true, in_array( 0, $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], 0, false );
			}
			$output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>';
		}

		if ( ! empty( $args['options'] ) ) {
			if ( $args['show_option_none'] ) {
				if ( $args['multiple'] ) {
					$selected = selected( true, in_array( -1, $args['selected'] ), false );
				} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) && ! empty( $args['selected'] ) ) {
					$selected = selected( $args['selected'], -1, false );
				}
				$output .= '<option value="-1"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>';
			}

			foreach ( $args['options'] as $key => $option ) {
				if ( $args['multiple'] && is_array( $args['selected'] ) ) {
					$selected = selected( true, in_array( (string) $key, $args['selected'] ), false );
				} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) ) {
					$selected = selected( $args['selected'], $key, false );
				}

				$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Renders an HTML Checkbox
	 *
	 * @since 0.9.5
	 *
	 * @param array $args
	 *
	 * @return string Checkbox HTML code
	 */
	public function checkbox( $args = [] ) {
		$defaults = array(
			'name'     => null,
			'current'  => null,
			'class'    => 'intercessor-checkbox',
			'options'  => array(
				'disabled' => false,
				'readonly' => false
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$options = '';
		if ( ! empty( $args['options']['disabled'] ) ) {
			$options .= ' disabled="disabled"';
		} elseif ( ! empty( $args['options']['readonly'] ) ) {
			$options .= ' readonly';
		}

		$output = '<input type="checkbox"' . $options . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" class="' . $class . ' ' . esc_attr( $args['name'] ) . '" ' . checked( 1, $args['current'], false ) . ' />';

		return $output;
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @since 0.9.5
	 *
	 * @param array $args Arguments for the text field
	 * @return string Text field
	 */
	public function text( $args = [] ) {
		// Backwards compatibility
		if ( func_num_args() > 1 ) {
			$args = func_get_args();

			$name  = $args[0];
			$value = isset( $args[1] ) ? $args[1] : '';
			$label = isset( $args[2] ) ? $args[2] : '';
			$desc  = isset( $args[3] ) ? $args[3] : '';
		}

		$defaults = array(
			'id'           => '',
			'name'         => isset( $name )  ? $name  : 'text',
			'value'        => isset( $value ) ? $value : null,
			'label'        => isset( $label ) ? $label : null,
			'desc'         => isset( $desc )  ? $desc  : null,
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => '',
			'data'         => false
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$data     = '';
		$settings = new Admin\Settings();

		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $key => $value ) {
				$data .= 'data-' . $settings->sanitize_key( $key ) . '="' . esc_attr( $value ) . '" ';
			}
		}

		$output = '<span id="intercessor-' . $settings->sanitize_key( $args['name'] ) . '-wrap">';
			if ( ! empty( $args['label'] ) ) {
				$output .= '<label class="intercessor-label" for="' . $settings->sanitize_key( $args['id'] ) . '">' . esc_html( $args['label'] ) . '</label>';
			}

			if ( ! empty( $args['desc'] ) ) {
				$output .= '<span class="intercessor-description">' . esc_html( $args['desc'] ) . '</span>';
			}

			$output .= '<input type="text" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] )  . '" autocomplete="' . esc_attr( $args['autocomplete'] )  . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $data . '' . $disabled . '/>';

		$output .= '</span>';

		return $output;
	}
	/**
	 * Renders a date picker
	 *
	 * @since 0.9.5
	 *
	 * @param array $args Arguments for the text field
	 * @return string Datepicker field
	 */
	public function date_field( $args = [] ) {

		if ( empty( $args['class'] ) ) {
			$args['class'] = 'intercessor_datepicker';
		} elseif ( ! strpos( $args['class'], 'intercessor_datepicker' ) ) {
			$args['class'] .= ' intercessor_datepicker';
		}

		return $this->text( $args );
	}

	/**
	 * Renders an HTML textarea
	 *
	 * @since 0.9.5
	 *
	 * @param array $args Arguments for the textarea
	 * @return string textarea
	 */
	public function textarea( $args = [] ) {
		$defaults = array(
			'name'        => 'textarea',
			'value'       => null,
			'label'       => null,
			'desc'        => null,
			'class'       => 'large-text',
			'disabled'    => false
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$settings = new Admin\Settings();
		$output   = '<span id="intercessor-' . $settings->sanitize_key( $args['name'] ) . '-wrap">';

			if ( ! empty( $args['label'] ) ) {
				$output .= '<label class="intercessor-label" for="' . $settings->sanitize_key( $args['name'] ) . '" > ' . esc_html( $args['label'] ) . '</label>';
			}

			$output .= '<textarea name="' . esc_attr( $args['name'] ) . '" id="' . $settings->sanitize_key( $args['name'] ) . '" class="' . $class . ' " ' . $disabled . ' >' . esc_attr( $args['value'] ) . '</textarea>';

			if ( ! empty( $args['desc'] ) ) {
				$output .= '<span class="intercessor-description">' . esc_html( $args['desc'] ) . '</span>';
			}

		$output .= '</span>';

		return $output;
	}

	/**
	 * Renders an ajax user search field
	 *
	 * @since 0.9.5
	 *
	 * @param array $args
	 * @return string text field with ajax search
	 */
	public function ajax_user_search( $args = [] ) {

		$defaults = array(
			'name'         => 'user_id',
			'value'        => null,
			'placeholder'  => esc_html__( 'Enter username', 'intercessor' ),
			'label'        => null,
			'desc'         => null,
			'class'        => '',
			'disabled'     => false,
			'autocomplete' => 'off',
			'data'         => false
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] = 'intercessor-ajax-user-search ' . $args['class'];

		$output  = '<span class="intercessor_user_search_wrap">';
			$output .= $this->text( $args );
			$output .= '<span class="intercessor_user_search_results hidden"><a class="intercessor-ajax-user-cancel" aria-label="' . esc_html__( 'Cancel', 'intercessor' ) . '" href="#">x</a><span></span></span>';
		$output .= '</span>';

		return $output;
	}


	/**
	 * Renders an HTML Dropdown of all the Prayers
	 *
	 * @since 1.0.0
	 *
	 * @param string $name     Name attribute of the dropdown.
	 * @param int    $selected Prayer to select automatically.
	 * @param string $status   Prayer post_status to retrieve.
	 *
	 * @return string $output Prayer dropdown
	 */
	public function prayers_dropdown( $name = 'intercessor_prayers', $selected = 0, $status = '' ) {
		$defaults = array(
			'name'            => 'prayers',
			'id'              => 'prayers',
			'class'           => '',
			'multiple'        => false,
			'selected'        => 0,
			'chosen'          => true,
			'placeholder'     => esc_html__( 'Choose a Prayer', 'intercessor' ),
			'show_option_all' => esc_html__( 'All Prayers', 'intercessor' ),
			'number'          => 30,
			'data'            => array(
				'search-type'        => 'prayer',
				'search-placeholder' => esc_html__( 'Search Prayers', 'intercessor' ),
			),
		);

		$args = func_get_args();

		if ( 1 === func_num_args() && is_array( $args[0] ) ) {
			$args = wp_parse_args( $args[0], $defaults );
		} else {
			$args = wp_parse_args( array(
				'name'     => $name,
				'selected' => $selected,
				'nopaging' => true,
			), $defaults );
		}

		$prayer_args = array(
			'number' => $args['number'],
		);

		if ( ! empty( $status ) ) {
			$prayer_args['status'] = $status;
		}

		$prayers = intercessor_get_items( 'prayer', $prayer_args );
		$options   = array();

		if ( $prayers ) {
			foreach ( $prayers as $prayer ) {
				$options[ absint( $prayer->id ) ] = esc_html( $prayer->name );
			}
		} else {
			$options[0] = esc_html__( 'No prayers found', 'intercessor' );
		}

		// Return prayers dropdown.
		return $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'] . ' intercessor-user-select',
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'chosen'           => $args['chosen'],
			'show_option_all'  => $args['show_option_all'],
			'show_option_none' => false,
		) );
	}

}
