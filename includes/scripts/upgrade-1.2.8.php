<?php
function spirit_flush_rewrite_rules() {
    flush_rewrite_rules(true);
}
add_action( 'init', 'spirit_flush_rewrite_rules' );