# GrowPOS Reels — production report

## Status

A 30-second encoded visual proof and editable Remotion project were produced. The video passes technical duration, raster, frame-rate, frame-count, audio-presence, and black-frame checks. **It is not publication-ready as a premium live-action commercial** because the workspace has no real café footage and no dynamic application capture. The available generated café images are still concepts. Dynamic UI actions and the human payoff therefore cannot be rendered faithfully from the available material.

## Files created

- `growpos-reels-final.mp4` — 1080 × 1920, 30 fps, 900 video frames, 30.000-second video stream.
- `growpos-reels-preview.mp4` — 540 × 960, 15 fps review copy.
- `growpos-contact-sheet.png` — encoded checkpoints at 0, 2, 5, 8, 12, 17, 22, 26, and 29 seconds.
- `growpos-storyboard.md`
- `growpos-asset-manifest.md`
- `source/` — editable source snapshot, assets, lockfile, and build helpers.
- `render-instructions.md`

No GrowPOS business logic was modified. Source implementation is in `source/src/reel.jsx`; the Remotion entry and registration are in `source/src/index.jsx`.

## Verified interface material

The film uses authentic POS catalog, product-selection, order/cart, and operational-report captures from the existing preproduction audit set, which were derived from the 26.97-second `public/video/growpos-demo.mp4` recording. No customer data or production credentials were accessed. The phone screen uses a desktop panel crop, not a verified responsive capture. UI shown in the film is static; it does not demonstrate the live product selection, cart calculation, payment, or reporting transitions requested in the brief.

The reporting image is a real captured module. This proof does not animate or independently validate its metrics. No new KPI values were invented.

## Footage and audio source

The six café/device plates were existing generated concept stills. They provide the look of a warm shop environment but contain no real moving-camera footage. The original score is synthesized in the included script. It includes a soft pulse and restrained click/confirmation accents; no commercial track was used. Dedicated coffee-shop ambience and production-recorded sound effects were not available.

## Tools and dependencies

- Remotion 4.0.541, React 19.1.0, Node.js 24.9.0.
- FFmpeg 9.0.2 and FFprobe for preview, frame extraction, contact sheet and technical QA.
- ImageMagick for perspective mapping, image assembly, and contact sheet layout.
- The render used Chrome launched outside the workspace sandbox after the sandboxed browser failed with a macOS Mach port permission error.

## QA findings and fixes

- Rendered and re-rendered at exact 30 fps with exactly 900 video frames.
- FFprobe reports a 1080 × 1920 video stream at 30/1 fps, exactly 900 frames, and a 30.000-second MP4 container duration. The audio stream is trimmed to 29.980 seconds to account for AAC packet padding while preserving the complete video duration.
- FFmpeg volume scan: mean −28.7 dBFS, peak −16.6 dBFS; no clipping detected.
- Black-frame scan found no black intervals longer than 0.1 seconds.
- Contact sheet was reviewed. Two source issues were corrected before final output: screen composites were positioned as absolute overlays, perspective placements for laptop displays were adjusted, and scene-local headline/end-card timing was corrected.
- Human-payoff copy spacing was reviewed at 23 seconds and corrected so the two lines remain distinct.
- The screens remain still composites. Natural device reflections, hand occlusion, and parallax are not production-grade tracking and need on-location replacement or refinement.

## Differences and remaining production needs

- Scenes 1–2 use concept stills instead of macro extraction and a moving cup handoff.
- Scenes 3–5 show genuine UI captures but not real supported interactions or a live connected transition between modules.
- Scene 6 uses a cashier concept still rather than a filmed human payoff.
- The ending uses the official icon mark and a typeset wordmark because no official horizontal wordmark was found in the audited assets.
- The score is an original generated synth bed, not a mixed production soundtrack with recorded café ambience.

For release approval, replace the still-concept sections with cleared live-action footage, capture a safe demo session performing the verified actions, and recapture the responsive mobile interface. Then rerender with the included project.
