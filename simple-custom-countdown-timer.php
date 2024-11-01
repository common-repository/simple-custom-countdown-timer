<?php
/*
Plugin Name: URJ Countdown Widget
Description: URJ Countdown Widget
Version: 1.0
Author: Sergei Bobrov
License: GPL2
*/

function urj_countdown_widget_register () {
    register_widget( 'urj_countdown_widget' );
}
add_action( 'widgets_init', 'urj_countdown_widget_register');

class urj_countdown_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'urj_countdown_widget',
            __('URJ Countdown Widget', 'urj_countdown_domain'),
            array(
                'classname'   => 'color-picker-widget',
                'description' => __('URJ Countdown Widget', 'urj_countdown_domain')
            )
        );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_footer-widgets.php', array( $this, 'print_scripts' ), 9999 );
    }

    public function enqueue_scripts( $hook_suffix ) {
        if ( 'widgets.php' !== $hook_suffix ) {
            return;
        }
        // Color picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'underscore' );
        // DateTime picker
        wp_enqueue_style('kv_js_time_style', plugin_dir_url( __FILE__ ).'css/jquery-ui-timepicker-addon.css');
        wp_enqueue_style('jquery-style', plugin_dir_url( __FILE__ ).'css/jquery-ui.css');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-time-picker',  plugin_dir_url( __FILE__ ).'js/jquery-ui-timepicker-addon.js', array('jquery-ui-datepicker'));
    }

    public function print_scripts() {
        ?>
        <script>
            ( function( $ ){
                function initColorPicker( widget ) {
                    widget.find( '.color-picker' ).wpColorPicker( {
                        change: _.throttle( function() { // For Customizer
                            $(this).trigger( 'change' );
                        }, 3000 )
                    });
                }

                function initDatePicker( widget ) {
                    widget.find( '.cd_date_class' ).datetimepicker( {
                        change: _.throttle( function() { // For Customizer
                            $(this).trigger( 'change' );
                        }, 3000 )
                    });
                }

                function onFormUpdate( event, widget ) {
                    initColorPicker( widget );
                    initDatePicker( widget );
                }

                $( document ).on( 'widget-added widget-updated', onFormUpdate );

                $( document ).ready( function() {
                    $( '#widgets-right .widget:has(.color-picker)' ).each( function () {
                        initColorPicker( $( this ) );
                        initDatePicker( $( this ) );
                    } );
                } );
            }( jQuery ) );
        </script>
        <?php
    }

    public function widget($args, $instance) {

        $dc_date = $instance['cd_date'] ? strtotime($instance['cd_date']) : 0;
        $text = $instance['text'] ? $instance['text'] : '';
        $text2 = $instance['text2'] ? $instance['text2'] : '';
        $font_color = $instance['font_color'] ? $instance['font_color'] : '#36373e';
        $bg_color = $instance['bg_color'] ? $instance['bg_color'] : '#DCDCDC';

        if ($dc_date > time()){

            wp_enqueue_style('countdown_style', plugin_dir_url( __FILE__ ).'css/countdown.css');
            wp_enqueue_script('countdown',  plugin_dir_url( __FILE__ ).'js/countdown.js', array('jquery'));

            echo $args['before_widget'];

            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
            }
        ?>
            <div class="urj_countdown_widget">
                <script>
                    var deadline = '<?php echo date('D M d Y H:i:s O', $dc_date); ?>';
                </script>
                <style>
                    #urj_countdown { color: <?php echo $font_color; ?> }
                    #urj_countdown div > span { background-color: <?php echo $bg_color; ?> }
                </style>
                <?php if($text): ?><div class="countdown_text"><?php echo $text ?></div><?php endif; ?>
                <div id="urj_countdown">
                    <div>
                        <span class="days"></span>
                        <div class="smalltext">Days</div>
                    </div>
                    <div>
                        <span class="hours"></span>
                        <div class="smalltext">Hours</div>
                    </div>
                    <div>
                        <span class="minutes"></span>
                        <div class="smalltext">Minutes</div>
                    </div>
                    <div>
                        <span class="seconds"></span>
                        <div class="smalltext">Seconds</div>
                    </div>
                </div>
                <?php if($text2): ?><div class="countdown_text"><?php echo $text2 ?></div><?php endif; ?>
            </div>
        <?php
            echo $args['after_widget'];
        }
    }

    public function form($instance) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        $dc_date = isset($instance['cd_date']) ? esc_attr($instance['cd_date']) : '';
        $text = isset($instance['text']) ? esc_attr($instance['text']) : '';
        $text2 = isset($instance['text2']) ? esc_attr($instance['text2']) : '';
        $font_color = isset($instance['font_color']) ? esc_attr($instance['font_color']) : '#36373e';
        $bg_color = isset($instance['bg_color']) ? esc_attr($instance['bg_color']) : '#DCDCDC';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('cd_date'); ?>"><?php _e('Date'); ?></label>
            <input type="text" class="widefat cd_date_class" id="<?php echo $this->get_field_id('cd_date'); ?>" name="<?php echo $this->get_field_name('cd_date'); ?>" value="<?php echo $dc_date; ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text above digits'); ?></label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('text2'); ?>"><?php _e('Text below digits'); ?></label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('text2'); ?>" name="<?php echo $this->get_field_name('text2'); ?>"><?php echo $text2; ?></textarea>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'font_color' ); ?>"><?php _e( 'Font color:' ); ?></label><br>
            <input type="text" class="color-picker" id="<?php echo $this->get_field_id( 'font_color' ); ?>" name="<?php echo $this->get_field_name( 'font_color' ); ?>" value="<?php echo $font_color; ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'bg_color' ); ?>"><?php _e( 'Background color:' ); ?></label><br>
            <input type="text" class="color-picker" id="<?php echo $this->get_field_id( 'bg_color' ); ?>" name="<?php echo $this->get_field_name( 'bg_color' ); ?>" value="<?php echo $bg_color; ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['cd_date'] = (isset($new_instance['cd_date'])) ? $new_instance['cd_date'] : '';
        $instance['title'] = (isset($new_instance['title'])) ? strip_tags( $new_instance['title'] ) : '';
        $instance['text'] = (isset($new_instance['text'])) ? trim($new_instance['text']) : '';
        $instance['text2'] = (isset($new_instance['text2'])) ? trim($new_instance['text2']) : '';
        $instance['font_color'] = ( isset( $new_instance['font_color'] ) ) ? trim($new_instance['font_color']) : '';
        $instance['bg_color'] = ( isset( $new_instance['bg_color'] ) ) ? trim($new_instance['bg_color']) : '';
        return $instance;
    }
}
?>