import { mkdir, rm } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '..');
const work = path.join(here, 'dist/v3-prototype-work');
const sourceFrames = path.join(work, 'ui-frames');
const outputFrames = path.join(work, 'scene-frames');
const source = path.join(root, 'public/video/growpos-demo.mp4');
const output = path.join(here, 'dist/growpos-v3-prototype.mp4');
const contact = path.join(here, 'dist/growpos-v3-prototype-contact-sheet.png');
const font = '/System/Library/Fonts/Supplemental/Arial.ttf';
const fps = 60;
const duration = 8;

await mkdir(sourceFrames, { recursive: true });
if (process.env.GROWPOS_FORCE_RENDER === '1') await rm(outputFrames, { recursive: true, force: true });
await mkdir(outputFrames, { recursive: true });
await mkdir(path.dirname(output), { recursive: true });

function run(bin, args, label) {
  const result = spawnSync(bin, args, { stdio: 'inherit' });
  if (result.status !== 0) throw new Error(`${label} failed (status ${result.status}).`);
}

run('ffmpeg', [
  '-hide_banner', '-loglevel', 'error', '-y', '-ss', '7.4', '-i', source, '-t', '5.0',
  '-vf', 'fps=30', '-start_number', '1', path.join(sourceFrames, 'ui-%04d.png'),
], 'Extracting authentic UI frames');

if (process.env.GROWPOS_FORCE_RENDER === '1' || !existsSync(path.join(outputFrames, 'frame-0479.png'))) {
  run('swift', [
    '-module-cache-path', '/private/tmp/swift-cache', path.join(here, 'render-v3-scene.swift'),
    outputFrames, path.join(sourceFrames, 'ui-0001.png'), '7.5', sourceFrames,
    String(fps * duration), '1080', '1920',
  ], 'Rendering SceneKit prototype');
}

const titlePng = path.join(work, 'cashier-title.png');
run('magick', [
  '-size', '1080x1920', 'xc:none', '-font', font, '-fill', '#f2f7f3', '-pointsize', '46',
  '-gravity', 'northwest', '-annotate', '+88+390', 'Jualan jadi lebih praktis.', titlePng,
], 'Creating editorial type layer');
run('ffmpeg', [
  '-hide_banner', '-loglevel', 'error', '-y', '-framerate', String(fps), '-i', path.join(outputFrames, 'frame-%04d.png'),
  '-loop', '1', '-framerate', String(fps), '-i', titlePng,
  '-filter_complex', "[0:v]drawbox=x=150:y=1370:w='min(760\\,1+t*900)':h=4:color=0x2bb77a@0.95:t=fill:enable='between(t,0.08,2.9)'[flow];[1:v]format=rgba,fade=t=in:st=5.0:d=0.6:alpha=1,fade=t=out:st=7.25:d=0.55:alpha=1[title];[flow][title]overlay=0:0:shortest=1[v]",
  '-map', '[v]', '-t', String(duration), '-an', '-r', String(fps), '-c:v', 'libx264', '-preset', 'slow', '-crf', '17',
  '-pix_fmt', 'yuv420p', '-movflags', '+faststart', output,
], 'Encoding prototype MP4');

run('ffmpeg', [
  '-hide_banner', '-loglevel', 'error', '-y', '-i', output, '-vf', "select='not(mod(n,30))',scale=270:480,tile=4x4",
  '-frames:v', '1', contact,
], 'Building prototype contact sheet');

console.log(`Prototype: ${output}`);
console.log(`Contact sheet: ${contact}`);
