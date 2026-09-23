wp.customize.controlConstructor['kirki-box-shadow'] = wp.customize.Control.extend({

	presets: {
		none: '0px 0px 0px 0px rgba(0,0,0,0)',
		soft: '4px 0px 14px 0px rgba(0,0,0,0.10)',
		medium: '8px 0px 24px -4px rgba(0,0,0,0.16)',
		strong: '12px 0px 35px -8px rgba(0,0,0,0.28)'
	},

	defaultParts: {
		horizontalLength: '0',
		verticalLength: '0',
		blurRadius: '0',
		spreadRadius: '0',
		color: 'rgba(0,0,0,0)',
		inset: ''
	},

	isSyncingColor: false,

	/**
	 * Triggered when the control is ready.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	ready: function() {
		this.setShadowValue( this.getRawValue(), false );
		this.setVisually();
		this.initColorpicker();
		this.bindEvents();
	},

	/**
	 * Bind field events.
	 *
	 * @returns {void}
	 */
	bindEvents: function() {
		var control = this;

		control.container.find( '.shadow-preset' ).on( 'change', function() {
			var preset = jQuery( this ).val();

			if ( 'custom' === preset || ! control.presets[ preset ] ) {
				return;
			}

			control.setShadowValue( control.presets[ preset ] );
			control.setVisually();
		});

		control.container.find( '.shadow-part' ).on( 'input change', function() {
			control.updatePartValue( jQuery( this ).data( 'context' ), jQuery( this ).val() );
		});

		control.container.find( '.kirki-color-control' ).on( 'change', function() {
			if ( control.isSyncingColor ) {
				return;
			}

			control.updatePartValue( 'color', jQuery( this ).val() );
		});

		control.container.find( 'input[data-context="inset"]' ).on( 'click change', function() {
			control.updatePartValue( 'inset', jQuery( this ).is( ':checked' ) ? 'inset' : '' );
		});
	},

	/**
	 * Init the colorpicker.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	initColorpicker: function() {
		var control = this,
			picker = control.container.find( '.kirki-color-control' ),
			clear;

		setTimeout( function() {
			clear = control.container.find( '.kirki-input-container .wp-picker-clear' );
			if ( clear.length ) {
				clear.on( 'click', function() {
					control.updatePartValue( 'color', 'rgba(0,0,0,0)' );
				});
			}
		}, 200 );

		picker.wpColorPicker({
			change: function() {
				setTimeout( function() {
					if ( control.isSyncingColor ) {
						return;
					}

					control.updatePartValue( 'color', picker.val() );
				}, 20 );
			}
		});
	},

	/**
	 * Get the current raw value.
	 *
	 * @returns {string}
	 */
	getRawValue: function() {
		var inputValue = this.container.find( '.hidden-value' ).val() || this.container.find( '.hidden-value' ).attr( 'value' ),
			settingValue = this.setting && this.setting.get ? this.setting.get() : '',
			defaultValue = this.params && this.params.default ? this.params.default : '';

		return String( inputValue || settingValue || defaultValue || this.presets.none );
	},

	/**
	 * Gets a part from the string value depending on the context.
	 *
	 * @since 1.0.1
	 * @param {string} part  - The requested part.
	 * @param {string} value - The string value.
	 * @returns {string}
	 */
	getFromStringValue: function( part, value ) {
		return this.parseShadowValue( value || this.getRawValue() )[ part ];
	},

	/**
	 * Parse a CSS box-shadow value into editable parts.
	 *
	 * @param {string} value - CSS box-shadow value.
	 * @returns {Object}
	 */
	parseShadowValue: function( value ) {
		var parts = jQuery.extend( {}, this.defaultParts ),
			match;

		value = String( value || '' ).trim();

		if ( ! value || 'none' === value ) {
			return parts;
		}

		if ( /^inset\b/i.test( value ) ) {
			parts.inset = 'inset';
			value = value.replace( /^inset\s+/i, '' );
		}

		if ( /\sinset$/i.test( value ) ) {
			parts.inset = 'inset';
			value = value.replace( /\s+inset$/i, '' );
		}

		match = value.match( /^(-?\d*\.?\d+)(?:px)?\s+(-?\d*\.?\d+)(?:px)?\s+(-?\d*\.?\d+)(?:px)?(?:\s+(-?\d*\.?\d+)(?:px)?)?\s+(.+)$/i );

		if ( ! match ) {
			return parts;
		}

		parts.horizontalLength = this.formatNumber( match[1], parts.horizontalLength );
		parts.verticalLength = this.formatNumber( match[2], parts.verticalLength );
		parts.blurRadius = this.formatNumber( match[3], parts.blurRadius, 0 );
		parts.spreadRadius = this.formatNumber( match[4], parts.spreadRadius );
		parts.color = this.formatColor( match[5] );

		return parts;
	},

	/**
	 * Visually updates the UI.
	 *
	 * @since 1.0
	 * @returns {Object} this
	 */
	setVisually: function() {
		var value = this.getRawValue(),
			parts = this.parseShadowValue( value );

		this.container.find( '.horizontal-length' ).val( parts.horizontalLength );
		this.container.find( '.vertical-length' ).val( parts.verticalLength );
		this.container.find( '.blur-radius' ).val( parts.blurRadius );
		this.container.find( '.spread-radius' ).val( parts.spreadRadius );
		this.syncColorControl( parts.color );
		this.container.find( '.inset' ).prop( 'checked', !! parts.inset );
		this.container.find( '.shadow-preset' ).val( this.getPresetByValue( this.buildValue( parts ) ) );

		return this;
	},

	/**
	 * Sync the color picker UI without marking the control as changed.
	 *
	 * @param {string} color - CSS color value.
	 * @returns {void}
	 */
	syncColorControl: function( color ) {
		var control = this,
			picker = this.container.find( '.kirki-color-control' );

		if ( picker.hasClass( 'wp-color-picker' ) && picker.wpColorPicker ) {
			control.isSyncingColor = true;
			picker.wpColorPicker( 'color', color );
			setTimeout( function() {
				control.isSyncingColor = false;
			}, 50 );
			return;
		}

		picker.val( color );
	},

	/**
	 * Updates the value.
	 *
	 * @since 1.0.1
	 * @param {string} param    - Which parameter has changed in the value.
	 * @param {string} paramVal - The parameter's value.
	 * @returns {void}
	 */
	updatePartValue: function( param, paramVal ) {
		var valueParts = this.parseShadowValue( this.getRawValue() );

		valueParts[ param ] = paramVal;
		this.setShadowValue( this.buildValue( valueParts ) );

		if ( 'inset' === param ) {
			this.container.find( '.inset' ).prop( 'checked', !! paramVal );
			this.container.find( '.shadow-preset' ).val( this.getPresetByValue( this.buildValue( valueParts ) ) );
			return;
		}

		this.setVisually();
	},

	/**
	 * Save a full box-shadow value.
	 *
	 * @param {string} value          - CSS box-shadow value.
	 * @param {bool}   triggerSetting - Whether to update the Customizer setting.
	 * @returns {void}
	 */
	setShadowValue: function( value, triggerSetting ) {
		var input = this.container.find( '.hidden-value' );

		triggerSetting = ( false !== triggerSetting );
		value = this.buildValue( this.parseShadowValue( value ) );

		input.val( value ).attr( 'value', value );

		if ( triggerSetting ) {
			if ( this.setting && this.setting.set ) {
				this.setting.set( value );
			}

			input.trigger( 'change' );
		}
	},

	/**
	 * Build CSS box-shadow value from parts.
	 *
	 * @param {Object} parts - Parsed box-shadow parts.
	 * @returns {string}
	 */
	buildValue: function( parts ) {
		var value,
			horizontalLength = this.formatNumber( parts.horizontalLength, this.defaultParts.horizontalLength ),
			verticalLength = this.formatNumber( parts.verticalLength, this.defaultParts.verticalLength ),
			blurRadius = this.formatNumber( parts.blurRadius, this.defaultParts.blurRadius, 0 ),
			spreadRadius = this.formatNumber( parts.spreadRadius, this.defaultParts.spreadRadius ),
			color = this.formatColor( parts.color );

		value = horizontalLength + 'px ' + verticalLength + 'px ' + blurRadius + 'px ' + spreadRadius + 'px ' + color;

		if ( parts.inset ) {
			value = 'inset ' + value;
		}

		return value;
	},

	/**
	 * Format a numeric CSS length value.
	 *
	 * @param {string|number} value    - Raw value.
	 * @param {string}        fallback - Fallback value.
	 * @param {number}        min      - Optional minimum.
	 * @returns {string}
	 */
	formatNumber: function( value, fallback, min ) {
		var number = parseFloat( value );

		if ( isNaN( number ) ) {
			number = parseFloat( fallback );
		}

		if ( 'number' === typeof min && number < min ) {
			number = min;
		}

		if ( Math.round( number ) === number ) {
			return String( number );
		}

		return String( parseFloat( number.toFixed( 2 ) ) );
	},

	/**
	 * Format color value.
	 *
	 * @param {string} value - Raw color value.
	 * @returns {string}
	 */
	formatColor: function( value ) {
		value = String( value || '' ).trim();

		if ( ! value ) {
			return this.defaultParts.color;
		}

		return value.replace( /\s*,\s*/g, ',' );
	},

	/**
	 * Resolve preset by current value.
	 *
	 * @param {string} value - CSS box-shadow value.
	 * @returns {string}
	 */
	getPresetByValue: function( value ) {
		var preset;

		value = this.normalizeValue( value );

		for ( preset in this.presets ) {
			if ( this.normalizeValue( this.presets[ preset ] ) === value ) {
				return preset;
			}
		}

		return 'custom';
	},

	/**
	 * Normalize value for comparisons.
	 *
	 * @param {string} value - CSS box-shadow value.
	 * @returns {string}
	 */
	normalizeValue: function( value ) {
		return String( value || '' ).replace( /\s*,\s*/g, ',' ).replace( /\s+/g, ' ' ).trim().toLowerCase();
	}
});
