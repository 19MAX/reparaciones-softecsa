<?php

if (!function_exists('get_config_empresa')) {
    function get_config_empresa()
    {
        $model = new \App\Models\ConfiguracionModel();
        return $model->getConfig();
    }
}

if (!function_exists('get_logo_url')) {
    function get_logo_url()
    {
        $config = get_config_empresa();
        if (!empty($config['logo_path'])) {
            return base_url($config['logo_path']) . '?v=' . filemtime(FCPATH . $config['logo_path']);
        }
        return base_url('assets/images/logo.png');
    }
}
