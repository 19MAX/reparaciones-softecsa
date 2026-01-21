<?php

use Config\Sidebar;


function current_controller(): string
{
    $router = service('router');
    return class_basename($router->controllerName());
}

/**
 * ¿El módulo está activo?
 */
function sidebar_module_active(string $module): bool
{
    $controller = current_controller();
    return in_array($controller, Sidebar::$modules[$module]['controllers'] ?? []);
}

/**
 * ¿Este item está activo?
 */
function sidebar_item_active(string $controller): bool
{
    return current_controller() === $controller;
}

function sidebar_class(bool $condition, string $class): string
{
    return $condition ? $class : '';
}