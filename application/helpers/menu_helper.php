<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cek apakah menu sedang aktif
 * berdasarkan controller & method
 */
function active_menu($ctrl, $method = null)
{
    $CI =& get_instance();
    $c = strtolower($CI->router->class);
    $m = strtolower($CI->router->method);

    $ctrl = strtolower($ctrl);
    $method = $method ? strtolower($method) : null;

    if ($c == $ctrl) {
        if ($method === null || $method == $m) {
            return 'active';
        }
    }
    return '';
}

/**
 * Untuk highlight group / parent menu
 */
function active_group($ctrls = [])
{
    $CI =& get_instance();
    $c = strtolower($CI->router->class);

    foreach ($ctrls as $ctrl) {
        if ($c == strtolower($ctrl)) {
            return 'active';
        }
    }
    return '';
}

/**
 * Cek URL aktif (jika tidak pakai controller)
 */
function active_url($url)
{
    $CI =& get_instance();
    $current = uri_string();

    if ($current == trim($url, '/')) {
        return 'active';
    }
    return '';
}

/**
 * Generate sidebar link
 */
function sidebar_link($menu)
{
    $active = active_menu($menu->controller, $menu->method);

    return '
    <li class="nav-item '.$active.'">
        <a class="nav-link" href="'.base_url($menu->url).'">
            <i class="'.$menu->icon.'"></i>
            <span>'.$menu->title.'</span>
        </a>
    </li>';
}
