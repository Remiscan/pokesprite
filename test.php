<?php
$d = json_decode(file_get_contents('data/inventory.json', true));
$itemGroups = [];

foreach($d->{'item-groups'} as $groupid => $group)
{
  echo '\'' . $groupid . '\' => true,<br>';
}