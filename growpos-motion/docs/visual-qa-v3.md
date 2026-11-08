# GrowPOS V3 visual QA

## Result

**Status: rendered and visually reviewed.** The 8-second prototype was completed first, inspected, and used to set the camera/device framing before the 28-second film was rendered. V3 uses actual SceneKit 3D geometry and a perspective-mapped GrowPOS display rather than the V1/V2 slideshow architecture.

## Checks completed

- Inspected the included 26.97-second demo recording at cashier and stock moments. It contains a real cashier flow, cursor, selected product, and an updating order panel. It also contains stock and report screens.
- Confirmed the source capture is 1600×900 H.264 at 30 fps.
- Searched the repo for externally supplied laptop/device 3D models and mobile capture assets; none were found. The laptop was authored as original SceneKit geometry.
- Checked available runtime commands and project dependencies: FFmpeg/FFprobe, Swift, SceneKit, ImageMagick, and an Apple M2 Metal GPU were used. Blender, Three.js, and Remotion were not needed for the final pipeline.
- Inspected the prototype contact sheet and full-film contact sheets, including the cashier-to-device reveal, laptop-to-stock move, stock-to-report change, and final brand frame.
- Verified the final with FFprobe: H.264, 1080×1920, 60/1 fps average and nominal, 28.000 seconds, 14,205,650 bytes. No audio stream is present.
- The prototype was separately verified at H.264, 1080×1920, 60 fps, and 8.000 seconds.

## Visual observations

- The cashier footage shows the real selection modal, cursor, and changing order state. The green signal resolves at the screen edge and the camera reveals the actual 3D laptop.
- The stock and report scenes use recorded screens, not redrawn data. The camera moves through the screen and back into the device hero.
- Portrait framing keeps the laptop visible in hero moments, with the UI large in the close scenes. Text labels were timed to the studio negative space so they obscure less of the product UI.
- The laptop is original SceneKit geometry with a modeled display, bezel, deck, keyboard, and trackpad. It reads convincingly in this film, though a more detailed licensed model could improve closer product shots in a future revision.

## Product and brand limitations

- The only available UI source is the fictional local demo recording; do not present its values as customer results.
- No verified mobile interface capture, approved audio, or verified public URL is available. The final has no phone, audio, or URL.
- The closing brand frame uses the included GrowPOS logo and “Satu Sistem, Tumbuh Bersama.”
