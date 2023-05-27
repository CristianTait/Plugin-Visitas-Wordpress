<?php
/**
 * Clase para manejar las estadísticas de visitas
 */
class Estadisticas_Visitas {

    /**
     * Almacenar los datos de una visita en la base de datos
     *
     * @param array $datos_visita Datos de la visita.
     */
    public static function almacenar_datos_visita($datos_visita) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'estadisticas_visitas';

        $wpdb->insert($table_name, $datos_visita);
    }

    /**
     * Obtener el número total de visitas registradas en la base de datos
     *
     * @return int Número total de visitas.
     */
    public static function obtener_total_visitas() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'estadisticas_visitas';

        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    /**
     * Obtener el número de visitantes online
     *
     * @return int Número de visitantes online.
     */
    public static function obtener_visitantes_online() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'estadisticas_visitas';

        $tiempo_limite = time() - 5 * 60; // Consideramos como "online" a aquellos visitantes que han tenido actividad en los últimos 5 minutos
        return $wpdb->get_var("SELECT COUNT(DISTINCT visitante_ip) FROM $table_name WHERE fecha_registro > '$tiempo_limite'");
    }

    /**
     * Obtener los navegadores más utilizados por los visitantes
     *
     * @param int $limit Número máximo de navegadores a obtener.
     * @return array Navegadores más utilizados.
     */
    public static function obtener_navegadores_mas_utilizados($limit = 5) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'estadisticas_visitas';

        $query = "SELECT navegador, COUNT(*) AS count FROM $table_name GROUP BY navegador ORDER BY count DESC LIMIT %d";
        $prepared_query = $wpdb->prepare($query, $limit);

        return $wpdb->get_results($prepared_query);
    }

    /**
     * Obtener los países de donde llegan las visitas
     *
     * @param int $limit Número máximo de países a obtener.
     * @return array Países de donde llegan las visitas.
     */
    public static function obtener_paises_llegada($limit = 5) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'estadisticas_visitas';

        $query = "SELECT pais, COUNT(*) AS count FROM $table_name GROUP BY pais ORDER BY count DESC LIMIT %d";
        $prepared_query = $wpdb->prepare($query, $limit);

        return $wpdb->get_results($prepared_query);
    }

    /**
     * Obtener los tipos de dispositivo utilizados por los visitantes
     *
     * @param int $limit Número máximo de tipos de dispositivo a obtener.
     * @return array Tipos de dispositivo utilizados por los visitantes.
     */
    public static function obtener_tipos_dispositivo($limit = 5) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'estadisticas_visitas';

 $query = "SELECT dispositivo, COUNT(*) AS count FROM $table_name GROUP BY dispositivo ORDER BY count DESC LIMIT %d";
        $prepared_query = $wpdb->prepare($query, $limit);

        return $wpdb->get_results($prepared_query);
    }

    /**
     * Obtener los motores de búsqueda utilizados por los visitantes
     *
     * @param int $limit Número máximo de motores de búsqueda a obtener.
     * @return array Motores de búsqueda utilizados por los visitantes.
     */
    public static function obtener_motores_busqueda($limit = 5) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'estadisticas_visitas';

        $query = "SELECT motor_busqueda, COUNT(*) AS count FROM $table_name GROUP BY motor_busqueda ORDER BY count DESC LIMIT %d";
        $prepared_query = $wpdb->prepare($query, $limit);

        return $wpdb->get_results($prepared_query);
    }
}