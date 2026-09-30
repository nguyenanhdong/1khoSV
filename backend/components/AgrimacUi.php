<?php

namespace backend\components;

use yii\helpers\Html;

class AgrimacUi
{
    public static function badge($label, $color, $bg)
    {
        return '<span class="am-badge" style="background:' . $bg . ';color:' . $color . '">' . Html::encode($label) . '</span>';
    }

    public static function stat($icon, $label, $value, $sub, $accent)
    {
        return '<div class="am-stat" style="border-top-color:' . $accent . '"><div>'
            . '<div class="am-stat-label">' . Html::encode($label) . '</div>'
            . '<div class="am-stat-value">' . Html::encode($value) . '</div>'
            . ($sub !== null ? '<div class="am-stat-sub">' . Html::encode($sub) . '</div>' : '')
            . '</div><span class="am-stat-icon">' . $icon . '</span></div>';
    }

    public static function info($icon, $label, $value)
    {
        return '<div class="am-info"><div class="am-info-label">' . $icon . ' ' . Html::encode($label) . '</div>'
            . '<div class="am-info-value">' . Html::encode($value !== null && $value !== '' ? $value : '—') . '</div></div>';
    }

    public static function thead(array $cols)
    {
        $html = '<thead><tr>';
        foreach ($cols as $col) {
            $html .= '<th>' . Html::encode($col) . '</th>';
        }
        return $html . '</tr></thead>';
    }

    public static function orderStatus($status)
    {
        $s = AgrimacData::ORDER_STATUS[$status];
        return self::badge($s['label'], $s['color'], $s['bg']);
    }
}
