<?php
// ═══════════════════════════════════════════════════════════
// COLORES POR ESCUDERÍA
// Cada equipo tiene su color, que se usa como acento en las
// tarjetas y en la ficha del auto. Las claves están normalizadas
// (minúsculas, sin acentos, un solo espacio), así que alcanza con
// agregar una línea cuando entre una escudería nueva.
// ═══════════════════════════════════════════════════════════

const TEAM_COLORS = [
    // Las más presentes en la colección
    'ferrari'          => '#e8002d',
    'mclaren'          => '#ff8000',
    'mercedes benz'    => '#00d2be',
    'red bull'         => '#2b4be0',
    'williams'         => '#3fc1f0',
    'alpine'           => '#f558a8',
    'lotus'            => '#d4af37',
    'brabham'          => '#4e9e6a',
    'renault'          => '#ffd800',
    'alfa romeo'       => '#c0294a',
    'maserati'         => '#1e6fc4',
    'aston martin'     => '#00b37a',
    'benetton'         => '#a8d83a',
    'chevrolet'        => '#e0a92e',
    'tyrrell'          => '#4169ff',

    // Las demás
    'apx gp'           => '#f2d33c',
    'arrows'           => '#ff6a1f',
    'ats'              => '#c9cdd2',
    'auto union'       => '#d7dce0',
    'bar'              => '#cfcfd6',
    'bmw sauber'       => '#5aa8ff',
    'brawn'            => '#c9f031',
    'brm'              => '#6fa83c',
    'eagle'            => '#2d7fd6',
    'force india'      => '#f2762a',
    'haas'             => '#e6e6ea',
    'hesketh'          => '#e86a9c',
    'honda'            => '#ff4d6d',
    'hrt'              => '#a8aeb5',
    'jaguar'           => '#0f8e5a',
    'jordan'           => '#c8d420',
    'lamborghini'      => '#bfd400',
    'lancia'           => '#d64b2a',
    'lancia / ferrari' => '#d64b2a',
    'ligier'           => '#2ca8d8',
    'march'            => '#d93f8c',
    'matra'            => '#3b6fd4',
    'penske'           => '#d8d22e',
    'sauber'           => '#3ac26a',
    'shadow'           => '#9aa0a6',
    'simca'            => '#b7c2cc',
    'stewart'          => '#d8d0b0',
    'talbot'           => '#2d6fb8',
    'toleman'          => '#4c8fe0',
    'toro rosso'       => '#5a6fe0',
    'wolf'             => '#c9a227',

    // Nombres alternativos de equipos que cambiaron de denominación
    'mercedes'         => '#00d2be',
    'mercedes amg'     => '#00d2be',
    'red bull racing'  => '#2b4be0',
    'alphatauri'       => '#5a6fe0',
    'alpha tauri'      => '#5a6fe0',
    'racing bulls'     => '#5a6fe0',
    'rb'               => '#5a6fe0',
    'alfa romeo racing'=> '#c0294a',
    'kick sauber'      => '#3ac26a',
    'stake'            => '#3ac26a',
    'racing point'     => '#f558a8',
    'lotus renault'    => '#d4af37',
    'team lotus'       => '#d4af37',
    'virgin'           => '#e8002d',
    'marussia'         => '#e8002d',
    'manor'            => '#2b4be0',
    'caterham'         => '#1f8a4c',
    'minardi'          => '#e8002d',
    'prost'            => '#2d6fb8',
    'footwork'         => '#ff6a1f',
    'osella'           => '#c0294a',
    'surtees'          => '#e8002d',
    'cooper'           => '#1f8a4c',
    'vanwall'          => '#1f8a4c',
    'porsche'          => '#c9cdd2',
    'bugatti'          => '#2d6fb8',
    'gordini'          => '#2ca8d8',
    'connaught'        => '#1f8a4c',
    'kurtis kraft'     => '#d8d22e',
    'kuzma'            => '#d8d22e',
    'epperly'          => '#d8d22e',
];

// Color por defecto para una escudería que todavía no esté en la tabla:
// un gris claro neutro, para que se note que falta cargarla y no se
// confunda con el rojo del sitio.
const TEAM_COLOR_FALLBACK = '#9aa0a6';

function teamColorKey(string $team): string {
    $k = mb_strtolower(trim($team), 'UTF-8');
    $k = strtr($k, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
    return preg_replace('/\s+/', ' ', $k);
}

// Devuelve el color hexadecimal de una escudería.
function teamColor(string $team): string {
    return TEAM_COLORS[teamColorKey($team)] ?? TEAM_COLOR_FALLBACK;
}

// Devuelve el atributo style listo para pegar en la tarjeta.
function teamColorStyle(string $team): string {
    return 'style="--team:' . teamColor($team) . '"';
}
