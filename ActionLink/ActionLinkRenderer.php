<?php


namespace ModelRenderers\ActionLink;


use Bat\StringTool;
use ModelRenderers\Renderer\AbstractRenderer;

class ActionLinkRenderer extends AbstractRenderer
{

    public static function create()
    {
        return new static();
    }

    public function render()
    {
        $m = $this->model;
        $label = (array_key_exists('label', $m)) ? $m['label'] : '';
        unset($m['label']);
        return '<a href="#" ' . StringTool::htmlAttributes($m, 'data-') . '>' . $label . '</a>';
    }


}