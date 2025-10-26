<?php

use acalvino4\easyimage\models\Settings;
use acalvino4\easyimage\models\TransformSet as TS;

return get_object_vars(new Settings(
  transformSets: [
    'hero' => new TS(
      widths: [2560, 1280],
      aspectRatio: 2 / 1,
    ),
    'hero-mobile' => new TS(
      widths: [640, 320],
      aspectRatio: 1 / 2,
    ),
    'user-photo' => new TS(
        widths: [274, 274],
        aspectRatio: 1 / 1,
    ),
    'card' => new TS(
      widths: [408, 272],
      aspectRatio: 2/1.3
  ),
    'blurredMaxImageSize' => 8
  ]
));