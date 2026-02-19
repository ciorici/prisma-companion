//--------------------------------------------------------------------//
// Prisma Companion Admin Widgets script.
//--------------------------------------------------------------------//
;(function( $ ) {
	"use strict";

	/**
	 * Common element caching.
	 */
	var $body     = $( 'body' );
	var $document = $( document );
	var $wrapper  = $( '#page' );
	var $html     = $( 'html' );
	var $this;

	/**
	 * Holds most important methods that bootstrap the whole theme.
	 * 
	 * @type {Object}
	 */
	var PrismaCompanionWidgets = {

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			// Document ready
			$(document).ready( PrismaCompanionWidgets.ready );

			// Window load
			$(window).on( 'load', PrismaCompanionWidgets.load );

			// Bind UI actions
			PrismaCompanionWidgets.bindUIActions();

			// Trigger event when Prisma Companion fully loaded
			$(document).trigger( 'prismaCompanionWidgetsReady' );
		},

		//--------------------------------------------------------------------//
		// Events
		//--------------------------------------------------------------------//

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			PrismaCompanionWidgets.initRepeatableSortable();
		},

		/**
		 * Window load.
		 *
		 * @since 1.0.0
		 */
		load: function() {
		},

		/**
		 * Bind UI actions.
		 *
		 * @since 1.0.0
		*/
		bindUIActions: function() {

			var $this,
				index = 0,
				template,
				$widget;

			$(document).on( 'click', '.pc-repeatable-widget .add-new-item', function(e){
				e.preventDefault();

				$this    = $(this);
				index    = parseInt( $this.attr('data-index') );
				template = wp.template( 'prisma-companion-repeatable-item' );

				var data = {
					index: index,
					name: $this.attr('data-widget-name'),
					id: $this.attr('data-widget-id'),
				};

				index++;

				$this.attr( 'data-index', index );
				$( template( data ) ).insertBefore( $this.closest('.pc-repeatable-footer') );
				$this.closest('.widget-inside').trigger('change');

				update_widget_repeatable_class( $this );
			});

			$(document).on( 'click', '.pc-repeatable-widget .remove-repeatable-item', function(e){
				e.preventDefault();

				$this   = $(this);
				$widget = $this.closest('.pc-repeatable-container');

				$this.closest('.widget-inside').trigger('change');
				$this.closest('.pc-repeatable-item').remove();
				
				update_widget_repeatable_class( $widget );
			});

			$(document).on( 'click', '.pc-repeatable-widget .pc-repeatable-item-title', function(){
				$(this).closest('.pc-repeatable-item').toggleClass('open');
			});

			var update_widget_repeatable_class = function( $target ) {

				var $widget = $target.closest('.pc-repeatable-container');

				if ( $widget.find('.pc-repeatable-item').length ) {
					$widget.removeClass('empty');
				} else {
					$widget.addClass('empty');
				}
			};

			// Updated widget event.
			$(document).on( 'widget-updated widget-added', function( e, widget ){
				if ( widget.find('.pc-repeatable-container').length ) {
					PrismaCompanionWidgets.initRepeatableSortable();
				}
			});
		},

		//--------------------------------------------------------------------//
		// Functions
		//--------------------------------------------------------------------//

		initRepeatableSortable: function() {

			$('.pc-repeatable-container').sortable({
				handle: '.pc-repeatable-item-title',
				accent: '.pc-repeatable-item',
				containment: 'parent',
				tolerance: 'pointer',
				change: function( event, ui ){
					$(this).closest('.widget-inside').trigger('change');
				},
			});


		},

	}; // END var PrismaCompanionWidgets.

	PrismaCompanionWidgets.init();
	window.PrismaCompanionWidgets = PrismaCompanionWidgets;	

})( jQuery );
