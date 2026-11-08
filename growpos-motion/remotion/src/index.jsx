import React from 'react';
import {Composition, registerRoot} from 'remotion';
import {GrowPOSScene} from './scene.jsx';

const Root = () => (
  <Composition
    id="GrowPOS-ArtOfSelling"
    component={GrowPOSScene}
    durationInFrames={372}
    fps={60}
    width={1080}
    height={1920}
  />
);

registerRoot(Root);
