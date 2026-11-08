import AppKit
import SceneKit
import Foundation
import Metal

let args = CommandLine.arguments
let output = args.count > 1 ? args[1] : "growpos-motion/dist/v3-scene-test.png"
let screenPath = args.count > 2 ? args[2] : "/private/tmp/growpos-inspect/frame10.png"
let time = args.count > 3 ? Double(args[3]) ?? 7.5 : 7.5
let sequenceMode = args.count > 4
let uiSequence = sequenceMode ? args[4] : ""
let frameCount = sequenceMode ? (Int(args[5]) ?? 480) : 1
let width = sequenceMode ? (Int(args[6]) ?? 1080) : 1080
let height = sequenceMode ? (Int(args[7]) ?? 1920) : 1920
let filmMode = sequenceMode && args.count > 8 && args[8] == "film"
let fps = 60.0

func smooth(_ start: Double, _ end: Double, _ t: Double) -> Double {
    let p = max(0, min(1, (t - start) / (end - start)))
    return p * p * (3 - 2 * p)
}
let scene = SCNScene()
scene.background.contents = NSColor(calibratedRed: 0.012, green: 0.021, blue: 0.019, alpha: 1)

func mat(_ color: NSColor, _ rough: CGFloat, _ metal: CGFloat) -> SCNMaterial {
    let m = SCNMaterial(); m.diffuse.contents = color; m.roughness.contents = rough; m.metalness.contents = metal; m.lightingModel = .physicallyBased; return m
}
func box(_ name: String, _ size: SCNVector3, _ position: SCNVector3, _ radius: CGFloat, _ material: SCNMaterial, _ parent: SCNNode) -> SCNNode {
    let g = SCNBox(width: CGFloat(size.x), height: CGFloat(size.y), length: CGFloat(size.z), chamferRadius: radius); g.materials = [material]
    let n = SCNNode(geometry: g); n.name = name; n.position = position; parent.addChildNode(n); return n
}

let silver = mat(NSColor(calibratedRed: 0.43, green: 0.49, blue: 0.46, alpha: 1), 0.25, 0.9)
let darkSilver = mat(NSColor(calibratedWhite: 0.12, alpha: 1), 0.3, 0.75)
let bezelMat = mat(NSColor(calibratedWhite: 0.008, alpha: 1), 0.12, 0.08)
let keyMat = mat(NSColor(calibratedRed: 0.022, green: 0.031, blue: 0.027, alpha: 1), 0.48, 0.04)
let screenMat = SCNMaterial(); screenMat.lightingModel = .constant; screenMat.diffuse.contents = NSImage(contentsOfFile: screenPath) ?? NSColor.white; screenMat.isDoubleSided = true

let laptop = SCNNode(); laptop.name = "laptop"; laptop.eulerAngles.y = -0.25; scene.rootNode.addChildNode(laptop)
let deck = box("unibody-deck", SCNVector3(2.34, 0.075, 1.48), SCNVector3(0, 0, 0.73), 0.06, silver, laptop)
_ = deck
_ = box("base-lip", SCNVector3(2.30, 0.045, 1.45), SCNVector3(0, -0.05, 0.73), 0.04, darkSilver, laptop)
_ = box("hinge", SCNVector3(2.0, 0.07, 0.09), SCNVector3(0, 0.025, 0.025), 0.035, darkSilver, laptop)

let lid = SCNNode(); lid.name = "display-lid"; lid.position = SCNVector3(0, 0.05, 0.02); lid.eulerAngles.x = -0.11; laptop.addChildNode(lid)
_ = box("aluminum-lid", SCNVector3(2.34, 1.52, 0.07), SCNVector3(0, 0.76, 0), 0.07, silver, lid)
_ = box("black-glass-bezel", SCNVector3(2.25, 1.43, 0.023), SCNVector3(0, 0.76, 0.045), 0.045, bezelMat, lid)
let screenGeo = SCNPlane(width: 2.16, height: 1.295); screenGeo.materials = [screenMat]
let screen = SCNNode(geometry: screenGeo); screen.name = "authentic-growpos-screen"; screen.position = SCNVector3(0, 0.76, 0.059); lid.addChildNode(screen)
let camDot = SCNSphere(radius: 0.012); camDot.firstMaterial = mat(NSColor(calibratedWhite: 0.01, alpha: 1), 0.2, 0.1)
let cam = SCNNode(geometry: camDot); cam.position = SCNVector3(0, 1.465, 0.06); lid.addChildNode(cam)

for row in 0..<5 {
    let count = row == 4 ? 9 : 14
    for col in 0..<count {
        let width: CGFloat = row == 4 && col == 4 ? 0.61 : 0.115
        let x = CGFloat(col - count / 2) * 0.148
        let z = 0.31 + Double(row) * 0.155
        _ = box("key-\(row)-\(col)", SCNVector3(width, 0.018, 0.085), SCNVector3(x, 0.048, z), 0.014, keyMat, laptop)
    }
}
_ = box("trackpad", SCNVector3(0.69, 0.009, 0.39), SCNVector3(0, 0.042, 1.10), 0.045, darkSilver, laptop)

let floorMat = mat(NSColor(calibratedRed: 0.026, green: 0.043, blue: 0.038, alpha: 1), 0.27, 0.12)
let floor = SCNFloor(); floor.reflectivity = 0.012; floor.reflectionFalloffEnd = 2.5; floor.firstMaterial = floorMat
let floorNode = SCNNode(geometry: floor); floorNode.position = SCNVector3(0, -0.13, 0); scene.rootNode.addChildNode(floorNode)

func aimedLight(_ color: NSColor, _ intensity: CGFloat, _ position: SCNVector3, _ target: SCNVector3) {
    let l = SCNLight(); l.type = .omni; l.color = color; l.intensity = intensity
    let n = SCNNode(); n.light = l; n.position = position
    let aim = SCNNode(); aim.position = target; scene.rootNode.addChildNode(aim); n.constraints = [SCNLookAtConstraint(target: aim)]; scene.rootNode.addChildNode(n)
}
aimedLight(NSColor(calibratedRed: 0.85, green: 0.94, blue: 0.88, alpha: 1), 640, SCNVector3(-3, 4.2, 2.8), SCNVector3(0, 0.5, 0.6))
aimedLight(NSColor(calibratedRed: 0.24, green: 0.7, blue: 0.45, alpha: 1), 80, SCNVector3(2.4, 1.7, -2.2), SCNVector3(0, 0.7, 0.4))
let ambient = SCNLight(); ambient.type = .ambient; ambient.color = NSColor(calibratedWhite: 0.4, alpha: 1); ambient.intensity = 210
let ambientNode = SCNNode(); ambientNode.light = ambient; scene.rootNode.addChildNode(ambientNode)

let camera = SCNCamera(); camera.fieldOfView = 33; camera.wantsHDR = true; camera.bloomIntensity = 0.05; camera.bloomThreshold = 1.5; camera.bloomBlurRadius = 4
let cameraNode = SCNNode(); cameraNode.camera = camera
let pull = max(0, min(1, (time - 2.4) / 5.0)); let eased = pull * pull * (3 - 2 * pull)
cameraNode.position = SCNVector3(0.08 + eased * 0.10, 1.10 + eased * 0.85, 2.05 + eased * 4.65)
let target = SCNNode(); target.position = SCNVector3(0, 0.72, 0.63); scene.rootNode.addChildNode(target)
cameraNode.constraints = [SCNLookAtConstraint(target: target)]; scene.rootNode.addChildNode(cameraNode)

// Opening signal: a deliberately restrained trace that resolves into the real interface.
let p = max(0, min(1, (time - 0.08) / 0.82))
if p > 0 && p < 1 {
    let glow = mat(NSColor(calibratedRed: 0.12, green: 0.76, blue: 0.40, alpha: 1), 0.24, 0.0); glow.emission.contents = NSColor(calibratedRed: 0.08, green: 0.52, blue: 0.24, alpha: 1)
    let line = SCNBox(width: 1.85 * p, height: 0.016, length: 0.012, chamferRadius: 0.008); line.materials = [glow]
    let lineNode = SCNNode(geometry: line); lineNode.position = SCNVector3(-0.925 + 0.925 * p, 1.17, 0.09); lid.addChildNode(lineNode)
    let dotGeo = SCNSphere(radius: 0.018); dotGeo.firstMaterial = glow
    let dot = SCNNode(geometry: dotGeo); dot.position = SCNVector3(-0.92 + 1.85 * p, 1.17, 0.073); lid.addChildNode(dot)
}
screen.opacity = time < 0.92 ? CGFloat(max(0, min(1, (time - 0.38) / 0.54))) : 1

guard let gpu = MTLCreateSystemDefaultDevice() else { fatalError("Metal GPU unavailable") }
let renderer = SCNRenderer(device: gpu, options: nil); renderer.scene = scene; renderer.pointOfView = cameraNode; renderer.autoenablesDefaultLighting = false
let fm = FileManager.default
if sequenceMode { try fm.createDirectory(atPath: output, withIntermediateDirectories: true) }
for i in 0..<frameCount {
    let t = sequenceMode ? Double(i) / fps : time
    if sequenceMode {
        let sourceSecond: Double
        if filmMode {
            if t < 6.5 { sourceSecond = 7.4 + max(0, min(3.1, t - 0.7)) }
            else if t < 13 { sourceSecond = 10.5 }
            else if t < 24 { sourceSecond = 14.0 + (t - 13) * 1.08 }
            else { sourceSecond = 25.9 }
        } else {
            sourceSecond = max(7.4, min(10.5, 7.4 + max(0, t - 0.7)))
        }
        let sourceIndex = Int((sourceSecond - 7.4) * 30.0 + 0.5) + 1
        let texturePath = String(format: "%@/ui-%04d.png", uiSequence, sourceIndex)
        if let ui = NSImage(contentsOfFile: texturePath) { screenMat.diffuse.contents = ui }
        screen.opacity = t < 0.92 ? CGFloat(max(0, min(1, (t - 0.38) / 0.54))) : 1
    }
    let z: Double
    let orbit: Double
    if filmMode {
        if t < 6 { z = 2.05; orbit = 0 }
        else if t < 13 { let p = smooth(6, 13, t); z = 2.05 + p * 5.65; orbit = p * 0.16 }
        else if t < 15.5 { let p = smooth(13, 15.5, t); z = 7.7 - p * 3.75; orbit = 0.16 - p * 0.08 }
        else if t < 22.6 { z = 3.95; orbit = 0.08 + smooth(19, 22.6, t) * 0.12 }
        else { let p = smooth(22.6, 27.8, t); z = 3.95 + p * 3.75; orbit = 0.20 - p * 0.08 }
    } else {
        let p = sequenceMode ? smooth(3.0, 8.0, t) : smooth(2.4, 7.4, t)
        z = 2.05 + p * 6.15; orbit = p * 0.10
    }
    laptop.eulerAngles.y = -0.25 + sin(t * 0.38) * 0.025
    cameraNode.position = SCNVector3(0.08 + orbit, 1.05 + (z - 2.05) * 0.14, z)
    target.position = SCNVector3(orbit * 0.42, 0.72 + (z - 2.05) * 0.025, 0.63)
    let trace = max(0, min(1, (t - 0.08) / 0.82))
    if let lineNode = lid.childNode(withName: "signal-line", recursively: false) { lineNode.removeFromParentNode() }
    if let dotNode = lid.childNode(withName: "signal-dot", recursively: false) { dotNode.removeFromParentNode() }
    if trace > 0 && trace < 1 {
        let glow = mat(NSColor(calibratedRed: 0.12, green: 0.76, blue: 0.40, alpha: 1), 0.24, 0.0)
        glow.emission.contents = NSColor(calibratedRed: 0.08, green: 0.52, blue: 0.24, alpha: 1)
        let line = SCNBox(width: 1.85 * trace, height: 0.016, length: 0.012, chamferRadius: 0.008); line.materials = [glow]
        let lineNode = SCNNode(geometry: line); lineNode.name = "signal-line"; lineNode.position = SCNVector3(-0.925 + 0.925 * trace, 1.17, 0.09); lid.addChildNode(lineNode)
        let dotGeo = SCNSphere(radius: 0.012); dotGeo.firstMaterial = glow
        let dotNode = SCNNode(geometry: dotGeo); dotNode.name = "signal-dot"; dotNode.position = SCNVector3(-0.92 + 1.85 * trace, 1.17, 0.073); lid.addChildNode(dotNode)
    }
    let image = renderer.snapshot(atTime: t, with: CGSize(width: width, height: height), antialiasingMode: .multisampling4X)
    guard let tiff = image.tiffRepresentation, let rep = NSBitmapImageRep(data: tiff), let png = rep.representation(using: .png, properties: [:]) else { fatalError("SceneKit did not return frame \(i)") }
    let file = sequenceMode ? String(format: "%@/frame-%04d.png", output, i) : output
    try png.write(to: URL(fileURLWithPath: file))
    if i % 30 == 0 { print("rendered \(i)/\(frameCount)") }
}
