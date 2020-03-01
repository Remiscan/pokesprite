<?php
require_once('build.php');

$options = (object) [
  'pokemon' => (object) [
    'base' => true,
    'female' => true,
    'forms' => true,
    'regular' => true,
    'shiny' => true,
    'other' => true
  ],
  'items' => (object) [
    'outline' => false,

    'apricorn' => true,
    'av-candy' => true,
    'ball' => true,
    'battle-item' => true,
    'berry' => true,
    'body-style' => true,
    'curry-ingredient' => true,
    'etc' => true,
    'ev-item' => true,
    'evo-item' => true,
    'exp-candy' => true,
    'flute' => true,
    'fossil' => true,
    'gem' => true,
    'hm' => true,
    'hold-item' => true,
    'incense' => true,
    'key-item' => true,
    'mail' => true,
    'medicine' => true,
    'mega-stone' => true,
    'memory' => true,
    'mint' => true,
    'mulch' => true,
    'other-item' => true,
    'partner-gift' => true,
    'petal' => true,
    'plate' => true,
    'poke-candy' => true,
    'roto' => true,
    'scarf' => true,
    'shard' => true,
    'storage' => true,
    'tm' => true,
    'tr' => true,
    'valuable-item' => true,
    'wonder-launcher' => true,
    'z-crystals' => true,
  ]
];
buildPokesprite($options, true);
//buildPokesprite(null, true);