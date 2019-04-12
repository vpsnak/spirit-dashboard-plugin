<?php
include_once 'class-spirit-pages.php';

$settingsApi = new Spirit_Pages();

$pages = array (
    array (
        'page_title' => 'Spirit Dashboard',
        'menu_title' => 'Spirit Dashboard',
        'capability' => 'manage_options',
        'menu_slug' => 'spirit-dashboard',
        'callback' => 'spirit_dashboard_index',
        'icon_url' => 'dashicons-image-filter',
        'position' => '100'
    ),
);

$settingsApi->addPages($pages)->withSubPage();

$subpages = array (
        array (
            'parent_slug' => 'spirit-dashboard',
            'page_title' => 'Settings',
            'menu_title' => 'Settings',
            'capability' => 'manage_options',
            'menu_slug' => 'spirit-dashboard-settings',
            'callback' => 'spirit_dashboard_settings'
        )
);

$settingsApi->addSubPages($subpages);

$sd_page = array (
    'settings' => array (
        'page' => 'spirit_dashboard_page_settings',
        'section'=>'spirit_dashboard_section_settings'
    )
);

$sections = array (
    array (
        'id' => $sd_page['settings']['section'],
        'title' => '',
        'callback' => '',
        'page' => $sd_page['settings']['page']
    ),
);

$settingsApi->add_sections($sections);

$sd_option = array (
    'username' => 'sd-username',
    'password' => 'sd-password',
);

$settings = array (
    array (
        'option_group' => $sd_page['settings']['section'],
        'option_name' => $sd_option['username'],
        'callback' => ''
    ),
    array (
        'option_group' => $sd_page['settings']['section'],
        'option_name' => $sd_option['password'],
        'callback' => ''
    ),
);

$settingsApi->add_settings($settings);

$fields = array (
    array (
        'id' => $sd_option['username'],
        'title' => 'Username',
        'callback' => 'sd_textbox',
        'page' => $sd_page['settings']['page'],
        'section' => $sd_page['settings']['section'],
        'args' => array (
            'label_for' => $sd_option['username'],
            'class' => ''
        )
    ),
    array (
        'id' => $sd_option['password'],
        'title' => 'Password',
        'callback' => 'sd_textbox',
        'page' => $sd_page['settings']['page'],
        'section' => $sd_page['settings']['section'],
        'args' => array (
            'label_for' => $sd_option['password'],
            'class' => ''
        )
    ),
);

$settingsApi->add_fields($fields);

$settingsApi->register();
include_once 'spirit-pages-callback.php';
include_once 'spirit-fields-callback.php';