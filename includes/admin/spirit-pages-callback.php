<?php
function spirit_dashboard_index () {
    ?>
	<div class="wrap">
		<h1>Dashboard Index</h1>
		<h4>Welcome to spirit dashboard index page!</h4>
		<p>Thanks for using our service.</p>
	</div>
    <?php
}

function spirit_dashboard_settings () {
    ?>
	<div class="wrap">
		<h1>Dashboard Settings</h1>
		<form method="post" action="options.php">
            <?php
            settings_fields("spirit_dashboard_section_settings");
            do_settings_sections("spirit_dashboard_page_settings");
            submit_button();
            ?>
		</form>
	</div>
    <?php
}

function spirit_dashboard_send_data () {
    ?>
	<div class="wrap">
		<h1>Dashboard Data</h1>
        <?php
        include_once(SPIRIT_APP_DIR . 'class-spirit-plugin-route.php');
        include_once(SPIRIT_APP_DIR . 'class-spirit-theme-route.php');
        include_once(SPIRIT_APP_DIR . 'class-spirit-debug-route.php');
        
        $spirit_plugin_route = new Spirit_Plugin_Route();
        $spirit_theme_route = new Spirit_Theme_Route();
        $spirit_debug_route = new Spirit_Debug_Route();
        ?>
		<pre><?php print_r($spirit_plugin_route->get_plugins_data()) ?></pre>
		<pre><?php print_r($spirit_theme_route->get_themes_data()) ?></pre>
		<pre><?php print_r($spirit_debug_route->get_wordpress_data()) ?></pre>
		<pre><?php print_r($spirit_debug_route->get_server_data()) ?></pre>
		<pre><?php print_r($spirit_debug_route->get_installation_data()) ?></pre>
	</div>
    <?php
}