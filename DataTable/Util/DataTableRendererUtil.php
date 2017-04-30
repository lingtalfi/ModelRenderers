<?php


namespace ModelRenderers\DataTable\Util;


use Bat\StringTool;

class DataTableRendererUtil
{


    public static function getRicValueStringByRow(array $ric, array $row, $sep = null)
    {
        if (null === $sep) {
            $sep = '+--ric_separator--+';
        }
        $values = [];
        foreach ($ric as $key) {
            if (array_key_exists($key, $row)) {
                $values[] = $row[$key];
            } else {
                throw new \RuntimeException("Key not found in ric: $key");
            }
        }
        return implode($sep, $values);
    }

    public static function getCountInfoText(array $model)
    {

        $page = $model['page'];
        $nipp = $model['nipp'];
        $nbItems = $model['nbTotalItems'];

        $offsetStart = (($page - 1) * $nipp) + 1;
        $offsetEnd = $offsetStart + $nipp - 1;
        if ($offsetEnd > $nbItems) {
            $offsetEnd = $nbItems;
        }

        return str_replace([
            '{offsetStart}',
            '{offsetEnd}',
            '{nbItems}',
        ], [
            $offsetStart,
            $offsetEnd,
            $nbItems,
        ], $model['textCountInfo']);
    }

    public static function getNippSelector(array $model, array $options = [])
    {

        $s = $model['textNipp'];
        $nipp = $model['nipp'];
        $attr = (array_key_exists('attr', $options)) ? $options['attr'] : [];
        $sel = '<select' . StringTool::htmlAttributes($attr) . '>';
        foreach ($model['nippItems'] as $value) {
            $sSel = ($nipp === $value) ? ' selected="selected"' : '';
            $sel .= '<option' . $sSel . ' value="' . $value . '">';
            if ('all' !== $value) {
                $sel .= $value;
            } else {
                $sel .= $model['textNippAll'];
            }
            $sel .= '</option>';
        }
        $sel .= '</select>';
        return str_replace('{select}', $sel, $s);
    }

    public static function toDataAttributes(array $attributes)
    {
        $attr = [];
        foreach ($attributes as $k => $v) {
            $attr['data-' . $k] = $v;
        }
        return StringTool::htmlAttributes($attr);
    }
}