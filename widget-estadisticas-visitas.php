<?php

require_once( ABSPATH . 'wp-includes/class-wp-widget.php' );
require_once('plugin-estadisticas-visitas.php');
/**
 *  Widget de Estadísticas de Visitas
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

/**
 * Core class used to implement a Text widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */

class Estadisticas_Visitas_Widget extends WP_Widget {

    /**
     * Constructor del widget
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'estadisticas_visitas_widget',
            'description' => 'Muestra las estadísticas de visitas en tu sitio web.'
        );
        parent::__construct( 'estadisticas_visitas_widget', 'Estadísticas de Visitas', $widget_ops );
    }

    /**
     * Método para mostrar el widget en el front-end
     *
     * @param array $args Argumentos del widget.
     * @param array $instance Instancia del widget.
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        echo $args['before_title'] . 'Estadísticas de Visitas' . $args['after_title'];
        
        // Llama a la función para mostrar las estadísticas
        mostrar_estadisticas_visitas();
        
        echo $args['after_widget'];
    }

    /**
     * Método para mostrar el formulario de opciones del widget en el back-end
     *
     * @param array $instance Instancia del widget.
     */
    public function form( $instance ) {
        // No se requiere configuración adicional para este widget
    }

    /**
     * Método para procesar y guardar las opciones del widget en el back-end
     *
     * @param array $new_instance Nuevos valores del widget.
     * @param array $old_instance Valores antiguos del widget.
     * @return array Valores actualizados del widget.
     */
    public function update( $new_instance, $old_instance ) {
        return $new_instance;
    }
}

// Registrar el widget
function registrar_estadisticas_visitas_widget() {
    register_widget( 'Estadisticas_Visitas_Widget' );
}
add_action( 'widgets_init', 'registrar_estadisticas_visitas_widget' );

?>