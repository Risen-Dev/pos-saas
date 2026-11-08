# GrowPOS — The Art of Selling

Single-scene portrait motion study built with Remotion. It represents independent GrowPOS POS UI layers: catalog filters, product cards, pointer, cart, item count, discount, and total. Product values and labels are taken from the project Blade UI/demo. The catalog uses the real initial-letter fallback because no verified product photos are present. The logo and local project fonts are included in the render source.

## Output

- `remotion/out/growpos-art-of-selling.mp4` — H.264, 1080×1920, 60 fps, 6.2 seconds.
- `remotion/out/01-interface-reveal.png` — opening catalog.
- `remotion/out/02-product-focus.png` — selected product and pointer.
- `remotion/out/03-cart-flight.png` — cart arrival and price update.
- `remotion/out/04-final-composition.png` — settled order summary.
- `remotion/out/rendered-video-contact-sheet.png` — four frames extracted from the encoded MP4 for visual QA.

The video was reviewed from encoded MP4 frames. The demo order ends at subtotal Rp22.000, menu discount Rp2.200, and total Rp19.800. No production application files were changed.

## Source and rerender

The Remotion source and lockfile are in `remotion/`. Installed dependencies and the rejected V1–V3 renders were removed after delivery to save disk space. To rerender, install from the lockfile with `npm ci` inside `remotion/`, then run:

```sh
npx remotion render src/index.jsx GrowPOS-ArtOfSelling out/growpos-art-of-selling.mp4 --codec=h264 --crf=17 --browser-executable="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
```
