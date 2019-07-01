<?php

namespace GDCalendar\Helpers;

class View
{

    /**
     * @param $view
     * @param string $path
     * @param string $defaultPath
     *
     * @return mixed
     */
    public static function locate($view, $path = '', $defaultPath = '')
    {
        if (!$path) {
            $path = \GDCalendar()->viewPath();
        }
        if (!$defaultPath) {
            $defaultPath = \GDCalendar()->pluginPath() . '/resources/views/';
        }

        $template = locate_template(
            array(
                trailingslashit($path) . $view,
                $view
            )
        );

        if (!$template) {
            $template = $defaultPath . $view;
        }

        return $template;
    }

    /**
     * @param $view
     * @param array $args
     * @param string $path
     * @param string $defaultPath
     */
    public static function render($view, $args = array(), $path = '', $defaultPath = '')
    {
        if ($args && is_array($args)) {

            extract($args);

        }

        $located = self::locate($view, $path, $defaultPath);

        if (!file_exists($located)) {

            _doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $located), '2.2.0');

            return;

        }

        include($located);
    }

    public static function buffer($view, $args = array(), $path='', $defaultPath = '')
    {
        ob_start();
        self::render($view, $args, $path, $defaultPath);
        return ob_get_clean();
    }
}