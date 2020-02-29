<?php
require_once('build.php');

$options = (object) [
  'pokemon' => (object) [
    'base' => true,
    'female' => true,
    'forms' => true,
    'shiny' => true,
    'other' => true
  ],
  'items' => (object) [
    'ball' => true
  ]
];
buildPokesprite($options, true);