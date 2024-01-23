<?php require_once(__DIR__."/../dom.php");

// For DOM examples, we disable most automated modules so that generated code is minimal

dom\set("script_back_to_top",           false);
dom\set("script_images_loading",        false);
dom\set("support_sliders",              false);
dom\set("support_service_worker",       false);
dom\set("script_pwa_install",           false);
dom\set("script_framework_material",    false);
dom\set("script_scan_and_print",        false);
dom\set("script_toolbar",               false);
dom\set("support_header_backgrounds",   false);
dom\set("script_toolbar_menu",          false);
dom\set("script_framework_material",    false);

// Don't minify generated code

dom\set("minify",   false);
dom\set("beautify", true);
