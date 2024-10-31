<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions\Helpers;

/**
 * Class HTML
 * @package Perfect\DecorationsForOccasions\Helpers
 * This is a helper class, use static methods only
 */
class HTML
{
    /**
     * @param string $instance Application instance
     * @param string $view Name of the view to load
     * @param array $data Data to pass to the view
     *
     * @return string HTML output
     */
    static public function getViewOutput(&$instance, $view, $data)
    {
        ob_start();
        $instance->loadTmpl($view, $data);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public static function getTriviaLink($event_name){
        return 'https://decorationsforoccasions.com/'.strtolower(str_replace(array(' ', '_', '\'', '.'), array('-', '-', '', ''), trim($event_name))).'/trivia/';
    }

    public static function getIncludeOutput($file_path, $data = array())
    {
        $real_path = dirname(dirname(dirname(__FILE__))).'/tmpl/admin/'.$file_path;
        ob_start();
        //Extract vars and load the template
        extract($data);
        include($real_path);
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    static public function printTreeMenu($tree, $class, $baseURI)
    {
        if (!is_null($tree) && count($tree) > 0) {
            echo '<ul class="' . $class . '">';
            foreach ($tree as $node) {
                //Traverse the nodes
                if (isset($node['id'])) {
                    printf('<li><a href="%s" data-dfo-id="%d">%s</a>', $baseURI . '&display=' . $node['id'], $node['id'], $node['name']);
                } else {
                    printf('<li><a href="#">%s</a>', $node['name']);
                }
                if(isset($node['children'])){
                    self::printTreeMenu($node['children'], 'dl-submenu', $baseURI);
                }
                echo '</li>';
            }
            echo '</ul>';
        }
    }
}