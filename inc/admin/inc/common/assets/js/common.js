(function ( $ ) {
	'use strict';

	if ( typeof qodefWooFramework !== 'object' ) {
		window.qodefWooFramework = {};
	}

	qodefWooFramework.scroll       = 0;
	qodefWooFramework.windowWidth  = $( window ).width();
	qodefWooFramework.windowHeight = $( window ).height();

	$( document ).ready(
		function () {
			var $mainHolder      = $( '.qodef-page-v4-product-bundles' ),
				$adminPageHolder = $( '.qodef-admin-page-v4' ),
				$cptPageHolder   = $( '.qodef-cpt-page' );

			if ( $mainHolder.length ) {
				qodefTabs.init( $mainHolder );
				qodefDependency.init( $mainHolder );
				qodefRepeater.init( $mainHolder );

				qodefAdminOptionsPanel.init();

				qodefInitMediaUploader.init( $mainHolder );
				qodefColorPicker.init( $mainHolder );
				qodefDatePicker.init( $mainHolder );
				qodefSelect2.init( $mainHolder );
				qodefInitIconPicker.init( $mainHolder );

				if ( $adminPageHolder.length ) {
					qodefSearchOptions.init( $adminPageHolder );
				}

				qodefAddressFields.init( $mainHolder );

				qodefReinitRepeaterFields.init();
				qodefWidgetFields.initColorPicker();
			} else if ( $cptPageHolder.length ) {
				// If there is no main holder, but there is a cpt holder, admin needs to reinit for header and navigation.
				qodefAdminOptionsPanel.adminReInit();
			}
		}
	);

	$( window ).scroll(
		function () {
			qodefWooFramework.scroll = $( window ).scrollTop();
		}
	);

	$( window ).resize(
		function () {
			qodefWooFramework.windowWidth  = $( window ).width();
			qodefWooFramework.windowHeight = $( window ).height();

			if ( qodefWooFramework.windowWidth > 600 &&
				typeof qodefAdminOptionsPanel.adminPage !== 'undefined' &&
				qodefAdminOptionsPanel.adminPage.length &&
				typeof qodefAdminOptionsPanel.adminHeader !== 'undefined' &&
				qodefAdminOptionsPanel.adminHeader.length ) {
				qodefAdminOptionsPanel.adminHeader.css(
					'width',
					qodefAdminOptionsPanel.adminPage.width()
				);
			}
		}
	);

	$( document ).on(
		'widget-added widget-updated',
		function ( event, widget ) {
			var $widgetID = typeof widget !== 'undefined' ? widget.find( '[name="id_base"]' ) : '';

			if ( $widgetID && $widgetID.val().includes( 'product-bundles' ) ) {
				qodefWidgetFields.initColorPicker( widget );
				qodefWidgetFields.initDependency( widget );
			}
		}
	);

	$( document ).on(
		'ajaxSuccess',
		function ( event, xhr, options ) {

			// Edit tags dashboard page event.
			if ( options && options.data && -1 !== options.data.indexOf( 'action=add-tag' ) ) {
				var $colorFields = $( '.qodef-field-color' );

				if ( $colorFields.length ) {
					$colorFields.each(
						function () {
							var clearButton = $( this ).find( '.wp-picker-clear' );

							if ( clearButton.length ) {
								clearButton.trigger(
									'click'
								);
							}
						}
					);
				}
			}
		}
	);

	var qodefTabs = {
		init: function ( $mainHolder ) {
			this.holder = $mainHolder.filter( '.qodef-tab-wrapper' );

			if ( this.holder.length ) {
				this.holder.each(
					function () {
						qodefTabs.initTabs( $mainHolder, $( this ) );
						var $tabsWrapper = $( this ).find( '.qodef-tab-item-nav-wrapper' );
						qodefWooFramework.qodefPerfectScrollbar.init( $tabsWrapper, false );
						qodefTabs.initTabsDrag( $tabsWrapper );
					}
				);
			}
		},
		initTabs: function ( $mainHolder, tabs ) {
			tabs.children( '.qodef-tab-item-content' ).each(
				function ( index ) {
					index = index + 1;

					var $that    = $( this ),
						link     = $that.attr( 'id' ),
						$navItem = $that.parent().find( '.qodef-tab-item-nav-wrapper li:nth-child(' + index + ') a' ),
						navLink  = $navItem.attr( 'href' );

					link = '#' + link;

					if ( link.indexOf( navLink ) > -1 ) {
						$navItem.attr( 'href', link );
					}
				}
			);

			tabs.addClass( 'qodef--init' ).tabs(
				{
					activate: function () {
						// This peace of code is required in order to re init maps for address field type when it's inside tabs layout.
						if ( typeof qodefWooFramework.qodefAddressFields === 'object' ) {
							qodefWooFramework.qodefAddressFields.init( $mainHolder, true );
						}

						$( document.body ).trigger( 'qode_product_bundles_for_woocommerce_trigger_tab_change' );
					}
				}
			);
		},
		initTabsDrag: function ( $tabsWrapper ) {
			var isDown = false;
			var startX;
			var scrollLeft;

			$tabsWrapper.on(
				'mousedown',
				function ( e ) {
					e.preventDefault();
					isDown = true;
					$tabsWrapper.addClass( 'qodef-drag' );
					startX     = e.pageX - $tabsWrapper[0].offsetLeft;
					scrollLeft = $tabsWrapper[0].scrollLeft;
				}
			);
			$tabsWrapper.on(
				'mousewheel',
				function ( event, delta ) {
					$tabsWrapper[0].scrollLeft  -= (delta * 20);
					$tabsWrapper[0].scrollRight -= (delta * 20);
					event.preventDefault();
				}
			);
			$tabsWrapper.on(
				'mouseleave',
				function () {
					isDown = false;
					$tabsWrapper.removeClass( 'qodef-drag' );
				}
			);
			$tabsWrapper.on(
				'mouseup',
				function () {
					isDown = false;
					$tabsWrapper.removeClass( 'qodef-drag' );
				}
			);
			$tabsWrapper.on(
				'mousemove',
				function ( e ) {
					if ( ! isDown ) {
						return;
					}
					e.preventDefault();
					var x                      = e.pageX - $tabsWrapper[0].offsetLeft;
					var walk                   = (x - startX);
					$tabsWrapper[0].scrollLeft = scrollLeft - walk;
				}
			);
		}
	};

	var qodefDependency = {
		init: function ( $mainHolder ) {
			qodefDependency.initOptions( $mainHolder );
			qodefDependency.initMenu();
			qodefDependency.initWidget();
			qodefDependency.initProductAttributeTypeSelectBox();
		},
		initOptions: function ( $mainHolder ) {
			var $dependencyOptions = $mainHolder.find( '.qodef-field-content .qodef-field[data-option-name]' );
			if ( $dependencyOptions.length ) {
				qodefDependency.initFields( $dependencyOptions );
			}
		},
		initMenu: function () {
			var $dependencyOptions = $( '#update-nav-menu .qodef-menu-item-field[data-option-name]' );

			if ( $dependencyOptions.length ) {
				qodefDependency.initFields( $dependencyOptions );
			}
		},
		initWidget: function () {
			var $dependencyOptions = $( '.widget-content .qodef-widget-field[data-option-name]' );
			if ( $dependencyOptions.length ) {
				$dependencyOptions.each(
					function () {
						var $option = $( this );

						if ( $option.parents( '#widget-list' ).length <= 0 ) {
							qodefDependency.initField( $option );
						}
					}
				);
			}
		},
		reinitRepeater: function ( $mainHolder ) {
			var $dependencyOptions = $mainHolder.find( '.qodef-repeater-fields-holder .qodef-field-content .qodef-field[data-option-name]' );

			if ( $dependencyOptions.length ) {
				$dependencyOptions.each(
					function () {
						var $thisOption    = $( this );
						var thisOptionType = $thisOption.data( 'option-type' );

						switch (thisOptionType) {
							case 'selectbox':
								qodefDependency.qodefSelectBoxDependencyRepeater( $thisOption );
								break;
							case 'radiogroup':
								qodefDependency.qodefRadioGroupDependencyRepeater( $thisOption );
								break;
						}
						qodefDependency.initField( $thisOption );
					}
				);
			}
		},
		reinitWidget: function ( widgetDependencyFields ) {
			qodefDependency.initFields( widgetDependencyFields );
		},
		initFields: function ( fields ) {
			fields.each(
				function () {
					var $thisOption = $( this );

					if ( $thisOption.parents( '.qodef-repeater-template' ).length <= 0 ) {
						qodefDependency.initField( $thisOption );
					}
				}
			);
		},
		initField: function ( thisOption ) {
			var thisOptionType = thisOption.data( 'option-type' );

			if ( ! thisOption.hasClass( 'qodef-dependency-option' ) ) {
				thisOption.addClass( 'qodef-dependency-option' );

				switch (thisOptionType) {
					case 'selectbox':
						qodefDependency.qodefSelectBoxDependency( thisOption );
						break;
					case 'radiogroup':
						qodefDependency.qodefRadioGroupDependency( thisOption );
						break;
					case 'yesno':
						qodefDependency.qodefRadioGroupDependency( thisOption );
						break;
					case 'checkbox':
						qodefDependency.qodefCheckBoxDependency( thisOption );
						break;
				}
			}
		},
		qodefSelectBoxDependency: function ( option ) {
			option.on(
				'change',
				function () {
					var optionValue = $( this ).val();
					qodefDependency.qodefDependencyActionInit(
						option,
						optionValue
					);
				}
			);
			option.trigger( 'change' );
		},
		qodefSelectBoxDependencyRepeater: function ( option ) {
			var repeaterOptionValue = option.val();
			qodefDependency.qodefDependencyActionInit(
				option,
				repeaterOptionValue
			);
		},
		qodefRadioGroupDependency: function ( option ) {
			var optionName = option.data( 'option-name' ),
				radioItem  = option.find( 'input[name="' + optionName + '"]' );

			radioItem.on(
				'change',
				function () {
					var optionValue = this.value;
					qodefDependency.qodefDependencyActionInit(
						option,
						optionValue
					);
				}
			);
			qodefDependency.qodefDependencyActionInit(
				option,
				option.find( 'input[name="' + option.data( 'option-name' ) + '"]:checked' ).val()
			);
		},
		qodefRadioGroupDependencyRepeater: function ( option ) {
			var optionName          = option.data( 'option-name' ),
				radioItem           = option.find( 'input[name="' + optionName + '"]' ),
				repeaterOptionValue = radioItem.value;
			qodefDependency.qodefDependencyActionInit(
				option,
				repeaterOptionValue
			);
		},
		qodefCheckBoxDependency: function ( option ) {
			option.on(
				'click',
				function () {
					var $thisOption = $( this );
					var optionValue = $thisOption.val();

					if ( $thisOption.is( ':checked' ) ) {
						optionValue += '-checked';
					}

					qodefDependency.qodefDependencyActionInit(
						option,
						optionValue
					);
				}
			);
		},
		qodefDependencyActionInit: function ( option, optionValue ) {
			var dependencyHolder = $( '.qodef-dependency-holder' ),
				optionName       = option.data( 'option-name' );

			if ( option.prop( 'id' ) === 'attribute_type' ) {
				optionName = option.attr( 'name' );
			}

			if ( dependencyHolder.length && optionName !== undefined && optionName !== '' && optionValue !== undefined ) {
				dependencyHolder.each(
					function () {
						var $thisHolder     = $( this ),
							showDataItems   = $thisHolder.data( 'show' ),
							hideDataItems   = $thisHolder.data( 'hide' ),
							relationData    = $thisHolder.data( 'relation' ),
							relation        = 'and',
							dependencyItems = '',
							visibility      = true;

						if ( showDataItems !== '' && showDataItems !== undefined ) {
							dependencyItems = showDataItems;
						}

						if ( hideDataItems !== '' && hideDataItems !== undefined ) {
							dependencyItems = hideDataItems;
							visibility		= false;
						}

						if ( relationData !== '' && relationData !== undefined ) {
							relation = relationData;
						}

						if ( '' !== dependencyItems ) {

							if ( qodefDependency.qodefGetNumberOfItems( dependencyItems ) > 1 ) {
								qodefDependency.qodefMultipleDependencyLogic(
									dependencyItems,
									$thisHolder,
									optionName,
									optionValue,
									visibility,
									relation
								);
							} else {
								qodefDependency.qodefSingleDependencyLogic(
									dependencyItems,
									$thisHolder,
									optionName,
									optionValue,
									visibility
								);
							}
						}
					}
				);
			}
		},
		qodefGetNumberOfItems: function ( items ) {
			var numberOfItems = 0;

			for ( var item in items ) {
				if ( items.hasOwnProperty( item ) ) {
					++numberOfItems;
				}
			}

			return numberOfItems;
		},
		qodefMultipleDependencyLogic: function ( dataItems, holder, optionName, optionValue, show, relation ) {
			var flag           = [],
				itemVisibility = true;

			$.each(
				dataItems,
				function ( key, value ) {
					value = value.split( ',' );

					if ( optionName === key ) {
						if ( value.indexOf( optionValue ) !== -1 ) {
							flag.push( true );
						} else {
							flag.push( false );
						}
					} else {
						var otherOptionName = $( '.qodef-dependency-option[data-option-name="' + key + '"]' ),
							otherOptionType = otherOptionName.data( 'option-type' ),
							otherValue      = '';

						// if there is no field with key in data-option-name, try to find it as checked field.
						if ( 0 === otherOptionName.length ) {
							var checkedFlag  = [],
								checkedValue = false;

							otherOptionName = $( '.qodef-dependency-option[data-option-name^="' + key + '["]' );
							otherOptionType = otherOptionName.data( 'option-type' );

							if ( otherOptionName.length && 'checkbox' === otherOptionType ) {
								otherOptionName.each(
									function () {
										var checked = $( this ).is( ':checked' );

										if ( checked ) {
											otherValue = $( this ).val();

											if ( otherValue.length && value.indexOf( otherValue ) !== -1 ) {
												checkedFlag.push( true );
											} else {
												checkedFlag.push( false );
											}
										}

									}
								);

								for ( var f in checkedFlag ) {
									if ( checkedFlag[f] ) {
										checkedValue = true;
									}
								}
								flag.push( checkedValue );
							}
						} else {
							switch (otherOptionType) {
								case 'selectbox':
									otherValue = otherOptionName.val();
									break;
								case 'radiogroup':
									otherValue = otherOptionName.find( 'input[name="' + key + '"]:checked' ).val();
									break;
							}

							if ( otherValue.length && value.indexOf( otherValue ) !== -1 ) {
								flag.push( true );
							} else {
								flag.push( false );
							}
						}
					}
				}
			);

			if ( 'and' === relation ) {
				for ( var f in flag ) {
					if ( ! flag[f] ) {
						itemVisibility = false;
					}
				}
			} else {
				itemVisibility = false;
				for ( var f in flag ) {
					if ( flag[f] ) {
						itemVisibility = true;
						continue;
					}
				}
			}

			if ( show ) {
				if ( itemVisibility ) {
					holder.fadeIn( 200 );
				} else {
					holder.fadeOut( 200 );
				}
			} else {
				if ( itemVisibility ) {
					holder.fadeOut( 200 );
				} else {
					holder.fadeIn( 200 );
				}
			}
		},
		qodefSingleDependencyLogic: function ( dataItems, holder, optionName, optionValue, show ) {
			$.each(
				dataItems,
				function ( key, value ) {
					var checkBoxValue = typeof optionValue === 'string' ? optionValue.replace( '-checked', '' ) : '';

					if ( optionName === key ) {
						value = value.split( ',' );

						if ( show ) {
							if ( value.indexOf( optionValue ) !== -1 ) {
								holder.removeClass( 'qodef-hide-dependency-holder' );

								// For search options manipulation.
								holder.addClass( 'qodef-show-dependency-holder' );
							} else {
								holder.addClass( 'qodef-hide-dependency-holder' );

								// For search options manipulation.
								holder.removeClass( 'qodef-show-dependency-holder' );
							}
						} else {
							if ( value.indexOf( optionValue ) !== -1 ) {
								holder.addClass( 'qodef-hide-dependency-holder' );

								// For search options manipulation.
								holder.removeClass( 'qodef-show-dependency-holder' );
							} else {
								holder.removeClass( 'qodef-hide-dependency-holder' );

								// For search options manipulation.
								holder.addClass( 'qodef-show-dependency-holder' );
							}
						}
					} else if ( optionName === key + '[' + checkBoxValue + ']' && checkBoxValue === value ) {

						if ( show ) {
							if ( value.indexOf( checkBoxValue ) !== -1 && optionValue.indexOf( '-checked' ) !== -1 ) {
								holder.removeClass( 'qodef-hide-dependency-holder' );

								// For search options manipulation.
								holder.addClass( 'qodef-show-dependency-holder' );
							} else {
								holder.addClass( 'qodef-hide-dependency-holder' );

								// For search options manipulation.
								holder.removeClass( 'qodef-show-dependency-holder' );
							}
						} else {
							if ( value.indexOf( checkBoxValue ) !== -1 && optionValue.indexOf( '-checked' ) !== -1 ) {
								holder.addClass( 'qodef-hide-dependency-holder' );

								// For search options manipulation.
								holder.removeClass( 'qodef-show-dependency-holder' );
							} else {
								holder.removeClass( 'qodef-hide-dependency-holder' );

								// For search options manipulation.
								holder.addClass( 'qodef-show-dependency-holder' );
							}
						}
					}
				}
			);
		},
		initProductAttributeTypeSelectBox: function () {
			var thisOption = $( '#attribute_type' );

			if ( thisOption.length ) {
				qodefDependency.qodefSelectBoxDependency( thisOption );
			}
		},
	};

	qodefWooFramework.qodefDependency = qodefDependency;

	var qodefRepeater = {
		init: function ( $mainHolder ) {
			qodefRepeater.initRepeater( $mainHolder );
			qodefRepeater.initRepeaterInner( $mainHolder );
		},
		initRepeater: function ( $mainHolder ) {
			var repeaterHolder = $mainHolder.find( '.qodef-repeater-wrapper' );

			if ( repeaterHolder.length ) {
				repeaterHolder.each(
					function () {
						var $thisHolder = $( this );
						qodefRepeater.qodefAddNewRow( $thisHolder, $mainHolder );
						qodefRepeater.qodefToggleRow( $thisHolder );
						qodefRepeater.qodefRemoveRow( $thisHolder );
						qodefRepeater.qodefInitSortable( $thisHolder );
					}
				);
			}
		},
		initRepeaterInner: function ( $mainHolder ) {
			var repeaterInnerHolder = $mainHolder.find( '.qodef-repeater-inner-wrapper' );

			if ( repeaterInnerHolder.length ) {
				repeaterInnerHolder.each(
					function () {
						var $thisHolder = $( this );
						qodefRepeater.qodefAddNewRowInner( $thisHolder, $mainHolder );
						qodefRepeater.qodefToggleRowInner( $thisHolder );
						qodefRepeater.qodefRemoveRowInner( $thisHolder );
						qodefRepeater.qodefInitSortableInner( $thisHolder );
					}
				);
			}
		},
		qodefGetNumberOfRows: function ( holder ) {
			return holder.find( '.qodef-repeater-fields-holder' ).length;
		},
		qodefInitSortable: function ( holder ) {
			if ( holder.find( '.qodef-repeater-wrapper-main.sortable' ).length ) {
				$( '.qodef-repeater-wrapper-main.sortable' ).sortable(
					{
						placeholder: 'qodef-placeholder',
						forcePlaceholderSize: true,
						handle: '.qodef-repeater-sort'
					}
				);
			}
			qodefRepeater.qodefInitSortableInner( holder );
		},
		qodefInitSortableInner: function ( holder ) {
			if ( holder.find( '.qodef-repeater-inner-wrapper-main.sortable' ).length ) {
				$( '.qodef-repeater-inner-wrapper-main.sortable' ).sortable(
					{
						placeholder: 'qodef-placeholder',
						forcePlaceholderSize: true,
						handle: '.qodef-repeater-inner-sort'
					}
				);
			}
		},
		qodefAddNewRow: function ( holder, $mainHolder ) {
			var $addButton       = holder.find( '.qodef-repeater-add a' );
			var templateName     = holder.find( '.qodef-repeater-wrapper-main' ).data( 'template' );
			var $repeaterContent = holder.find( '.qodef-repeater-wrapper-main' );
			var repeaterTemplate = wp.template( 'qodef-repeater-template-' + templateName );

			$addButton.off().on(
				'tap click',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					var $row = $(
						repeaterTemplate(
							{
								rowIndex: qodefRepeater.qodefGetNumberOfRows( holder ) || 0
							}
						)
					);

					$repeaterContent.append( $row );
					var innerHolder = $row.find( '.qodef-repeater-inner-wrapper' );
					qodefRepeater.qodefAddNewRowInner( innerHolder, $mainHolder );
					qodefRepeater.qodefToggleRowInner( innerHolder );
					qodefRepeater.qodefRemoveRowInner( innerHolder );
					qodefRepeater.qodefInitSortable( holder );
					qodefDependency.reinitRepeater( $mainHolder );

					// need to be plugin unique.
					$( document ).trigger(
						'qode_product_bundles_for_woocommerce_add_new_row_trigger',
						$row.find( '.qodef-repeater-fields' )
					);
				}
			);
		},
		qodefToggleRow: function ( holder ) {
			var repeaterContent = holder.find( '.qodef-repeater-wrapper-main' );

			repeaterContent.on(
				'click',
				'.qodef-row-toggle',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					var $rowParent  = $( this ).parents( '.qodef-repeater-fields-holder' ),
						$rowContent = $rowParent.children( '.qodef-repeater-fields' );
					$rowContent.stop().fadeToggle( 300 );

					if ( $rowParent.hasClass( 'qodef--closed' ) ) {
						$rowParent.removeClass( 'qodef--closed' );
					} else {
						setTimeout(
							function () {
								$rowParent.addClass( 'qodef--closed' );
							},
							300
						);
					}
				}
			);
		},
		qodefRemoveRow: function ( holder ) {
			var repeaterContent = holder.find( '.qodef-repeater-wrapper-main' );

			repeaterContent.on(
				'click',
				'.qodef-clone-remove',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					if ( ! window.confirm( 'Are you sure you want to remove this section?' ) ) {
						return;
					}

					var $rowParent = $( this ).parents( '.qodef-repeater-fields-holder' );
					$rowParent.remove();
				}
			);
		},
		qodefAddNewRowInner: function ( holder, $mainHolder ) {
			var $addInnerButton   = holder.find( '.qodef-repeater-inner-add a' ),
				templateInnerName = holder.find( '.qodef-repeater-inner-wrapper-main' ).data( 'template' ),
				rowInnerTemplate  = wp.template( 'qodef-repeater-inner-template-' + templateInnerName );

			$addInnerButton.off().on(
				'click',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					var $clickedButton    = $( this ),
						$parentRow        = $clickedButton.parents( '.qodef-repeater-fields-holder' ).first(),
						parentIndex       = $parentRow.data( 'index' ),
						$rowInnerContent  = $clickedButton.parent().parent().prev(),
						lastRowInnerIndex = $parentRow.find( '.qodef-repeater-inner-fields-holder' ).length;

					var $repeaterInnerRow = $(
						rowInnerTemplate(
							{
								rowIndex: parentIndex,
								rowInnerIndex: lastRowInnerIndex
							}
						)
					);

					$rowInnerContent.append( $repeaterInnerRow );
					qodefRepeater.qodefInitSortableInner( holder );
					qodefDependency.reinitRepeater( $mainHolder );
				}
			);
		},
		qodefToggleRowInner: function ( holder ) {
			var repeaterInnerContent = holder.find( '.qodef-repeater-inner-wrapper-main' );

			repeaterInnerContent.on(
				'click',
				'.qodef-row-toggle',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					var $rowParent  = $( this ).parents( '.qodef-repeater-inner-fields-holder' ),
						$rowContent = $rowParent.children( '.qodef-repeater-inner-fields' );
					$rowContent.stop().fadeToggle( 300 );

					if ( $rowParent.hasClass( 'qodef--closed' ) ) {
						$rowParent.removeClass( 'qodef--closed' );
					} else {
						setTimeout(
							function () {
								$rowParent.addClass( 'qodef--closed' );
							},
							300
						);
					}
				}
			);
		},
		qodefRemoveRowInner: function ( holder ) {
			var repeaterInnerContent = holder.find( '.qodef-repeater-inner-wrapper-main' );

			repeaterInnerContent.on(
				'click',
				'.qodef-clone-inner-remove',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					if ( ! confirm( 'Are you sure you want to remove section?' ) ) {
						return;
					}

					var $removeButton = $( this );
					var $parent       = $removeButton.parents( '.qodef-repeater-inner-fields-holder' );

					$parent.remove();
				}
			);
		}
	};

	var qodefPerfectScrollbar = {
		init: function ( $holder, suppressScrollX ) {
			if ( $holder.length ) {
				qodefPerfectScrollbar.qodefInitScroll( $holder, typeof suppressScrollX !== 'undefined' ? suppressScrollX : true );
			}
		},
		qodefInitScroll: function ( $holder, suppressScrollX ) {
			var $defaultParams = {
				wheelSpeed: 0.6,
				suppressScrollX: suppressScrollX
			};

			var $ps = new PerfectScrollbar(
				$holder[0],
				$defaultParams
			);

			$( window ).resize(
				function () {
					$ps.update();
				}
			);
		}
	};

	qodefWooFramework.qodefPerfectScrollbar = qodefPerfectScrollbar;

	var qodefReinitRepeaterFields = {
		init: function () {
			// need to be plugin unique.
			$( document ).on(
				'qode_product_bundles_for_woocommerce_add_new_row_trigger',
				function ( event, $row ) {
					if ( typeof qodefSearchOptions.fieldHolder !== 'undefined' ) {
						qodefSearchOptions.fieldHolder.push( $row );
					}
					qodefInitMediaUploader.reinit( $row );
					qodefColorPicker.reinit( $row );
					qodefDatePicker.reinit( $row );
					qodefSelect2.reinit( $row );
					qodefInitIconPicker.reinit( $row );
				}
			);
		}
	};

	var qodefAdminOptionsPanel = {
		init: function () {
			this.adminPage = $( '.qodef-admin-page-v4' );

			if ( this.adminPage.length ) {
				this.adminHeaderPosition();
				this.navigationInit();
				this.saveOptionsInit( this.adminPage );
				this.setActivePanel();
				this.navigationReset();

				if ( qodefWooFramework.windowWidth <= 1180 ) {
					this.mobile( this.adminPage );
				}
			}
		},
		mobile: function ( $admin ) {
			var $opener          = $admin.find( '.qodef-mobile-nav-opener' ),
				$navigation      = $admin.find( '.qodef-tabs-navigation-wrapper' ),
				$navigationInner = $admin.find( '.qodef-tabs-navigation-wrapper-inner' );

			qodefWooFramework.qodefPerfectScrollbar.init( $navigationInner );

			$opener.on(
				'click tap',
				function ( e ) {
					e.preventDefault();

					if ( $navigation.hasClass( 'qodef--show' ) ) {
						$navigation.removeClass( 'qodef--show' );
						qodefAdminScroll.enable();
					} else {
						$navigation.addClass( 'qodef--show' );
						qodefAdminScroll.disable();
					}
				}
			);

			$( document ).on(
				'click',
				function ( e ) {

					if ( ! $( e.target ).closest( '.qodef-tabs-navigation-wrapper, .qodef-mobile-nav-opener' ).length ) {
						if ( $navigation.hasClass( 'qodef--show' ) ) {
							$navigation.removeClass( 'qodef--show' );
							qodefAdminScroll.enable();
						}
					}
				}
			);

		},
		navigationReset: function () {
			var urlParams = new URLSearchParams( window.location.search );
			var template  = urlParams.get( 'template' );

			if ( template !== null ) {
				this.adminPage.find( '.qodef-tabs-navigation-wrapper .navbar ul li' ).removeClass( 'qodef-active' );
			}
		},
		navigationInit: function () {
			var navigationItems = this.adminPage.find( '.qodef-tabs-navigation-wrapper .navbar ul li' );

			navigationItems.on(
				'click',
				function () {
					qodefSearchOptions.resetSearchView();
					qodefSearchOptions.resetSearchField();
					qodefAdminOptionsPanel.initTabNavItemClick( $( this ) );
					qodefAdminOptionsPanel.initNavItemClick( $( this ), true );
				}
			);
		},
		adminReInit: function () {
			this.adminPage = $( '.qodef-admin-page-v4' );
			this.adminHeaderPosition();

			if ( qodefWooFramework.windowWidth <= 1180 ) {
				this.mobile( this.adminPage );
			}

			var navigationItems = this.adminPage.find( '.qodef-tabs-navigation-wrapper .navbar ul li' );

			navigationItems.on(
				'click',
				function () {
					qodefAdminOptionsPanel.initTabNavItemClick( $( this ) );
					qodefAdminOptionsPanel.initNavItemClick( $( this ), true );
				}
			);
		},
		// Change behavior on navigation item click.
		initTabNavItemClick: function ( item ) {
			var panelName = item.find( '.nav-link' ).data( 'section' );
			var urlParams = new URLSearchParams( window.location.search );
			var template  = urlParams.get( 'template' );
			var postType  = urlParams.get( 'post_type' );
			var post      = urlParams.get( 'post' );
			var taxonomy  = urlParams.get( 'taxonomy' );

			// If we are on a page that has template, post_type or post parameters in url.
			// ergo it is not default options page than make a reload.
			if ( template !== null || postType !== null || post !== null || taxonomy !== null ) {
				this.setCookie(
					'qodefProductBundlesActiveTab',
					panelName
				);
				window.location = item.data( 'options-url' );

			}
		},
		initNavItemClick: function ( item, click_trigger ) {
			if ( item.length ) {
				var panelName = item.find( '.nav-link' ).data( 'section' );

				if ( item.hasClass( 'qodef-layout-custom' ) && ! item.hasClass( 'qodef-active' ) && click_trigger && item.data( 'options-url' ) ) {
					this.setCookie(
						'qodefProductBundlesActiveTab',
						panelName
					);

					window.location = item.data( 'options-url' );
					return;
				} else if ( item.hasClass( 'qodef-layout-custom' ) && item.hasClass( 'qodef-active' ) && click_trigger && item.data( 'options-url' ) ) {
					window.location = item.data( 'options-url' );
					return;
				}

				var $navigationPanes = this.adminPage.find( '.qodef-tabs-content' );
				var $activePane      = $navigationPanes.find( '.tab-content:visible' );
				$activePane.addClass( 'qodef-hide-pane' );

				var $newPane = $navigationPanes.find( '.tab-content[data-section=' + panelName + ']' );
				$newPane.removeClass( 'qodef-hide-pane' );

				item.siblings( '.qodef-active' ).removeClass( 'qodef-active' );
				item.addClass( 'qodef-active' );
				this.setCookie(
					'qodefProductBundlesActiveTab',
					panelName
				);

				setTimeout(
					function () {
						qodefWooFramework.qodefColorPicker.checkFieldPosition( $newPane );

						$( document.body ).on(
							'qode_product_bundles_for_woocommerce_trigger_tab_change',
							function () {
								qodefWooFramework.qodefColorPicker.checkFieldPosition( $newPane );
							}
						);
					},
					500
				);
			}
		},
		setActivePanel: function () {
			var cookie 	  = this.getCookie( 'qodefProductBundlesActiveTab' );
			var urlParams = new URLSearchParams( window.location.search );
			var post      = urlParams.get( 'post' );
			var postType  = urlParams.get( 'post_type' );

			if ( post === null && postType === null ) {
				if ( cookie !== '' && cookie !== 'undefined' ) {
					this.initNavItemClick( $( '.qodef-tabs-navigation-wrapper .nav-link[data-section=' + cookie + ']' ).parent() );
				} else {
					this.initNavItemClick( $( '.qodef-tabs-navigation-wrapper .navbar ul li:first-child' ) );
				}
			}
		},
		saveOptionsInit: function ( $adminPage ) {
			this.optionsForm = this.adminPage.find( '#qode_product_bundles_for_woocommerce_framework_ajax_form' );

			var buttonPressed,
				$saveResetLoader = $( '.qodef-save-reset-loading' ),
				$saveSuccess     = $( '.qodef-save-success' );

			if ( this.optionsForm.length ) {
				$( '.qodef-save-reset-button' ).on(
					'click',
					function () {
						buttonPressed = $( this ).attr( 'name' );
					}
				);

				this.optionsForm.on(
					'submit',
					function ( e ) {
						e.preventDefault();
						e.stopPropagation();
						$saveResetLoader.addClass( 'qodef-show-loader' );
						$adminPage.addClass( 'qodef-save-reset-disable' );

						var form          = $( this ),
							button_action = buttonPressed === 'qodef_save' ? 'qode_product_bundles_for_woocommerce_action_framework_save_options_' : 'qode_product_bundles_for_woocommerce_action_framework_reset_options_',
							ajaxData      = {
								action: button_action + form.data( 'options-name' ),
								options_name: form.data( 'options-name' )
						};

						var $formFields      = form.find( '[class*=qodef-page-v4]:not(.qodef-exclude-panel-from-saving) :input' );
						var $formNonceFields = form.find( ' > :input' );

						if ( form.siblings( ':input' ).length ) {
							$formNonceFields = form.siblings( ':input' );
						}

						$.ajax(
							{
								type: 'POST',
								url: ajaxurl,
								cache: ! 1,
								data: $.param( ajaxData, ! 0 ) + '&' + $formFields.serialize() + '&' + $formNonceFields.serialize(),
								success: function () {
									$saveResetLoader.removeClass( 'qodef-show-loader' );

									switch (buttonPressed) {
										case 'qodef_reset':
											window.location.reload( true );
											break;
										case 'qodef_save':
											$adminPage.removeClass( 'qodef-save-reset-disable' );
											$saveSuccess.fadeIn( 300 );

											setTimeout(
												function () {
													$saveSuccess.fadeOut( 200 );
												},
												2000
											);
											break;
									}
								}
							}
						);
					}
				);
			}
		},
		setCookie: function ( name, value ) {
			document.cookie = name + '=' + value;
		},
		getCookie: function ( name ) {
			var newName          = name + '=';
			var decodedCookie    = decodeURIComponent( document.cookie );
			var cookieArray      = decodedCookie.split( ';' );
			var cookieArrayCount = cookieArray.length;

			for ( var i = 0; i < cookieArrayCount; i++ ) {
				var cookie = cookieArray[i];

				while (cookie.charAt( 0 ) === ' ') {
					cookie = cookie.substring( 1 );
				}

				if ( cookie.indexOf( newName ) === 0 ) {
					return cookie.substring(
						newName.length,
						cookie.length
					);
				}
			}
			return '';
		},
		adminHeaderPosition: function () {
			this.adminPage 	 = $( '.qodef-admin-page-v4' );
			this.adminHeader = $( '.qodef-admin-header' );

			if ( this.adminPage.length && this.adminHeader.length && qodefWooFramework.windowWidth > 600 ) {
				this.adminBarHeight         = $( '#wpadminbar' ).height();
				this.adminHeaderHeight      = this.adminHeader.outerHeight( true );
				this.adminHeaderTopPosition = this.adminHeader.offset().top - parseInt( this.adminBarHeight );
				this.adminContent           = $( '.qodef-admin-content' );
				this.adminNavigation        = $( '.qodef-tabs-navigation-wrapper' );

				this.adminHeader.css( 'width', this.adminPage.width() );

				$( window ).on(
					'scroll load',
					function () {
						if ( qodefWooFramework.scroll >= qodefAdminOptionsPanel.adminHeaderTopPosition ) {
							qodefAdminOptionsPanel.adminHeader.addClass( 'qodef-fixed' ).css(
								'top',
								parseInt( qodefAdminOptionsPanel.adminBarHeight )
							);
							qodefAdminOptionsPanel.adminContent.css(
								'marginTop',
								qodefAdminOptionsPanel.adminHeaderHeight
							);
							if ( qodefWooFramework.windowWidth > 1180 ) {
								qodefAdminOptionsPanel.adminNavigation.css(
									'marginTop',
									0
								);
							} else if ( qodefWooFramework.windowWidth <= 1180 ) {
								qodefAdminOptionsPanel.adminNavigation.css(
									'marginTop',
									qodefAdminOptionsPanel.adminBarHeight + qodefAdminOptionsPanel.adminHeaderHeight
								);
							}
						} else {
							qodefAdminOptionsPanel.adminHeader.removeClass( 'qodef-fixed' ).css(
								'top',
								0
							);
							qodefAdminOptionsPanel.adminContent.css(
								'marginTop',
								0
							);
							if ( qodefWooFramework.windowWidth > 1180 ) {
								qodefAdminOptionsPanel.adminNavigation.css(
									'marginTop',
									qodefAdminOptionsPanel.adminHeaderHeight
								);
							} else if ( qodefWooFramework.windowWidth <= 1180 ) {
								qodefAdminOptionsPanel.adminNavigation.css(
									'marginTop',
									qodefAdminOptionsPanel.adminHeader.offset().top + qodefAdminOptionsPanel.adminHeaderHeight - qodefWooFramework.scroll
								);
							}
						}
					}
				);
			}
		},
	};

	var qodefInitMediaUploader = {
		init: function ( $mainHolder ) {
			this.$holder = $mainHolder.find( '.qodef-image-uploader' );

			if ( this.$holder.length ) {
				this.$holder.each(
					function () {
						qodefInitMediaUploader.initField( $( this ) );
					}
				);
			}
		},
		reinit: function ( row ) {
			var $holder = $( row ).find( '.qodef-image-uploader' );

			if ( $holder.length ) {
				$holder.each(
					function () {
						qodefInitMediaUploader.initField( $( this ) );
					}
				);
			}
		},
		initField: function ( thisHolder ) {
			var varialbles = {
				$multiple: thisHolder.data( 'multiple' ) === 'yes' && thisHolder.data( 'file' ) === 'no',
				$file: thisHolder.data( 'file' ) === 'yes',
				$allowed_type: thisHolder.data( 'file' ) === 'yes' ? thisHolder.data( 'allowed-type' ) : 'image',
				$imageHolder: thisHolder,
				mediaFrame: '',
				attachment: '',
				$thumbImageHolder: thisHolder.find( '.qodef-image-thumb' ),
				$uploadId: thisHolder.find( '.qodef-image-upload-id' ),
				$removeButton: thisHolder.find( '.qodef-image-remove-btn' )
			};

			if ( varialbles.$thumbImageHolder.find( 'img' ).length ) {
				varialbles.$removeButton.show();
				qodefInitMediaUploader.remove( varialbles.$removeButton );
			}

			qodefInitMediaUploader.reset( thisHolder );

			varialbles.$imageHolder.on(
				'click',
				'.qodef-image-upload-btn',
				function () {

					// if the media frame already exists, reopen it.
					if ( varialbles.mediaFrame ) {
						varialbles.mediaFrame.open();
						return;
					}

					// create the media frame.
					varialbles.mediaFrame = wp.media.frames.fileFrame = wp.media(
						{
							title: $( this ).data( 'frame-title' ),
							button: {
								text: $( this ).data( 'frame-button-text' )
							},
							library: {
								type: varialbles.$allowed_type
							},
							multiple: varialbles.$multiple
						}
					);

					// call right select, multiple or single or file.
					if ( varialbles.$file ) {
						qodefInitMediaUploader.fileSelect( varialbles );
					} else if ( varialbles.$multiple ) {
						qodefInitMediaUploader.multipleSelect( varialbles );
					} else {
						qodefInitMediaUploader.singleSelect( varialbles );
					}

					// check selected images when wp media is opened.
					varialbles.mediaFrame.on(
						'open',
						function () {
							var selection = varialbles.mediaFrame.state().get( 'selection' ),
								ids       = varialbles.$uploadId.val().split( ',' );
							ids.forEach(
								function ( id ) {
									varialbles.attachment = wp.media.attachment( id );
									varialbles.attachment.fetch();
									selection.add( varialbles.attachment ? [varialbles.attachment] : [] );
								}
							);
						}
					);

					// open media frame.
					varialbles.mediaFrame.open();
				}
			);
		},
		multipleSelect: function ( varialbles ) {
			varialbles.mediaFrame.on(
				'select',
				function () {
					varialbles.attachment = varialbles.mediaFrame.state().get( 'selection' ).map(
						function ( attachment ) {
							attachment.toJSON();
							return attachment;
						}
					);

					varialbles.$removeButton.show().trigger( 'change' );
					qodefInitMediaUploader.remove( varialbles.$removeButton );

					var ids = $.map(
						varialbles.attachment,
						function ( o ) {
							if ( o.attributes.type === 'image' ) {
								return o.id;
							}
						}
					);

					varialbles.$uploadId.val( ids );
					varialbles.$thumbImageHolder.find( 'ul' ).empty().trigger( 'change' );

					// loop through the array and add image for each attachment.
					var attachment_count = varialbles.attachment.length;

					for ( var i = 0; i < attachment_count; ++i ) {
						if ( varialbles.attachment[i].attributes.sizes.thumbnail !== undefined ) {
							varialbles.$thumbImageHolder.find( 'ul' ).append( '<li><img src="' + varialbles.attachment[i].attributes.sizes.thumbnail.url + '" alt="thumbnail" /></li>' );
						} else {
							varialbles.$thumbImageHolder.find( 'ul' ).append( '<li><img src="' + varialbles.attachment[i].attributes.sizes.full.url + '" alt="thumbnail" /></li>' );
						}
					}

					varialbles.$thumbImageHolder.show().trigger( 'change' );
				}
			);
		},
		singleSelect: function ( varialbles ) {
			varialbles.mediaFrame.on(
				'select',
				function () {
					varialbles.attachment = varialbles.mediaFrame.state().get( 'selection' ).first().toJSON();

					// write to url field and img tag.
					if ( varialbles.attachment.hasOwnProperty( 'url' ) && varialbles.attachment.type === 'image' ) {

						varialbles.$removeButton.show();
						qodefInitMediaUploader.remove( varialbles.$removeButton );

						varialbles.$uploadId.val( varialbles.attachment.id );
						varialbles.$thumbImageHolder.empty();

						if ( varialbles.attachment.hasOwnProperty( 'sizes' ) && varialbles.attachment.sizes.thumbnail ) {
							varialbles.$thumbImageHolder.append( '<img class="qodef-single-image" src="' + varialbles.attachment.sizes.thumbnail.url + '" alt="thumbnail" />' );
						} else {
							varialbles.$thumbImageHolder.append( '<img class="qodef-single-image" src="' + varialbles.attachment.url + '" alt="thumbnail" />' );
						}
						varialbles.$thumbImageHolder.show().trigger( 'change' );
					}

				}
			);
		},
		fileSelect: function ( varialbles ) {

			varialbles.mediaFrame.on(
				'select',
				function () {
					varialbles.attachment = varialbles.mediaFrame.state().get( 'selection' ).first().toJSON();

					// write to url field and img tag.
					if ( varialbles.attachment.hasOwnProperty( 'url' ) && varialbles.$allowed_type.includes( varialbles.attachment.type ) ) {

						varialbles.$removeButton.show();
						qodefInitMediaUploader.remove( varialbles.$removeButton );

						varialbles.$uploadId.val( varialbles.attachment.id );
						varialbles.$thumbImageHolder.empty();

						varialbles.$thumbImageHolder.append(
							'' +
							'<img class="qodef-file-image" src="' + varialbles.attachment.icon + '" alt="thumbnail" />' +
							'<div class="qodef-file-name">' + varialbles.attachment.filename + '</div>' +
							''
						);

						varialbles.$thumbImageHolder.show().trigger( 'change' );
					}

				}
			);
		},
		remove: function ( button ) {
			button.on(
				'click',
				function () {
					// Remove images and hide it's holder.
					button.siblings( '.qodef-image-thumb' ).hide();
					button.siblings( '.qodef-image-thumb' ).find( 'img' ).attr(
						'src',
						''
					);
					button.siblings( '.qodef-image-thumb' ).find( 'li' ).remove();

					// reset meta fields.
					button.siblings( '.qodef-image-meta-fields' ).find( 'input[type="hidden"]' ).each(
						function () {
							$( this ).val( '' );
						}
					);

					button.hide().trigger( 'change' );
				}
			);
		},
		reset: function ( thisHolder ) {
			$( document ).on(
				'ajaxSuccess',
				function ( event, xhr, options ) {

					if ( options && options.data && -1 === options.data.indexOf( 'action=add-tag' ) ) {
						return;
					}

					thisHolder.find( '.qodef-image-thumb' ).hide();
					thisHolder.find( '.qodef-image-thumb' ).find( 'img' ).attr(
						'src',
						''
					);
					thisHolder.find( '.qodef-image-thumb' ).find( 'li' ).remove();

					// reset meta fields.
					thisHolder.find( '.qodef-image-meta-fields' ).find( 'input[type="hidden"]' ).each(
						function () {
							$( this ).val( '' );
						}
					);
				}
			);
		}
	};

	qodefWooFramework.qodefInitMediaUploader = qodefInitMediaUploader;

	var qodefColorPicker = {
		init: function ( $mainHolder ) {
			this.$holder = $mainHolder.find( '.qodef-color-field:not(.widefat)' );

			if ( this.$holder.length ) {
				this.$holder.each(
					function () {
						qodefColorPicker.initField( $( this ) );
					}
				);
			}
		},
		reinit: function ( row ) {
			var $holder = $( row ).find( '.qodef-color-field:not(.widefat)' );

			if ( $holder.length ) {
				qodefColorPicker.initField( $holder );
			}
		},
		initField: function ( thisHolder ) {
			thisHolder.wpColorPicker(
				{
					palettes: false,
					mode    : 'hsl',
				}
			);
		},
		checkFieldPosition: function ( item ) {
			var holder = item.find( '.qodef-color-field:not(.widefat)' );

			if ( holder.length ) {
				holder.each(
					function () {
						var thisHolder   = $( this ).parents( '.qodef-field-content' );
						var adminContent = $( '#wpbody-content' );

						if ( adminContent.length && (adminContent.outerHeight() - thisHolder.offset().top < 340) ) {
							thisHolder.addClass( 'qodef-color-picker-reverse' );
						}
					}
				);
			}
		}
	};

	qodefWooFramework.qodefColorPicker = qodefColorPicker;

	var qodefDatePicker = {
		init: function ( $mainHolder ) {
			this.$holder = $mainHolder.find( '.qodef-datepicker' );

			if ( this.$holder.length ) {
				this.$holder.each(
					function () {
						qodefDatePicker.initField( $( this ) );
					}
				);
			}
		},
		reinit: function ( row ) {
			var $holder = $( row ).find( '.qodef-datepicker' );

			if ( $holder.length ) {
				qodefDatePicker.initField( $holder );
			}
		},
		initField: function ( thisHolder ) {
			var dateFormat = thisHolder.data( 'date-format' );
			thisHolder.datepicker( { dateFormat: dateFormat } );
		}
	};

	qodefWooFramework.qodefDatePicker = qodefDatePicker;

	var qodefSelect2 = {
		init: function ( $mainHolder ) {
			this.$holder = $mainHolder.find( 'select.qodef-select2' );

			if ( this.$holder.length ) {
				this.$holder.each(
					function () {
						qodefSelect2.initField( $( this ) );
					}
				);
			}
		},
		reinit: function ( row ) {
			var $holder = $( row ).find( 'select.qodef-select2' );

			if ( $holder.length ) {
				qodefSelect2.initField( $holder );
			}
		},
		initField: function ( thisHolder ) {
			if ( typeof thisHolder.select2 === 'function' ) {
				thisHolder.select2(
					{
						width: '100%',
						allowClear: false,
						minimumResultsForSearch: 11,
						dropdownCssClass: 'qodef-select-v4',
					}
				);
			}
		}
	};

	qodefWooFramework.select2 = qodefSelect2;

	var qodefInitIconPicker = {
		init: function ( $mainHolder ) {
			this.$holder = $mainHolder.find( '.qodef-iconpicker-select:not(.qodef-select2):not(.qodef--icons-init)' );

			if ( this.$holder.length ) {
				this.$holder.each(
					function () {
						var $thisHolder = $( this );

						if ( typeof $thisHolder.fontIconPicker === 'function' ) {
							$thisHolder.addClass( 'qodef--icons-init' );
							$thisHolder.fontIconPicker();
						}
					}
				);
			}
		},
		reinit: function ( row, $element ) {
			var $holder = typeof $element !== 'undefined' && $element !== '' && $element !== false ? $element : $( row ).find( '.qodef-iconpicker-select:not(.qodef-select2)' );

			if ( $holder.length && ! $holder.hasClass( 'qodef--icons-init' ) && typeof $holder.fontIconPicker === 'function' ) {
				$holder.addClass( 'qodef--icons-init' );
				$holder.fontIconPicker();
			}
		}
	};

	qodefWooFramework.qodefInitIconPicker = qodefInitIconPicker;

	var qodefAddressFields = {
		init: function ( $mainHolder, trigger ) {
			this.$addressHolder = $mainHolder.find( '.qodef-address-field-holder' );

			if ( this.$addressHolder.length ) {
				this.$addressHolder.each(
					function () {
						qodefAddressFields.initMap(
							$( this ),
							trigger
						);
					}
				);
			}
		},
		initMap: function ( $holder, trigger ) {
			var $reset       = $holder.find( '.qodef-reset-marker' ),
				$inputField  = $holder.find( 'input' ),
				$mapField    = $holder.find( '.qodef-map-canvas' ),
				countryLimit = $holder.data( 'country' ),
				latFieldName = $holder.data( 'lat' ),
				$latField    = $( '.qodef-address-elements [name="' + latFieldName + '"]' ),
				lngFieldName = $holder.data( 'lng' ),
				$lngField    = $( '.qodef-address-elements [name="' + lngFieldName + '"]' );

			// This peace of code is required in order to re init maps for address field type when it's inside tabs layout.
			if ( trigger ) {
				$inputField.trigger( 'geocode' );
			}

			if ( typeof $inputField.geocomplete === 'function' && typeof trigger === 'undefined' ) {
				$inputField.geocomplete(
					{
						map: $mapField,
						details: '.qodef-address-elements',
						detailsAttribute: 'data-geo',
						types: ['geocode', 'establishment'],
						country: countryLimit,
						markerOptions: {
							draggable: true
						},
					}
				).bind(
					'geocode:result',
					function () {
						$reset.show();
					}
				);

				$inputField.on(
					'geocode:dragged',
					function ( event, latLng ) {
						$latField.val( latLng.lat() );
						$lngField.val( latLng.lng() );
						$reset.show();
						var map = $inputField.geocomplete( 'map' );
						map.panTo( latLng );
						var geocoder = new google.maps.Geocoder();

						geocoder.geocode(
							{ 'latLng': latLng },
							function ( results, status ) {
								if ( status === google.maps.GeocoderStatus.OK && typeof results[0] === 'object' ) {
									$inputField.val( results[0].formatted_address );
								}
							}
						);
					}
				);

				$inputField.on(
					'focus',
					function () {
						var map = $inputField.geocomplete( 'map' );
						google.maps.event.trigger(
							map,
							'resize'
						);
					}
				);

				$reset.on(
					'click',
					function ( e ) {
						e.preventDefault();

						$reset.hide();

						$inputField.geocomplete( 'resetMarker' ).val( '' );
						$latField.val( '' );
						$lngField.val( '' );
					}
				);

				$( window ).on(
					'load',
					function () {
						$inputField.trigger( 'geocode' );
					}
				);
			}
		},
	};

	qodefWooFramework.qodefAddressFields = qodefAddressFields;

	var qodefSearchOptions = {
		init: function ( $adminPageHolder ) {
			this.searchField    = $adminPageHolder.find( '.qodef-search-field' );
			this.adminContent   = $adminPageHolder.find( '.qodef-admin-content' );
			this.tabHolder      = $adminPageHolder.find( '.tab-content' );
			this.rowHolder      = $adminPageHolder.find( '.qodef-row-wrapper' );
			this.sectionHolder  = $adminPageHolder.find( '.qodef-section-wrapper' );
			this.repeaterHolder = $adminPageHolder.find( '.qodef-repeater-wrapper' );
			this.fieldHolder    = $adminPageHolder.find( '.qodef-field-holder' );

			if ( this.searchField.length ) {
				var searchLoading = this.searchField.next( '.qodef-search-loading' ),
					searchRegex,
					keyPressTimeout;

				this.searchField.on(
					'keyup paste',
					function () {
						var field = $( this );
						field.attr(
							'autocomplete',
							'off'
						);
						searchLoading.removeClass( 'qodef-hidden' );
						clearTimeout( keyPressTimeout );

						keyPressTimeout = setTimeout(
							function () {
								var searchTerm = field.val();
								searchRegex    = new RegExp(
									field.val(),
									'gi'
								);
								searchLoading.addClass( 'qodef-hidden' );

								if ( searchTerm.length < 3 ) {
									qodefSearchOptions.resetSearchView();
								} else {
									qodefSearchOptions.resetSearchView();
									qodefSearchOptions.adminContent.addClass( 'qodef-apply-search' );
									qodefSearchOptions.fieldHolder.each(
										function () {
											var thisFieldHolder = $( this );
											if ( thisFieldHolder.find( '.qodef-field-desc' ).text().search( searchRegex ) !== -1 ) {
												thisFieldHolder.parents( '.tab-content' ).addClass( 'qodef-search-show' );
												thisFieldHolder.parents( '.qodef-section-wrapper' ).addClass( 'qodef-search-show' );
												thisFieldHolder.parents( '.qodef-row-wrapper' ).addClass( 'qodef-search-show' );
												thisFieldHolder.parents( '.qodef-repeater-wrapper' ).addClass( 'qodef-search-show' );
											} else {
												thisFieldHolder.addClass( 'qodef-search-hide' );
											}
										}
									);
								}
							},
							500
						);
					}
				);

			}
		},
		resetSearchView: function () {
			this.adminContent.removeClass( 'qodef-apply-search' );
			this.tabHolder.removeClass( 'qodef-search-show' );
			this.rowHolder.removeClass( 'qodef-search-show' );
			this.sectionHolder.removeClass( 'qodef-search-show' );
			this.repeaterHolder.removeClass( 'qodef-search-show' );
			this.fieldHolder.removeClass( 'qodef-search-hide' );

		},
		resetSearchField: function () {
			this.searchField.val( '' );
		}
	};

	qodefWooFramework.qodefSearchOptions = qodefSearchOptions;

	var qodefAdminScroll = {
		disable: function () {
			if ( window.addEventListener ) {
				window.addEventListener(
					'wheel',
					qodefAdminScroll.preventDefaultValue,
					{ passive: false }
				);
				window.addEventListener(
					'touchmove',
					qodefAdminScroll.preventDefaultValue,
					{ passive: false }
				);
			}

			document.onkeydown = qodefAdminScroll.keyDown;
		},
		enable: function () {
			if ( window.removeEventListener ) {
				window.removeEventListener(
					'wheel',
					qodefAdminScroll.preventDefaultValue,
					{ passive: false }
				);
				window.removeEventListener(
					'touchmove',
					qodefAdminScroll.preventDefaultValue,
					{ passive: false }
				);
			}
			window.onmousewheel = document.onmousewheel = document.onkeydown = null;
		},
		preventDefaultValue: function ( e ) {
			e = e || window.event;
			if ( e.preventDefault ) {
				e.preventDefault();
			}
			e.returnValue = false;
		},
		keyDown: function ( e ) {
			var keys = [37, 38, 39, 40];
			for ( var i = keys.length; i--; ) {
				if ( e.keyCode === keys[i] ) {
					qodefAdminScroll.preventDefaultValue( e );
					return;
				}
			}
		}
	};

	var qodefDragAndDropCheckboxFields = {
		init: function ( $checkboxFieldHolder, $valuesHolder ) {
			var $fieldHolder = $checkboxFieldHolder.find( '.qodef-checkbox-group-holder' ),
				currentOrder = $valuesHolder.find( '.qodef-field' ).val();

			if ( currentOrder && ! currentOrder.trim() ) {
				qodefDragAndDropCheckboxFields.multipleSelectSaveNewOrder( qodefDragAndDropCheckboxFields.multipleSelectGetNewOrder( $checkboxFieldHolder ), $valuesHolder );
			} else {
				qodefDragAndDropCheckboxFields.multipleSelectSetOrder( $fieldHolder, currentOrder );
				qodefDragAndDropCheckboxFields.multipleSelectSaveNewOrder( qodefDragAndDropCheckboxFields.multipleSelectGetNewOrder( $checkboxFieldHolder ), $valuesHolder );
			}

			$fieldHolder.sortable(
				{
					opacity: 0.6,
					stop: function () {
						qodefDragAndDropCheckboxFields.multipleSelectSaveNewOrder( qodefDragAndDropCheckboxFields.multipleSelectGetNewOrder( $checkboxFieldHolder ), $valuesHolder );
					}
				}
			);
		},
		multipleSelectSaveNewOrder: function ( newOrder, $valuesHolder ) {
			$valuesHolder.find( '.qodef-field' ).val( newOrder );
		},
		multipleSelectSetOrder: function ( $fieldHolder, newOrder ) {
			var fieldWrappers = $fieldHolder.find( '.qodef-inline:not(.qodef-hide)' );

			fieldWrappers.sort(
				function (a, b) {
					var valueA = $( a ).find( '.qodef-field' ).val();
					var valueB = $( b ).find( '.qodef-field' ).val();
					return newOrder.indexOf( valueA ) - newOrder.indexOf( valueB );
				}
			);

			$fieldHolder.empty();
			fieldWrappers.each(
				function () {
					$fieldHolder.append( $( this ) );
				}
			);
		},
		multipleSelectGetNewOrder: function ( $checkboxFieldHolder ) {
			var $fields        = $checkboxFieldHolder.find( '.qodef-inline:not(.qodef-hide) .qodef-field' ),
				fieldsNewOrder = '',
				separator      = '';

			if ( $fields.length ) {
				$fields.each(
					function ( index ) {
						separator       = 0 === index ? '' : ',';
						fieldsNewOrder += separator + $( this ).val();
					}
				);
			}
			return fieldsNewOrder;
		}
	};

	qodefWooFramework.qodefDragAndDropCheckboxFields = qodefDragAndDropCheckboxFields;

	var qodefWidgetFields = {
		initColorPicker: function ( $widget ) {
			var $colorPickerHolder = typeof $widget !== 'undefined' ? $widget.find( '.qodef-widget-field--color' ) : $( '#widgets-right .qodef-widget-field--color' );

			if ( $colorPickerHolder.length ) {
				qodefWidgetFields.initPickerField(
					$colorPickerHolder,
					$colorPickerHolder.find( '.qodef-color-field' )
				);
			}
		},
		initPickerField: function ( $holder, $field ) {
			if ( $field.length && $holder.find( '.wp-picker-container' ).length <= 0 ) {
				$field.wpColorPicker(
					{
						change: _.throttle(
							function () {
								// For Customizer.
								$( this ).trigger( 'change' );
							},
							3000
						)
					}
				);
			}
		},
		initDependency: function ( $widget ) {
			var $dependency = $widget.find( '.widget-content .qodef-widget-field[data-option-name]' );

			if ( $dependency.length ) {
				qodefWooFramework.qodefDependency.reinitWidget( $dependency );
			}
		}
	};

})( jQuery );
