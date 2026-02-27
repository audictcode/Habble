<?php

namespace App\Services;

use Carbon\Carbon;

abstract class TimeService
{
    public static function transform($date): String
    {
		$restTime = now()->timestamp - $date->timestamp;

		if ($restTime < 1) {
			return '1 segundo';
        }

        $timeFormat = array(
            365 * 24 * 60 * 60  =>  'año',
            30 * 24 * 60 * 60  =>  'mes',
            24 * 60 * 60  =>  'día',
            60 * 60  =>  'hora',
            60  =>  'minuto',
            1  =>  'segundo'
        );

        $pluralTime = array(
            'año'   => 'años',
            'mes'  => 'meses',
            'día'    => 'días',
            'hora'   => 'horas',
            'minuto' => 'minutos',
            'segundo' => 'segundos'
        );

        foreach ($timeFormat as $seconds => $relatedTime) {
            $d = $restTime / $seconds;
            if ($d >= 1) {
                $time = round($d);
                return "hace {$time} " . ($time > 1 ? $pluralTime[$relatedTime] : $relatedTime);
            }
        }
    }
}
