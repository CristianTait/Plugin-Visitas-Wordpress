<?php
/*
Plugin Name: Estadisticas de Visitas
Plugin URI: https://cristiantait/plugin-estadisticas-visitas
Description: Plugin para mostrar estadísticas de visitas en tu sitio web.
Version: 1.0.0
Author: Cristian Tait
Author URI: https://cristiantait.com
License: GPL2
*/

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

//require_once ABSPATH . 'includes/class-estadisticas-visitas.php';

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(ABSPATH . 'wp-includes/functions.php');
require_once(ABSPATH . 'wp-includes/http.php');
require_once('widget-estadisticas-visitas.php');
require_once(ABSPATH . 'wp-includes/class-wp-widget.php');
function obtener_datos_visita()
{

    // Obtener el motor de búsqueda
    $motor_busqueda = obtener_motor_busqueda();  
    $datos_visita['motor_busqueda'] = $motor_busqueda;

    // Obtener el país de donde llega la visita
    $pais_llegada = obtener_pais_llegada();  
    $datos_visita['pais_llegada'] = $pais_llegada;

    $datos_visita = array();

    // Obtener la dirección IP del visitante
    $ip = $_SERVER['REMOTE_ADDR'];
    $datos_visita['visitante_ip'] = $ip;

    // Obtener el navegador del visitante
    $navegador = $_SERVER['HTTP_USER_AGENT'];
    $datos_visita['navegador'] = $navegador;

    // Obtener el país del visitante 
    $pais = obtener_pais_del_visitante();  
    $datos_visita['pais'] = $pais;

    // Obtener el tipo de dispositivo del visitante 
    $dispositivo = obtener_tipo_dispositivo();  
    $datos_visita['dispositivo'] = $dispositivo;

    // Obtener la fecha y hora actual
    $fecha_registro = current_time('mysql');
    $datos_visita['fecha_registro'] = $fecha_registro;

    return $datos_visita;
}

function obtener_pais_llegada()
{
    if (isset($_SERVER['HTTP_REFERER'])) {
        $url_referencia = $_SERVER['HTTP_REFERER'];
        $paises = array(
            'es' => 'España',
            'us' => 'Estados Unidos',
            'fr' => 'Francia',
            // Añade más países si lo deseas
        );

        // Obtener el código de país desde la URL de referencia
        $codigo_pais = obtener_codigo_pais_desde_url($url_referencia);  

        // Verificar si el código de país existe en la lista de países conocidos
        if (isset($paises[$codigo_pais])) {
            return $paises[$codigo_pais]; // Devolver el nombre del país de llegada
        }

        return 'Desconocido'; // Si no se detecta ningún país conocido, se devuelve "Desconocido"
    } else {
        return 'Error HTTP-REFERER';
    }
}

function obtener_codigo_pais_desde_url($url)
{
    // Obtener la URL de referencia sin el protocolo y la www
    $url_sin_protocolo = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $url);

    // Verificar si la URL tiene un dominio de nivel superior de dos letras
    preg_match('/\.([a-z]{2})($|\/|\?)/', $url_sin_protocolo, $matches);

    if (isset($matches[1])) {
        return strtolower($matches[1]); // Devolver el código de país en minúsculas
    }

    return ''; // Si no se encuentra un código de país válido, se devuelve una cadena vacía
}

function obtener_motor_busqueda()
{
    if (isset($_SERVER['HTTP_REFERER'])) {
        $url_referencia = $_SERVER['HTTP_REFERER'];
        $motores_busqueda = array(
            'google' => 'Google',
            'bing' => 'Bing',
            'yahoo' => 'Yahoo',
            // Añade más motores de búsqueda si lo deseas
        );

        // Verificar si la URL de referencia corresponde a un motor de búsqueda conocido
        foreach ($motores_busqueda as $motor => $nombre) {
            if (strpos($url_referencia, $motor) !== false) {
                return $nombre; // Devolver el nombre del motor de búsqueda
            }
        }

        return 'Desconocido'; // Si no se detecta ningún motor de búsqueda conocido, se devuelve "Desconocido"
    } else {
        return 'Error HTTP-REFERER';
    }
}



function obtener_tipo_dispositivo()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Definir patrones de User-Agent para detectar el tipo de dispositivo
    $patrones = array(
        '/mobile/i' => 'Móvil',
        '/tablet/i' => 'Tableta',
        '/android/i' => 'Android',
        '/iphone/i' => 'iPhone',
        '/ipad/i' => 'iPad',
    );

    // Verificar cada patrón en el User-Agent
    foreach ($patrones as $patron => $tipo) {
        if (preg_match($patron, $user_agent)) {
            return $tipo; // Devolver el tipo de dispositivo
        }
    }

    return 'Desconocido'; // Si no se detecta ningún tipo de dispositivo, se devuelve "Desconocido"
}


function obtener_pais_del_visitante()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $url = "http://api.ipstack.com/$ip?access_key=TU_ACCESS_KEY"; // Reemplaza "TU_ACCESS_KEY" con tu propia clave de acceso

    // Realizar la solicitud HTTP para obtener la información del país
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return ''; // Manejo de errores, retorna un valor por defecto o muestra un mensaje de error
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['country_name'])) {
        return $data['country_name']; // Devuelve el nombre del país
    } else {
        return ''; // Manejo de errores, retorna un valor por defecto o muestra un mensaje de error
    }
}


add_action('wp_loaded', 'actualizar_datos_visita');



function estadisticas_visitas_activate_plugin()
{
    // Crear una tabla de base de datos
    global $wpdb;
    $table_name = $wpdb->prefix . 'estadisticas_visitas'; // Nombre de la tabla con el prefijo de la base de datos
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        visitante_ip varchar(100) NOT NULL,
        navegador varchar(100) NOT NULL,
        pais varchar(100) NOT NULL,
        dispositivo varchar(100) NOT NULL,
        fecha_registro datetime NOT NULL,
        count int NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Establecer opciones iniciales
    add_option('estadisticas_visitas_version', '1.0.0');
    add_option('estadisticas_visitas_tracking_enabled', true);
}

register_activation_hook(__FILE__, 'estadisticas_visitas_activate_plugin');


function estadisticas_visitas_deactivate_plugin()
{
    // Realizar acciones de desactivación, como limpiar la base de datos o desactivar funcionalidades específicas

    // Eliminar la tabla de base de datos al desactivar el plugin
    global $wpdb;
    $table_name = $wpdb->prefix . 'estadisticas_visitas'; // Nombre de la tabla con el prefijo de la base de datos

    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    // Eliminar opciones al desactivar el plugin
    delete_option('estadisticas_visitas_version');
    delete_option('estadisticas_visitas_tracking_enabled');
}

register_deactivation_hook(__FILE__, 'estadisticas_visitas_deactivate_plugin');


// Agregar una entrada de menú para tu plugin en la barra de WordPress
function agregar_menu_plugin_visitas()
{
    add_menu_page(
        'Estadísticas de Visitas', // Título de la página
        'Visitas', // Texto del menú
        'manage_options', // Capacidad requerida para acceder a la página
        'plugin-visitas', // Slug de la página
        'mostrar_pagina_plugin_visitas', // Función que mostrará el contenido de la página
        'dashicons-chart-bar', // Icono del menú (puedes cambiarlo)
        80 // Posición en el menú
    );
}
add_action('admin_menu', 'agregar_menu_plugin_visitas');

// Función que muestra el contenido de la página del plugin
function mostrar_pagina_plugin_visitas()
{
    // Aquí puedes mostrar el contenido de tu página de administración del plugin
    echo '<h1>Estadísticas de Visitas</h1>';
    mostrar_estadisticas_visitas();
    // Resto del contenido de la página
}

function agregar_chart_js()
{
    wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js', array(), '3.5.1', true);
    wp_script_add_data('chart-js', 'integrity', 'sha512-Wt1bJGtlnMtGP0dqNFH1xlkLBNpEodaiQ8ZN5JLA5wpc1sUlk/O5uuOMNgvzddzkpvZ9GLyYNa8w2s7rqiTk5Q==');
}
add_action('wp_enqueue_scripts', 'agregar_chart_js');


function actualizar_datos_visita()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'estadisticas_visitas';

    // Obtener los datos de la visita
    $datos_visita = obtener_datos_visita();

    // Construir la consulta SQL
    $sql = "INSERT INTO $table_name (visitante_ip, navegador, pais, dispositivo, fecha_registro, count)
            VALUES (%s, %s, %s, %s, %s, 1)
            ON DUPLICATE KEY UPDATE count = count + 1";

    // Preparar los valores para la consulta
    $values = array(
        $datos_visita['visitante_ip'],
        $datos_visita['navegador'],
        $datos_visita['pais'],
        $datos_visita['dispositivo'],
        $datos_visita['fecha_registro']
    );

    // Ejecutar la consulta SQL
    $wpdb->query($wpdb->prepare($sql, $values));
}

function mostrar_estadisticas_visitas()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'estadisticas_visitas';

    // Obtener el número total de visitas
    $total_visitas = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Obtener el número de visitantes online
    $tiempo_limite = time() - 5 * 60; // Consideramos como "online" a aquellos visitantes que han tenido actividad en los últimos 5 minutos
    $visitantes_online = $wpdb->get_var("SELECT COUNT(DISTINCT visitante_ip) FROM $table_name WHERE fecha_registro > '$tiempo_limite'");

    // Obtener los navegadores de donde se accede
    $navegadores = $wpdb->get_results("SELECT DISTINCT navegador, COUNT(*) AS count FROM $table_name GROUP BY navegador ORDER BY count DESC");

    // Obtener los países de donde llegan las visitas
    $paises = $wpdb->get_results("SELECT DISTINCT pais, COUNT(*) AS count FROM $table_name GROUP BY pais ORDER BY count DESC");

    // Obtener los tipos de dispositivo
    $tipos_dispositivo = $wpdb->get_results("SELECT DISTINCT dispositivo, COUNT(*) AS count FROM $table_name GROUP BY dispositivo ORDER BY count DESC");

    // Obtener los motores de búsqueda
    $motores_busqueda = $wpdb->get_results("SELECT DISTINCT motor_busqueda, COUNT(*) AS count FROM $table_name GROUP BY motor_busqueda ORDER BY count DESC");

     // Obtener el número total de visitas únicas por IP
     $total_visitas_unicas = $wpdb->get_var("SELECT COUNT(DISTINCT visitante_ip) FROM $table_name");

    // Mostrar las estadísticas en tu página
    echo '<h2>Estadísticas de Visitas</h2>';
    echo '<p>Total de visitas: ' . $total_visitas . '</p>';
    echo '<p>Visitantes online: ' . $visitantes_online . '</p>';
    echo '<p>Total de visitas únicas: ' . $total_visitas_unicas . '</p>';

    // Obtener los navegadores de donde se accede
    $navegadores = $wpdb->get_results("SELECT DISTINCT navegador, COUNT(*) AS count FROM $table_name GROUP BY navegador ORDER BY count DESC");

    // Mostrar los navegadores
    echo '<h3>Navegadores</h3>';


    $navegadores_labels = array();
    $navegadores_data = array();

    foreach ($navegadores as $navegador) {
        $navegadores_labels[] = $navegador->navegador;
        $navegadores_data[] = $navegador->count;
    }

    $navegadores_labels = json_encode($navegadores_labels);
    $navegadores_data = json_encode($navegadores_data);
    $navegadores_labels = explode(',', $navegadores_labels);
    $navegadores_data = explode(',', $navegadores_data);

?>

    <canvas id="navegadores-chart" width="400" height="400"></canvas>
    <script src="../wp-content/plugins/Plugin-Visitas/Chart-min.js"></script>
    <script>
        // Obtener los datos del gráfico desde PHP
        var navegadoresLabels = <?php echo json_encode($navegadores_labels, JSON_UNESCAPED_UNICODE); ?>;
        var navegadoresData = <?php echo json_encode($navegadores_data, JSON_UNESCAPED_UNICODE); ?>;

        var navegadoresLabels2 = ["Chrome", "Firefox", "Safari"];
        var navegadoresData2 = [60, 30, 10];

        // Verificar que navegadoresLabels sea un array válido
        if (!Array.isArray(navegadoresLabels)) {
            console.error('Los datos de las etiquetas del gráfico no son válidos.');
        } else {
            // Crear el gráfico de barras con Chart.js
            var ctx = document.getElementById('navegadores-chart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: navegadoresLabels2,
                    datasets: [{
                        label: 'Cantidad',
                        data: navegadoresData2,
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            
                        ]
                    }]
                },
                options: {
                    responsive: false, // Cambiado a false
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        console.log(navegadoresLabels);
        console.log(navegadoresData);
    </script>

<?php
    // Mostrar los países
    echo '<h3>Países</h3>';
    echo '<ul>';
    foreach ($paises as $pais) {
        echo '<li>' . $pais->pais . ': ' . $pais->count . '</li>';
    }
    echo '</ul>';

    // Obtener los tipos de dispositivo
    $tipos_dispositivo = $wpdb->get_results("SELECT DISTINCT dispositivo, COUNT(*) AS count FROM $table_name GROUP BY dispositivo ORDER BY count DESC");

    // Mostrar los tipos de dispositivo
    echo '<h3>Tipos de Dispositivo</h3>';
    echo '<ul>';
    foreach ($tipos_dispositivo as $tipo_dispositivo) {
        echo '<li>' . $tipo_dispositivo->dispositivo . ': ' . $tipo_dispositivo->count . '</li>';
    }
    echo '</ul>';

    // Obtener los motores de búsqueda
    $motores_busqueda = $wpdb->get_results("SELECT DISTINCT motor_busqueda, COUNT(*) AS count FROM $table_name GROUP BY motor_busqueda ORDER BY count DESC");

    // Mostrar los motores de búsqueda
    echo '<h3>Motores de Búsqueda</h3>';
    echo '<ul>';
    foreach ($motores_busqueda as $motor_busqueda) {
        echo '<li>' . $motor_busqueda->motor_busqueda . ': ' . $motor_busqueda->count . '</li>';
    }
    echo '</ul>';
}


?>