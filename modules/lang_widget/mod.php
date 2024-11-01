<?php
class lang_widgetTbs extends moduleTbs {
    public function init() {
        parent::init();
        add_action('widgets_init', array($this, 'registerWidget'));
    }
    public function registerWidget() {
        return register_widget('formsWidgetWpTbs');
    }
}
/**
 * Forms Widget class
 */
class formsWidgetWpTbs extends WP_Widget {
    public function __construct() {
        $widgetOps = array( 
            'classname' => 'formsWidgetWpTbs', 
            'description' => __('Display Languages Select', TBS_LANG_CODE)
        );
        $control_ops = array(
            'id_base' => 'formsWidgetWpTbs'
        );
		parent::__construct( 'formsWidgetWpTbs', TBS_WP_PLUGIN_NAME, $widgetOps );
    }
    public function widget($args, $instance) {
        frameTbs::_()->getModule('lang_widget')->getView()->displayWidget($args, $instance);
    }
    public function update($new_instance, $old_instance) {
        return $new_instance;
    }
    public function form($instance) {
        frameTbs::_()->getModule('lang_widget')->getView()->displayForm($instance, $this);
    }
}

