# GrowPOS V3 asset manifest and provenance

| Asset | Source | Usable for | Status |
| --- | --- | --- | --- |
| `../public/video/growpos-demo.mp4` | Included local project asset; H.264, 1600×900, 30 fps, 26.97 s | Authentic cashier interaction, stock, and report screen footage | Inspected. Shows fictional “Kedai Senja Demo” data. |
| `../public/img/growpos_logo.png` | Included project brand asset | Closing mark, subject to checking correct transparent/white variant | Available. |
| `../public/img/growpos_logo-bg.png` | Included project brand asset | Brand/color reference | Available. |
| `../public/img/hero-screen-1920.webp` | Included landing-page artwork | Product context only | Depicts a person holding a tablet with a screen that is not a verified mobile capture; do not use as an authentic GrowPOS phone UI. |
| `public/laptop-studio-v2.png` | Generated for rejected V2 composition | Historical reference only | Do not reuse in the V3 shot architecture. |
| Laptop 3D geometry | Original SceneKit geometry authored in `render-v3-scene.swift` | Perspective screen mapping, camera reveal, and hero | Available. Not a third-party or exact commercial laptop model; see visual-QA note on model detail. |
| Mobile capture | Not present in repo | Mobile scene | Not available; V3 uses the desktop stock/report bridge. |
| Approved music/SFX | Not present in motion folder | Audio mix | Not available. |
| Verified public URL | Not found in checked project assets/config | End-card CTA | Not available; omit from end card. |

## Runtime check

- FFmpeg and FFprobe are available.
- Blender, Three.js, Remotion, and an external device model are not present in the project/runtime.
- SceneKit and an Apple M2 Metal GPU rendered the original laptop geometry. The production UI stays perspective-registered on the actual 3D display surface.
- The production Laravel application was left untouched.
