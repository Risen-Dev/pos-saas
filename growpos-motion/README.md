# GrowPOS — Everything Flows (V3)

Standalone cinematic product film. The production application remains untouched. V3 rebuilds the visual system with a real SceneKit camera, original 3D laptop geometry, perspective-mapped screen material, and the authentic local GrowPOS demo recording.

## Deliverables

- `dist/growpos-v2-prototype.mp4` — earlier 8.4-second visual prototype, retained for history.
- `dist/growpos-reels-v2.mp4` — earlier 35-second V2 render, retained for history.
- `dist/growpos-v3-prototype.mp4` — V3 8-second prototype for the signal, real cashier interaction, and 3D laptop reveal.
- `dist/growpos-v3-prototype-contact-sheet.png` — prototype frames for review.
- `dist/growpos-v3-prototype-keyframe.png` — representative prototype hero frame.
- `dist/growpos-everything-flows-v3.mp4` — V3 28-second final Reels film.
- `dist/growpos-everything-flows-v3-contact-sheet.png` — full-film contact sheet sampled every two seconds.
- `dist/growpos-everything-flows-v3-qa.png` — representative visual-QA frames.
- `dist/growpos-everything-flows-v3-hero.png` — final device/brand frame.

Both V3 videos are 1080×1920, 60 fps, H.264 MP4. The final runs for 28 seconds. It has no audio track because the project did not include approved music or SFX.

## Render

Requirements: macOS with SceneKit and an accessible Metal GPU, Swift, FFmpeg/FFprobe with `libx264`, and ImageMagick.

```sh
node growpos-motion/render-v3-prototype.mjs
node growpos-motion/render-v3-film.mjs
```

Set `GROWPOS_FORCE_RENDER=1` when invoking a script to rebuild its cached frame sequence. Rendering is deterministic and frame-based. The scripts use only local assets and do not connect to the production application.

## Product and media constraints

- UI footage comes from `../public/video/growpos-demo.mp4`, a fictional “Kedai Senja Demo” account. Its numbers are demo data, not customer results.
- The laptop is original procedural geometry authored in `render-v3-scene.swift`; its real screen surface carries recorded GrowPOS UI. No phone is shown because there is no verified mobile capture.
- Stock and operational report screens are captured from the same demo video. The ad does not claim a completed payment, profit, or a specific business outcome.
- The closing frame uses the project logo and “Satu Sistem, Tumbuh Bersama.” No public URL was verified, so no URL appears.
- No licensed audio asset was available. The MP4 is intentionally silent.

See `docs/creative-direction-v3.md`, `docs/asset-manifest-v3.md`, and `docs/visual-qa-v3.md` for direction, provenance, and inspection notes.
