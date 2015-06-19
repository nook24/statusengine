Frontend.AppController = Frontend.Controller.extend({
	/**
	 * Holds the DOM element of this controller.
	 *
	 * @var DOMElement
	 */
	_dom: null,
	$: null,
	/**
	 * Holds the currently open dialog
	 *
	 * @var DOMElement
	 */
	_dialog: null,
	/**
	 * These components will be merged with the sub-controllers' components
	 *
	 * @return void
	 */
	baseComponents: [],

	/**
	 * Initializer
	 *
	 * @return void
	 */
	_init: function(){
		this._dom = $('div.controller.' + this._frontendData.controller + '_' + this._frontendData.action);
		this.$ = this._dom.find.bind(this._dom);
		this._initComponents();
		this._initialize(); // Intented to be overwritten.
	},

	/**
	 * Initializer, this one should be used by sub controllers
	 *
	 * @return void
	 */
	_initialize: function(){

	},

	/**
	 * Initializes global UI components
	 *
	 * @return void
	 */
	_initComponents: function(){
		var self = this;

		if(typeof $().chosen === 'function'){
			$('.chosen').chosen({
				placeholder_text_single: 'Please choose',
				placeholder_text_multiple: 'Please choose',
				allow_single_deselect: true, // This will only work if the first option has a blank text.
				search_contains: true,
				enable_split_word_search: true,
				width: '100%' // Makes the graph responsive.
			});
		}


	},
	/**
	 * Returns the DOM element of the controller
	 *
	 * @return DOMElement
	 */
	getDomElement: function(){
		return this._dom;
	},
	/**
	 * Returns Server-side state value for mobile-check
	 *
	 * @return {boolean}
	 */
	isMobile: function(){
		return this.getVar('isMobile');
	}
});
