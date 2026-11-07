import { mkdir } from 'node:fs/promises';
import { spawnSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '..');
const work = path.join(here, 'dist/v2-prototype-work');
const output = path.join(here, 'dist/growpos-v2-prototype.mp4');
const source = path.join(root, 'public/video/growpos-demo.mp4');
const device = path.join(here, 'public/laptop-studio-v2.png');
const base = path.join(work, 'laptop-portrait.png');
const type = path.join(work, 'intro-type.png');
const fps = 60;
const duration = 8.4;
const font = '/System/Library/Fonts/Supplemental/Arial.ttf';

await mkdir(work, { recursive: true });
await mkdir(path.dirname(output), { recursive: true });

const image = spawnSync('magick', [
  '-size', '1080x1920', 'gradient:#071713-#1c4938',
  '(', device, '-resize', '1680x945!', ')', '-geometry', '-300+440', '-composite',
  base,
], { stdio: 'inherit' });
if (image.status !== 0) throw new Error('Could not create the cinematic laptop composition.');

const title = spawnSync('magick', [
  '-size', '1080x1920', 'xc:none', '-font', font, '-fill', '#f4fbf7', '-pointsize', '56',
  '-gravity', 'north', '-annotate', '+0+252', 'Kenalkan GrowPOS.', type,
], { stdio: 'inherit' });
if (title.status !== 0) throw new Error('Could not create prototype title layer.');

const args = [
  '-hide_banner', '-loglevel', 'error', '-y',
  '-loop', '1', '-framerate', String(fps), '-i', base,
  '-loop', '1', '-framerate', String(fps), '-i', type,
  '-ss', '5.35', '-t', String(duration), '-i', source,
  '-filter_complex',
  `[1:v]format=rgba,fade=t=in:st=0.15:d=0.45:alpha=1,fade=t=out:st=2.3:d=0.5:alpha=1[title];`+
  `[2:v]scale=955:536:flags=lanczos,fps=${fps},format=rgba,fade=t=in:st=0.8:d=0.7:alpha=1[ui];`+
  `[0:v][title]overlay=0:0:shortest=1[plate];`+
  `[plate][ui]overlay=67:557:shortest=1[shot];`+
  `[shot]zoompan=z='min(1.24,1+0.24*on/${duration*fps})':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1:s=1080x1920:fps=${fps},format=yuv420p[v]`,
  '-map', '[v]', '-t', String(duration), '-an', '-r', String(fps), '-c:v', 'libx264',
  '-preset', 'slow', '-crf', '17', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', output,
];
const result = spawnSync('ffmpeg', args, { stdio: 'inherit' });
if (result.status !== 0) throw new Error('Could not render the V2 prototype.');
console.log(`Rendered prototype: ${output}`);
