<?php
function sd_label ($args) {
    ?>
    <label for="<?= $args['label_for'] ?>"><?= $args['title'] ?></label>
    <?php
}

function sd_textbox ($args) {
    ?>
    <input type="text" name="<?= $args['label_for'] ?>" id="<?= $args['label_for'] ?>" value="<?php echo get_option($args['label_for']); ?>" />
    <?php
}

function sd_checkbox ($args) {
    echo '<input name="' . $args['label_for'] . '" id="' . $args['label_for'] . '" type="checkbox" value="0" class="code" ' . checked(1, get_option($args['label_for']), false) . ' />';
}

function sd_numberbox ($args) {
    ?>
    <input type="number" name="<?= $args['label_for'] ?>" id="<?= $args['label_for'] ?>" value="<?php echo get_option($args['label_for']); ?>" />
    <?php
}


function sd_emailbox ($args) {
    ?>
    <input type="email" name="<?= $args['label_for'] ?>" id="<?= $args['label_for'] ?>" value="<?php echo get_option($args['label_for']); ?>" />
    <?php
}

function sd_fileuploadbox ($args) {
    ?>
    <input type="file" name="<?= $args['label_for'] ?>" id="<?= $args['label_for'] ?>" />
    <?php $option = get_option($args['label_for']);
    if (isset($option)) {
        $file_name = array_slice(explode('/', rtrim($option, '/')), -1)[0];
        echo '<a href="' . $option . '" target="_blank">' . $file_name . '</a>';
    }
}

function sd_editorbox ($args) {
    $settings = array (
        'teeny' => true,
        'textarea_rows' => 15,
        'tabindex' => 1,
        'media_buttons' => false
    );
    wp_editor(get_option($args['label_for']), $args['label_for'], $settings);
}
