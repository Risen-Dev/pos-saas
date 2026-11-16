# Render and reproduce

Requirements: Node.js, npm, Chrome/Chromium compatible with Remotion, FFmpeg/FFprobe, and ImageMagick.

1. Open a terminal in `source/` and install the pinned project dependencies:

   ```sh
   npm ci
   ```

2. Rebuild the perspective UI plates and original score after changing source captures or scripts:

   ```sh
   ./prepare-reel-plates.sh
   node make-score.mjs
   ```

3. Render the final composition from `source/`:

   ```sh
   npx remotion render src/index.jsx GrowPOS-EverySaleMatters ../growpos-reels-final.mp4 --codec=h264 --crf=17 --pixel-format=yuv420p
   ```

4. Trim AAC packet padding to keep the MP4 container at exactly 30 seconds:

   ```sh
   ffmpeg -y -i ../growpos-reels-final.mp4 -map 0:v:0 -map 0:a:0 -c:v copy -af "atrim=end=29.98,asetpts=PTS-STARTPTS" -c:a aac -b:a 160k -t 30 -movflags +faststart ../growpos-reels-exact.mp4
   mv ../growpos-reels-exact.mp4 ../growpos-reels-final.mp4
   ```

If Remotion cannot find Chrome automatically, add `--browser-executable=/path/to/Chrome`. The checked-in `package-lock.json` pins the version used for this render. The Remotion composition is 1080 × 1920, 30 fps, and 900 frames.
