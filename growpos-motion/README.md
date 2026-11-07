# GrowPOS Cinematic Product Film V2

Standalone promotional film source. It uses the authentic screen recording at `../public/video/growpos-demo.mp4`; it does not modify the Laravel application.

## Outputs

- `dist/growpos-v2-prototype.mp4` — 8.4-second visual direction prototype.
- `dist/growpos-reels-v2.mp4` — final 35-second, 1080×1920, 60 fps H.264 Reels render.

## Re-render

Requirements: Node.js, FFmpeg with `libx264`/`xfade`, ImageMagick, and Arial at `/System/Library/Fonts/Supplemental/Arial.ttf`.

```sh
node growpos-motion/render-v2-prototype.mjs
node growpos-motion/render-v2.mjs
```

The scene timing, UI source offsets, crop paths, and transition choices are configured in `render-v2.mjs`. The composition uses frame-timed FFmpeg filters for repeatable rendering.

## Creative and product notes

- A photorealistic laptop studio plate in `public/laptop-studio-v2.png` was generated for compositing. Its blank display is filled with the real GrowPOS demo recording.
- The cashier, stock, and report sequences are reframed directly from the demo recording. The product screen itself is not redrawn.
- The source does not show a verified mobile flow or a completed QRIS payment, so neither is claimed. The mobile storyboard beat becomes a close-up desktop stock view.
- The demo contains fictional **Kedai Senja Demo** data. Its displayed figures are not customer results.
- Music/SFX assets and a confirmed public URL were not available, so the deliverable has no audio and no URL.
- The device plate is a photorealistic still with deterministic camera motion and perspective-aligned UI compositing; this render does not use a live 3D device model or Remotion. A fresh authorized mobile capture, audio asset, or URL can be incorporated in a later pass.

See [GROWPOS_PROMPT_ID.md](./GROWPOS_PROMPT_ID.md) for the project-specific product facts and authenticity constraints.
