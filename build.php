<?php
function buildPokesprite($options = null, $logs = false)
{
  if ($options == null)
  {
    $d = json_decode(file_get_contents('data/inventory.json', true));
    $itemGroups = [];

    $options = (object) [
      'pokemon' => (object) [
      'base' => true,
      'female' => true,
      'forms' => true,
      'regular' => true,
      'shiny' => true,
      'other' => true
      ],
      'items' => (object) []
    ];

    foreach($d->{'item-groups'} as $groupid => $group)
    {
      $options->items->{$groupid} = true;
    }
  }

  if ($logs) var_dump($options);

  // Step 1: get the list of files

    // Step 1.1: Pokémon

  $pokemon = json_decode(file_get_contents('data/pokemon.json', true));
  $iconesPokemon = [];
  $basePath = 'pokemon-gen8/';
  $pokemonSize = (object) [
    'width' => 68,
    'height' => 56
  ];

  foreach($pokemon as $dexid => $data)
  {
    $listeIcones = [];
    $baseSlug = $data->slug->eng;
    $forms = $data->{'gen-8'}->forms;
    foreach($forms as $formid => $form)
    {
      if ($formid == '$' && $options->pokemon->base !== true) continue;
      if ($formid != '$' && $options->pokemon->forms !== true) continue;

      if ($formid != '$')
        $slug = $baseSlug . '-' . $formid;
      else
        $slug = $baseSlug;

      if ($form->{'is_alias_of'} != '')
        $id = $form->{'is_alias_of'};
      else
        $id = $formid;

      if ($id != '$')
        $path = $baseSlug . '-' . $id;
      else
        $path = $baseSlug;

      if ($options->pokemon->regular === true) $iconesPokemon[$slug] = $basePath . 'regular/' . $path . '.png';
      if ($options->pokemon->shiny === true) $iconesPokemon[$slug . '-shiny'] = $basePath . 'shiny/' . $path . '.png';

      if ($form->{'has_female'} == 1 && $options->pokemon->female === true)
      {
        if ($options->pokemon->regular === true) $iconesPokemon[$slug . '-female'] = $basePath . 'regular/female/' . $path . '.png';
        if ($options->pokemon->shiny === true) $iconesPokemon[$slug . '-female-shiny'] = $basePath . 'shiny/female/' . $path . '.png';
      }
    }
  }

    // Step 1.2: additional Pokémon icons (eggs, etc)

  $other = json_decode(file_get_contents('data/other-sprites.json', true));
  $other = $other->pokemon;

  foreach($other as $id => $data)
  {
    if ($options->pokemon->other !== true) continue;

    $slug = $data->slug->eng;
    $path = $data->file;

    $iconesPokemon[$slug] = $basePath.$path . '.png';
  }

  if ($logs)
  {
    echo '<pre>';
    print_r($iconesPokemon);
    echo '</pre>';
  }

    // Step 1.3: Items

  $items = json_decode(file_get_contents('data/item-map.json', true));
  $iconesItems = [];
  $basePath = 'items/';
  $itemSize = (object) [
    'width' => 32,
    'height' => 32
  ];

  foreach($items as $itemid => $data)
  {
    $group = explode('/', $data)[0];
    if ($options->items->{$group} !== true) continue;

    $slug = str_replace('/', '-', $data);
    $iconesItems[$slug] = $basePath.$data . '.png';
  }

  if ($logs)
  {
    echo '<pre>';
    print_r($iconesItems);
    echo '</pre>';
  }



  // Step 2: generate the big tile and the CSS file in parallel

    // Step 2.1: compute final image size
  
  $width = 0; $height = 0;

  $pokemonColumns = 32;
  $width += $pokemonColumns * $pokemonSize->width;
  $height += (intdiv(count($iconesPokemon), $pokemonColumns) + 1) * $pokemonSize->height;

  $itemColumns = intdiv($width, $itemSize->width);
  $height += (intdiv(count($iconesItems), $itemColumns) + 1) * $itemSize->height;

  $currentPosition = (object) [
    'x' => 0,
    'y' => 0
  ];

    // Step 2.2: let's generate, baby

  $cssPath = 'out/pokesprite.css';
  $imagePath = 'out/pokesprite.png';

  // If files already exist, remove
  if (file_exists($cssPath)) unlink($cssPath);
  if (file_exists($imagePath)) unlink($imagePath);

  // Init CSS file
  file_put_contents($cssPath, ".pkspr{background-image:url('pokesprite.png')}.pkspr.pokemon{width:68px;height:56px}.pkspr.item{width:32px;height:32px}");

  // Create a blank image the right size
  $background = imagecreatetruecolor($width, $height);

  // Make that image transparent
  $black = imagecolorallocate($background, 0, 0, 0);
  imagecolortransparent($background, $black);
  imagealphablending($background, false);
  imagesavealpha($background, true);
  $outputImage = $background;

  foreach($iconesPokemon as $i => $file)
  {
    // Create image from the icon file
    $icon = imagecreatefrompng($file);

    // Copy that icon to the output image
    imagecopy($outputImage, $icon, $currentPosition->x, $currentPosition->y, 0, 0, $pokemonSize->width, $pokemonSize->height);

    // Insert position into CSS file
    $css = '.pkspr.pokemon.' . $i . '{background-position:' . $currentPosition->x . 'px ' . $currentPosition->y . 'px}';
    file_put_contents($cssPath, $css, FILE_APPEND);

    // Increment current position
    $currentPosition->x += $pokemonSize->width;
    if ($currentPosition->x + $pokemonSize->width > $width)
    {
      $currentPosition->x = $currentPosition->x % $width;
      $currentPosition->y += $pokemonSize->height;
    }
  }

  $currentPosition->x = 0;
  $currentPosition->y += $pokemonSize->height;

  foreach($iconesItems as $i => $file)
  {
    // Create image from the icon file
    $icon = imagecreatefrompng($file);

    // Copy that icon to the output image
    imagecopy($outputImage, $icon, $currentPosition->x, $currentPosition->y, 0, 0, $itemSize->width, $itemSize->height);

    // Insert position into CSS file
    $css = '.pkspr.item.' . $i . '{background-position:' . $currentPosition->x . 'px ' . $currentPosition->y . 'px}';
    file_put_contents($cssPath, $css, FILE_APPEND);

    // Increment current position
    $currentPosition->x += $itemSize->width;
    if ($currentPosition->x + $itemSize->width > $width)
    {
      $currentPosition->x = $currentPosition->x % $width;
      $currentPosition->y += $itemSize->height;
    }
  }

  imagepng($outputImage, $imagePath, 9, PNG_NO_FILTER);

  if ($logs) echo '<br>' . date('Y-m-d H:i:s') . ' - Image créée !';
}