<form method="post" action="options.php">
    <?php
    settings_fields('_secret_api_key');
    do_settings_sections('_secret_api_key');
    submit_button();
    ?>
</form>