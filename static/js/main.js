/*
 * ============================================================================
 * RestApiTheme entry point
 * ============================================================================
 */

var RestApiTheme = {};

RestApiTheme.$window = null;
RestApiTheme.$body = null;

RestApiTheme.isMobile = false;
RestApiTheme.windowSize = {
    width: 1920,
    height: 1280
};


/**
 * On document ready.
 *
 * @param event
 */
RestApiTheme.onDocumentReady = function(e) {
    var _this = _this;

    // Store temp configuration
    for( var index in temp ){
        RestApiTheme[index] = temp[index];
    }

    RestApiTheme.init();
};


/**
 * Init.
 */
RestApiTheme.init = function(){
    var _this = this;

    // Selectors
    _this.$window = $(window);
    _this.$body = $('body');

    // isMobile test
    _this.isMobile = (isMobile.any() === null) ? false : true;
    if(_this.isMobile) addClass(_this.$body[0],'is-mobile');

    // Events
    _this.$window.on('resize', $.proxy(_this.resize, _this));
    _this.$window.trigger('resize');
};


/**
 * Main resize method.
 *
 *
 */
RestApiTheme.resize = function(){
    var _this = this;

    /*
     * Match CSS media queries and JavaScript window width.
     *
     * @see http://stackoverflow.com/a/11310353
     */
    _this.windowSize = getViewportSize();
};


/*
 * ============================================================================
 * Plug into jQuery standard events
 * ============================================================================
 */
$(document).ready(RestApiTheme.onDocumentReady);
