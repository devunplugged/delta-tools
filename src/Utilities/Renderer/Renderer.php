<?php
namespace Utilities\Renderer;

class Renderer
{
    public static function render(string $template, array $args = [], bool $render = true)
    {
        foreach($args as $key => $value){
            $$key = $value;
        }

        if(!$render){
            ob_start();
        }
        
        include $template;

        if(!$render){
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }
}