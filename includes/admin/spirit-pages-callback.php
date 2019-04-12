<?php
function spirit_dashboard_index () {
    ?>
    <div class="wrap">
        <h1>Dashboard Index</h1>
		<h4>Welcome to spirit dashboard settings page!</h4>
		<p>Thanks for using our service.</p>
    </div>
    <?php
}
function spirit_dashboard_settings () {
    ?>
    <div class="wrap">
        <h1>Dashboard Settings</h1>
		<h4>Welcome to spirit dashboard settings page!</h4>
		<form method="post" action="options.php">
            <?php
            settings_fields("spirit_dashboard_page_settings");
            do_settings_sections("spirit_dashboard_page_settings");
            submit_button();
            ?>
		</form>
    </div>
    <?php
}