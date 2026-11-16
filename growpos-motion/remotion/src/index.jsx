import React from 'react';
import {Composition, registerRoot} from 'remotion';
import {GrowPOSReel} from './reel.jsx';

const Root = () => (
  <Composition
    id="GrowPOS-EverySaleMatters"
    component={GrowPOSReel}
    durationInFrames={900}
    fps={30}
    width={1080}
    height={1920}
  />
);

registerRoot(Root);
