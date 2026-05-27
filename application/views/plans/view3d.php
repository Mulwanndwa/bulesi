<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>3D — <?= htmlspecialchars($plan->title) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0a0a14;overflow:hidden;font-family:system-ui,sans-serif;height:100vh}
canvas{display:block}

#topbar{position:fixed;top:0;left:0;right:0;height:48px;background:rgba(0,0,0,.55);
  backdrop-filter:blur(10px);display:flex;align-items:center;gap:10px;padding:0 16px;z-index:200;color:#fff}
.tb{background:rgba(255,255,255,.1);border:none;border-radius:6px;color:#fff;
  padding:5px 11px;font-size:.8rem;cursor:pointer;display:flex;align-items:center;gap:5px}
.tb:hover{background:rgba(255,255,255,.2)}
#plan-name{font-weight:600;font-size:.95rem;margin-left:4px}
#loading{position:fixed;inset:0;background:#0a0a14;display:flex;align-items:center;justify-content:center;
  flex-direction:column;gap:14px;z-index:300;color:#fff;font-size:1.1rem}
.spinner{width:40px;height:40px;border:3px solid rgba(255,255,255,.15);border-top-color:#e94560;
  border-radius:50%;animation:spin .7s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* Controls panel */
#ctrl{position:fixed;top:60px;right:14px;width:220px;background:rgba(10,10,20,.82);
  backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.1);border-radius:12px;
  padding:14px;z-index:200;color:#fff;font-size:.82rem}
#ctrl h3{font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.4);margin-bottom:10px}
.row{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;gap:6px}
.row label{color:rgba(255,255,255,.7);white-space:nowrap}
.sl{flex:1;accent-color:#e94560}
.val{color:rgba(255,255,255,.5);font-size:.75rem;min-width:30px;text-align:right}
.tog{position:relative;width:36px;height:20px;flex-shrink:0}
.tog input{opacity:0;width:0;height:0}
.tog span{position:absolute;inset:0;background:rgba(255,255,255,.15);border-radius:10px;cursor:pointer;transition:.2s}
.tog input:checked+span{background:#e94560}
.tog span:before{content:'';position:absolute;width:14px;height:14px;left:3px;top:3px;
  background:#fff;border-radius:50%;transition:.2s}
.tog input:checked+span:before{left:19px}
.sep{height:1px;background:rgba(255,255,255,.1);margin:10px 0}
.view-btns{display:flex;gap:5px;flex-wrap:wrap}
.vb{flex:1;background:rgba(255,255,255,.08);border:none;color:rgba(255,255,255,.7);
  border-radius:5px;padding:5px 4px;font-size:.72rem;cursor:pointer;white-space:nowrap}
.vb:hover{background:rgba(255,255,255,.18);color:#fff}
.vb.active{background:#e94560;color:#fff}

/* Info overlay */
#info{position:fixed;bottom:14px;left:50%;transform:translateX(-50%);
  background:rgba(0,0,0,.5);color:rgba(255,255,255,.6);font-size:.72rem;
  padding:4px 14px;border-radius:10px;z-index:200;pointer-events:none}
#stats{position:fixed;bottom:14px;left:14px;color:rgba(255,255,255,.3);font-size:.7rem;z-index:200}
</style>
</head>
<body>

<!-- Loading screen -->
<div id="loading"><div class="spinner"></div><span>Building 3D model…</span></div>

<!-- Top bar -->
<div id="topbar">
    <button class="tb" onclick="location.href='<?= $base ?>plans/draw/<?= $plan->id ?>'">
        ← Draw
    </button>
    <button class="tb" onclick="location.href='<?= $base ?>plans'">Plans</button>
    <span id="plan-name"><?= htmlspecialchars($plan->title) ?></span>
    <div style="margin-left:auto;display:flex;gap:8px">
        <button class="tb" id="btn-rebuild" onclick="rebuild()">⟳ Rebuild</button>
        <button class="tb" onclick="screenshot()">⬇ Screenshot</button>
    </div>
</div>

<!-- Controls -->
<div id="ctrl">
    <h3>Settings</h3>

    <div class="row">
        <label>Wall Height</label>
        <input type="range" class="sl" id="sl-wh" min="1.5" max="5" step="0.1" value="2.5">
        <span class="val" id="v-wh">2.5m</span>
    </div>
    <div class="row">
        <label>Wall Thickness</label>
        <input type="range" class="sl" id="sl-wt" min="0.05" max="0.5" step="0.05" value="0.15">
        <span class="val" id="v-wt">0.15m</span>
    </div>
    <div class="sep"></div>
    <div class="row"><label>Show Floor</label>   <label class="tog"><input type="checkbox" id="cb-floor" checked><span></span></label></div>
    <div class="row"><label>Show Ceiling</label> <label class="tog"><input type="checkbox" id="cb-ceil"><span></span></label></div>
    <div class="row"><label>Shadows</label>      <label class="tog"><input type="checkbox" id="cb-shad" checked><span></span></label></div>
    <div class="row"><label>Wireframe</label>    <label class="tog"><input type="checkbox" id="cb-wire"><span></span></label></div>
    <div class="row"><label>Ground</label>       <label class="tog"><input type="checkbox" id="cb-gnd" checked><span></span></label></div>
    <div class="sep"></div>
    <h3>Camera</h3>
    <div class="view-btns">
        <button class="vb active" onclick="setView('iso')">Isometric</button>
        <button class="vb" onclick="setView('top')">Top</button>
        <button class="vb" onclick="setView('front')">Front</button>
        <button class="vb" onclick="setView('side')">Side</button>
    </div>
    <div class="sep"></div>
    <div class="row">
        <label>Sky</label>
        <input type="color" id="cp-sky" value="#87ceeb" style="width:32px;height:24px;border:none;border-radius:4px;cursor:pointer;background:none">
    </div>
</div>

<div id="info">Drag to rotate · Scroll to zoom · Right-drag to pan</div>
<div id="stats"></div>

<!-- Three.js via importmap -->
<script type="importmap">
{
  "imports": {
    "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
    "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
  }
}
</script>
<script type="module">
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

// ── Plan data ─────────────────────────────────────────────────────────
const SHAPES    = <?= $plan->plan_data ? $plan->plan_data : '[]' ?>;
const GRID_SIZE = <?= (int)$plan->grid_size ?>;
const S         = 0.5 / GRID_SIZE;   // world units → metres

// ── Scene ─────────────────────────────────────────────────────────────
const scene    = new THREE.Scene();
scene.background = new THREE.Color(0x87ceeb);
scene.fog        = new THREE.FogExp2(0x87ceeb, 0.012);

const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
renderer.setSize(innerWidth, innerHeight);
renderer.shadowMap.enabled = true;
renderer.shadowMap.type    = THREE.PCFSoftShadowMap;
renderer.toneMapping       = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.1;
document.body.appendChild(renderer.domElement);

// ── Camera ────────────────────────────────────────────────────────────
const camera = new THREE.PerspectiveCamera(45, innerWidth/innerHeight, 0.1, 2000);
camera.position.set(20, 18, 20);

const controls = new OrbitControls(camera, renderer.domElement);
controls.enableDamping   = true;
controls.dampingFactor   = 0.06;
controls.maxPolarAngle   = Math.PI / 2.05;
controls.minDistance     = 2;
controls.maxDistance     = 300;

// ── Lighting ──────────────────────────────────────────────────────────
const hemi = new THREE.HemisphereLight(0x87ceeb, 0xa0845c, 0.8);
scene.add(hemi);

const sun = new THREE.DirectionalLight(0xfff5e0, 2.0);
sun.position.set(60, 100, 40);
sun.castShadow = true;
sun.shadow.mapSize.set(2048, 2048);
sun.shadow.camera.near   = 0.5;
sun.shadow.camera.far    = 400;
sun.shadow.camera.left   = -80;
sun.shadow.camera.right  =  80;
sun.shadow.camera.top    =  80;
sun.shadow.camera.bottom = -80;
sun.shadow.bias          = -0.001;
scene.add(sun);

// Fill light from opposite side
const fill = new THREE.DirectionalLight(0xddeeff, 0.4);
fill.position.set(-40, 30, -30);
scene.add(fill);

// ── Ground plane ──────────────────────────────────────────────────────
const gndMat  = new THREE.MeshPhongMaterial({ color: 0x7cad5a });
const gndMesh = new THREE.Mesh(new THREE.PlaneGeometry(500, 500), gndMat);
gndMesh.rotation.x = -Math.PI / 2;
gndMesh.receiveShadow = true;
scene.add(gndMesh);

// ── Building group (rebuilt on settings change) ───────────────────────
let building = new THREE.Group();
scene.add(building);

// Materials
const wallMat    = () => new THREE.MeshPhongMaterial({ color: 0xe8ddd0, side: THREE.DoubleSide });
const floorMat   = (hex, op) => new THREE.MeshPhongMaterial({ color: hex, transparent: op<1, opacity: op, side: THREE.DoubleSide });
const winMat     = new THREE.MeshPhongMaterial({ color: 0x9dd5f5, transparent: true, opacity: 0.55, emissive: 0x3399cc, emissiveIntensity: 0.15 });
const doorMat    = new THREE.MeshPhongMaterial({ color: 0x7b5230 });
const stairMat   = new THREE.MeshPhongMaterial({ color: 0xb0a090 });
const ceilMat    = new THREE.MeshPhongMaterial({ color: 0xf5f0ea, side: THREE.DoubleSide });

// ── Helper: hex string → THREE.Color int ─────────────────────────────
function hexInt(h){ return parseInt((h||'#c0c0c0').replace('#',''), 16); }

function hexToRgb(h) {
    const v = hexInt(h||'#dbeafe');
    return [(v>>16&255)/255, (v>>8&255)/255, (v&255)/255];
}

// ── Get plan bounds ───────────────────────────────────────────────────
function bounds() {
    let x1=Infinity,z1=Infinity,x2=-Infinity,z2=-Infinity;
    SHAPES.forEach(s => {
        const pts = s.type==='wall'||s.type==='window'
            ? [[s.x1,s.y1],[s.x2,s.y2]]
            : (s.type==='room'||s.type==='stair'
                ? [[s.x,s.y],[s.x+Math.abs(s.w||0),s.y+Math.abs(s.h||0)]]
                : s.type==='door' ? [[s.hx-s.r,s.hy-s.r],[s.hx+s.r,s.hy+s.r]] : []);
        pts.forEach(([px,py])=>{ x1=Math.min(x1,px*S); z1=Math.min(z1,py*S); x2=Math.max(x2,px*S); z2=Math.max(z2,py*S); });
    });
    if (!isFinite(x1)) return {cx:0,cz:0,size:10};
    return {cx:(x1+x2)/2, cz:(z1+z2)/2, size:Math.max(x2-x1,z2-z1,4)};
}

// ── Build 3D ─────────────────────────────────────────────────────────
function rebuild() {
    // Remove old building
    scene.remove(building);
    building = new THREE.Group();
    scene.add(building);

    const WH  = parseFloat(document.getElementById('sl-wh').value);
    const WT  = parseFloat(document.getElementById('sl-wt').value);
    const showFloor  = document.getElementById('cb-floor').checked;
    const showCeil   = document.getElementById('cb-ceil').checked;
    const showGnd    = document.getElementById('cb-gnd').checked;
    const wire       = document.getElementById('cb-wire').checked;
    const shadows    = document.getElementById('cb-shad').checked;

    sun.castShadow = shadows;
    gndMesh.visible = showGnd;

    // Collect all floor regions from rooms
    const floorRooms = [];

    SHAPES.forEach(s => {
        switch (s.type) {

            case 'wall': {
                const x1=s.x1*S, z1=s.y1*S, x2=s.x2*S, z2=s.y2*S;
                const len = Math.hypot(x2-x1, z2-z1);
                if (len < 0.01) break;
                const ang = Math.atan2(z2-z1, x2-x1);
                const mat = wallMat(); mat.wireframe = wire;
                const m = new THREE.Mesh(new THREE.BoxGeometry(len, WH, WT), mat);
                m.position.set((x1+x2)/2, WH/2, (z1+z2)/2);
                m.rotation.y = -ang;
                m.castShadow = shadows; m.receiveShadow = shadows;
                building.add(m);
                break;
            }

            case 'room': {
                const rx=s.x*S, rz=s.y*S, rw=Math.abs(s.w*S), rd=Math.abs(s.h*S);
                const rgb = hexToRgb(s.fill||'#dbeafe');
                const op  = Math.max(0.35, (s.opacity||25)/100);
                floorRooms.push({rx,rz,rw,rd,rgb,op,label:s.label});

                if (showFloor) {
                    const mat = new THREE.MeshPhongMaterial({
                        color: new THREE.Color(...rgb),
                        transparent: true, opacity: op,
                        side: THREE.DoubleSide,
                    });
                    mat.wireframe = wire;
                    const geo = new THREE.PlaneGeometry(rw, rd);
                    const m   = new THREE.Mesh(geo, mat);
                    m.rotation.x = -Math.PI/2;
                    m.position.set(rx+rw/2, 0.02, rz+rd/2);
                    m.receiveShadow = shadows;
                    building.add(m);
                }
                if (showCeil) {
                    const mat = ceilMat.clone(); mat.wireframe = wire;
                    const m   = new THREE.Mesh(new THREE.PlaneGeometry(rw, rd), mat);
                    m.rotation.x = Math.PI/2;
                    m.position.set(rx+rw/2, WH, rz+rd/2);
                    building.add(m);
                }
                break;
            }

            case 'door': {
                const hx=s.hx*S, hz=s.hy*S, r=s.r*S;
                const dH = Math.min(WH*0.88, 2.1);
                // Hinge post
                const post = new THREE.Mesh(new THREE.CylinderGeometry(0.04,0.04,dH,8), doorMat.clone());
                post.position.set(hx, dH/2, hz);
                building.add(post);
                // Door panel
                const mat = doorMat.clone(); mat.wireframe = wire;
                const m   = new THREE.Mesh(new THREE.BoxGeometry(r, dH, 0.05), mat);
                m.position.set(hx + r/2*Math.cos(s.ang), dH/2, hz + r/2*Math.sin(s.ang));
                m.rotation.y = -s.ang;
                m.castShadow = shadows;
                building.add(m);
                // Swing arc (thin plane)
                const arcMat = new THREE.MeshBasicMaterial({color:0x7b5230, transparent:true, opacity:0.2, side:THREE.DoubleSide});
                const arcGeo = new THREE.RingGeometry(r-0.02, r, 32, 1, s.ang, s.dir*Math.PI/2);
                const arc    = new THREE.Mesh(arcGeo, arcMat);
                arc.rotation.x = -Math.PI/2;
                arc.position.set(hx, 0.05, hz);
                building.add(arc);
                break;
            }

            case 'window': {
                const x1=s.x1*S, z1=s.y1*S, x2=s.x2*S, z2=s.y2*S;
                const len=Math.hypot(x2-x1,z2-z1);
                if (len<0.01) break;
                const ang=Math.atan2(z2-z1,x2-x1);
                const wH=WH*0.45, wY=WH*0.5;
                const mat=winMat.clone(); mat.wireframe=false; // keep windows see-through
                const m=new THREE.Mesh(new THREE.PlaneGeometry(len, wH), mat);
                m.position.set((x1+x2)/2, wY, (z1+z2)/2);
                m.rotation.y=-ang;
                building.add(m);
                // Frame
                const frameMat=new THREE.MeshPhongMaterial({color:0xffffff});
                const frame=new THREE.Mesh(new THREE.BoxGeometry(len, wH, 0.04), frameMat);
                frame.position.copy(m.position); frame.rotation.copy(m.rotation);
                building.add(frame);
                break;
            }

            case 'stair': {
                const rx=s.x*S, rz=s.y*S, rw=Math.abs(s.w*S), rd=Math.abs(s.h*S);
                const steps=s.steps||8;
                const totalH=WH*0.45;
                const isH = rw >= rd;
                for (let i=0;i<steps;i++) {
                    const sh = totalH * (i+1) / steps;
                    const sw = isH ? rw/steps : rw;
                    const sd = isH ? rd         : rd/steps;
                    const mat=stairMat.clone(); mat.wireframe=wire;
                    const m=new THREE.Mesh(new THREE.BoxGeometry(sw,sh,sd), mat);
                    m.position.set(
                        rx + (isH ? (i+0.5)*rw/steps : rw/2),
                        sh/2,
                        rz + (isH ? rd/2 : (i+0.5)*rd/steps)
                    );
                    m.castShadow=shadows; m.receiveShadow=shadows;
                    building.add(m);
                }
                break;
            }

            case 'roof': {
                const ov   = Math.abs((s.overhang||0)*S);
                const rx   = s.x*S - ov,   rz   = s.y*S - ov;
                const rw   = Math.abs(s.w*S) + 2*ov;
                const rd   = Math.abs(s.h*S) + 2*ov;
                if (rw<0.05||rd<0.05) break;
                const pitch = ((s.pitch||30)*Math.PI/180);
                const isEW  = (s.direction||'ew')==='ew';
                const halfSpan = isEW ? rd/2 : rw/2;
                const rH   = halfSpan * Math.tan(pitch);
                const col  = hexInt(s.color||'#b07040');
                const rt   = s.roof_type||'gable';
                let   tris;
                if      (rt==='gable') tris = roofGable(rx,rz,rw,rd,WH,rH,isEW);
                else if (rt==='hip')   tris = roofHip  (rx,rz,rw,rd,WH,rH,isEW);
                else if (rt==='shed')  tris = roofShed (rx,rz,rw,rd,WH,rH,isEW);
                else                  tris = null; // flat handled separately

                if (tris) {
                    const geo = new THREE.BufferGeometry();
                    geo.setAttribute('position', new THREE.Float32BufferAttribute(tris,3));
                    geo.computeVertexNormals();
                    const mat = new THREE.MeshPhongMaterial({color:col, side:THREE.DoubleSide, wireframe:wire});
                    const m   = new THREE.Mesh(geo, mat);
                    m.castShadow=shadows; m.receiveShadow=shadows;
                    building.add(m);
                } else {
                    // Flat roof — slab
                    const thick = 0.25;
                    const mat = new THREE.MeshPhongMaterial({color:col, wireframe:wire});
                    const m   = new THREE.Mesh(new THREE.BoxGeometry(rw, thick, rd), mat);
                    m.position.set(rx+rw/2, WH+thick/2, rz+rd/2);
                    m.castShadow=shadows; m.receiveShadow=shadows;
                    building.add(m);
                }
                break;
            }
        }
    });

    // ── Roof geometry helpers ─────────────────────────────────────────
    function roofGable(x,z,w,d,wH,rH,isEW) {
        if (isEW) {
            const Rwx=x, Rwy=wH+rH, Rwz=z+d/2;
            const Rex=x+w, Rey=wH+rH, Rez=z+d/2;
            return [
                x,wH,z,    Rwx,Rwy,Rwz, Rex,Rey,Rez,   // N slope tri1
                x,wH,z,    Rex,Rey,Rez,  x+w,wH,z,     // N slope tri2
                x,wH,z+d,  x+w,wH,z+d,  Rex,Rey,Rez,   // S slope tri1
                x,wH,z+d,  Rex,Rey,Rez,  Rwx,Rwy,Rwz,  // S slope tri2
                x,wH,z,    x,wH,z+d,    Rwx,Rwy,Rwz,   // W gable
                x+w,wH,z,  Rex,Rey,Rez,  x+w,wH,z+d,   // E gable
            ];
        } else {
            const Rnx=x+w/2, Rny=wH+rH, Rnz=z;
            const Rsx=x+w/2, Rsy=wH+rH, Rsz=z+d;
            return [
                x,wH,z,    x,wH,z+d,    Rsx,Rsy,Rsz,
                x,wH,z,    Rsx,Rsy,Rsz, Rnx,Rny,Rnz,
                x+w,wH,z,  Rnx,Rny,Rnz, Rsx,Rsy,Rsz,
                x+w,wH,z,  Rsx,Rsy,Rsz, x+w,wH,z+d,
                x,wH,z,    x+w,wH,z,    Rnx,Rny,Rnz,
                x,wH,z+d,  x+w,wH,z+d,  Rsx,Rsy,Rsz,  // note: winding for inside visibility
                x,wH,z+d,  Rsx,Rsy,Rsz,  x+w,wH,z+d,
            ];
        }
    }

    function roofHip(x,z,w,d,wH,rH,isEW) {
        const hi = isEW ? Math.min(d/2*0.9, w*0.2) : Math.min(w/2*0.9, d*0.2);
        if (isEW) {
            const Rwx=x+hi, Rwy=wH+rH, Rwz=z+d/2;
            const Rex=x+w-hi, Rey=wH+rH, Rez=z+d/2;
            return [
                x,wH,z,     x+w,wH,z,    Rex,Rey,Rez,   // N slope tri1
                x,wH,z,     Rex,Rey,Rez,  Rwx,Rwy,Rwz,  // N slope tri2
                x,wH,z+d,   Rwx,Rwy,Rwz, Rex,Rey,Rez,   // S slope tri1
                x,wH,z+d,   Rex,Rey,Rez,  x+w,wH,z+d,   // S slope tri2
                x,wH,z,     Rwx,Rwy,Rwz, x,wH,z+d,      // W hip
                x+w,wH,z,   x+w,wH,z+d,  Rex,Rey,Rez,   // E hip
            ];
        } else {
            const Rnx=x+w/2, Rny=wH+rH, Rnz=z+hi;
            const Rsx=x+w/2, Rsy=wH+rH, Rsz=z+d-hi;
            return [
                x,wH,z,     x,wH,z+d,    Rsx,Rsy,Rsz,
                x,wH,z,     Rsx,Rsy,Rsz, Rnx,Rny,Rnz,
                x+w,wH,z,   Rnx,Rny,Rnz, Rsx,Rsy,Rsz,
                x+w,wH,z,   Rsx,Rsy,Rsz, x+w,wH,z+d,
                x,wH,z,     x+w,wH,z,    Rnx,Rny,Rnz,
                x,wH,z+d,   Rsx,Rsy,Rsz, x+w,wH,z+d,
            ];
        }
    }

    function roofShed(x,z,w,d,wH,rH,isEW) {
        if (isEW) {
            return [
                x,wH+rH,z,   x+w,wH+rH,z,  x+w,wH,z+d,
                x,wH+rH,z,   x+w,wH,z+d,   x,wH,z+d,
                x,wH,z+d,    x,wH+rH,z,    x,wH,z,
                x+w,wH,z+d,  x+w,wH,z,     x+w,wH+rH,z,
            ];
        } else {
            return [
                x,wH+rH,z,   x,wH+rH,z+d,  x+w,wH,z+d,
                x,wH+rH,z,   x+w,wH,z+d,   x+w,wH,z,
                x,wH,z,      x+w,wH,z,      x,wH+rH,z,
                x,wH,z+d,    x,wH+rH,z+d,   x+w,wH,z+d,
            ];
        }
    }

    document.getElementById('stats').textContent =
        SHAPES.length + ' shapes · wall h=' + WH + 'm';

    // Set camera focus to plan centre
    const b = bounds();
    const dist = Math.max(b.size * 1.2, 8);
    controls.target.set(b.cx, 0, b.cz);
    controls.update();
}

// ── View presets ─────────────────────────────────────────────────────
window.setView = function(v) {
    document.querySelectorAll('.vb').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    const b = bounds();
    const d = Math.max(b.size * 1.3, 10);
    switch(v) {
        case 'iso':   camera.position.set(b.cx+d, d*0.7,  b.cz+d); break;
        case 'top':   camera.position.set(b.cx,   d*1.5,  b.cz+0.01); break;
        case 'front': camera.position.set(b.cx,   d*0.4,  b.cz+d*1.2); break;
        case 'side':  camera.position.set(b.cx+d*1.2, d*0.4, b.cz); break;
    }
    controls.target.set(b.cx, 0, b.cz);
    controls.update();
};

// ── Wireframe toggle (live) ───────────────────────────────────────────
document.getElementById('cb-wire').addEventListener('change', rebuild);
document.getElementById('cb-floor').addEventListener('change', rebuild);
document.getElementById('cb-ceil').addEventListener('change', rebuild);
document.getElementById('cb-shad').addEventListener('change', rebuild);
document.getElementById('cb-gnd').addEventListener('change', () => {
    gndMesh.visible = document.getElementById('cb-gnd').checked;
});

document.getElementById('sl-wh').addEventListener('input', function(){
    document.getElementById('v-wh').textContent = this.value + 'm';
    rebuild();
});
document.getElementById('sl-wt').addEventListener('input', function(){
    document.getElementById('v-wt').textContent = this.value + 'm';
    rebuild();
});

document.getElementById('cp-sky').addEventListener('input', function(){
    const c = new THREE.Color(this.value);
    scene.background = c;
    scene.fog.color  = c;
    renderer.domElement.style.background = this.value;
});

// ── Screenshot ───────────────────────────────────────────────────────
window.screenshot = function() {
    renderer.render(scene, camera);
    const a = document.createElement('a');
    a.href = renderer.domElement.toDataURL('image/png');
    a.download = '<?= htmlspecialchars($plan->title) ?>-3d.png';
    a.click();
};

// ── Resize ────────────────────────────────────────────────────────────
window.addEventListener('resize', () => {
    camera.aspect = innerWidth/innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(innerWidth, innerHeight);
});

// ── Animation loop ────────────────────────────────────────────────────
(function animate() {
    requestAnimationFrame(animate);
    controls.update();
    renderer.render(scene, camera);
})();

// ── Init ─────────────────────────────────────────────────────────────
rebuild();
const b = bounds();
const dist = Math.max(b.size * 1.3, 10);
camera.position.set(b.cx + dist, dist * 0.75, b.cz + dist);
controls.target.set(b.cx, 0, b.cz);
controls.update();
document.getElementById('loading').style.display = 'none';

window.rebuild = rebuild; // expose for button
</script>
</body>
</html>
