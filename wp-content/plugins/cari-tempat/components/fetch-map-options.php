<?php
$options = get_option('_secret_api_key');

$api_key = $options && $options['api_key'] != "" ? $options['api_key'] : '<div style="color:#ff291c;">API Key harap diisi terlebih dahulu!</div>';

echo '<div>PHP version: ' . phpversion() . '</div>';
?>
<div class="col-wrap">
    <h2>Fetch Data</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-field form-required">
            <label>Keyword*</label>
            <input style="max-width: 400px;" name="keyword" id="keyword" placeholder="keyword" type="text" value="" size="40" aria-required=" true">
            <p></p>
        </div>

        <p class="submit"><input type="submit" name="ambilData" id="submit" class="button button-primary" value="Ambil Data"></p>
    </form>
    <?php
    if (isset($_POST['ambilData'])) {
        if ($_POST['keyword'] != '') {
            $keyword = $_POST['keyword'];

            require plugin_dir_path(__FILE__) . '../lib/places-data.php';
            $places_data = new PlacesData($api_key);
            $new_post = $places_data->post($keyword);

            if (is_wp_error($new_post)) {
                echo "<pre>";
                print_r($new_post);
                echo "</pre>";
            } else {
                echo "<script>window.location.href = '" . get_admin_url() . "post.php?post={$new_post}&action=edit';</script>";
            }
        }
    }
    ?>
</div>