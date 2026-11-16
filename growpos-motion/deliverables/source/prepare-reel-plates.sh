#!/bin/sh
set -eu
cd "$(dirname "$0")"
P=public/reel
# Perspective-mapped verified UI captures into the blank concept device screens.
warp() {
  input="$1"; output="$2"; size="$3"; points="$4"
  magick "$P/$input" -resize "${size}^" -gravity center -extent "$size" \
    -virtual-pixel transparent -define distort:viewport=941x1672+0+0 -distort Perspective "$points" +repage "$P/$output"
}
# POS terminal, captured positions are in each 941x1672 source plate.
warp pos-catalog.png ui-opening.png 540x360 '0,0 416,622 539,0 899,592 539,359 875,950 0,359 351,941'
warp product-selection.png ui-cashier.png 590x365 '0,0 291,568 589,0 867,568 589,364 817,962 0,364 204,963'
warp order-cart.png ui-laptop.png 640x650 '0,0 250,451 639,0 883,447 639,649 837,1102 0,649 203,1096'
warp pos-catalog.png ui-laptop-stock.png 500x340 '0,0 222,655 499,0 690,652 499,339 673,984 0,339 182,977'
warp mobile-transaction-panel-crop.png ui-phone.png 260x470 '0,0 632,818 259,0 839,816 259,469 826,1284 0,469 576,1276'
warp operational-report.png ui-analytics.png 640x390 '0,0 229,781 639,0 820,750 639,389 769,1157 0,389 128,1154'
warp dashboard.png ui-dashboard.png 640x390 '0,0 229,781 639,0 820,750 639,389 769,1157 0,389 128,1154'
# End card uses real cashier UI on the laptop and the same verified order crop on mobile.
warp order-cart.png ui-hero-laptop.png 570x365 '0,0 158,766 569,0 728,769 569,364 714,1134 0,364 142,1130'
warp mobile-transaction-panel-crop.png ui-hero-phone.png 165x295 '0,0 748,887 164,0 887,887 164,294 875,1207 0,294 743,1208'
