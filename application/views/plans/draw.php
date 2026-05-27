<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $plan ? htmlspecialchars($plan->title) : 'New Plan' ?> — Demo QT Plans</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#f0f0f0;font-family:system-ui,sans-serif;overflow:hidden;height:100vh;display:flex;flex-direction:column}

/* Top bar */
#topbar{height:48px;background:#1a1a2e;display:flex;align-items:center;gap:8px;padding:0 12px;flex-shrink:0;z-index:10}
#topbar a.back{color:rgba(255,255,255,.6);text-decoration:none;font-size:.82rem;margin-right:6px}
#topbar a.back:hover{color:#fff}
#plan-title{background:rgba(255,255,255,.1);border:none;border-radius:6px;color:#fff;padding:5px 10px;font-size:.9rem;width:220px;outline:none}
#plan-title::placeholder{color:rgba(255,255,255,.4)}
#plan-title:focus{background:rgba(255,255,255,.18)}
.tb-sep{width:1px;height:24px;background:rgba(255,255,255,.15);margin:0 4px}
.tb-btn{background:rgba(255,255,255,.1);border:none;border-radius:6px;color:#fff;padding:5px 11px;font-size:.8rem;cursor:pointer;display:flex;align-items:center;gap:5px;white-space:nowrap}
.tb-btn:hover{background:rgba(255,255,255,.2)}
.tb-btn.success{background:#16a34a}
#save-status{color:rgba(255,255,255,.5);font-size:.75rem;min-width:60px}
.tb-check{display:flex;align-items:center;gap:4px;color:rgba(255,255,255,.75);font-size:.78rem;cursor:pointer}
.tb-check input{accent-color:#e94560}
#zoom-label{color:rgba(255,255,255,.7);font-size:.78rem;min-width:38px;text-align:center}
#scale-info{color:rgba(255,255,255,.4);font-size:.72rem}

/* Main area */
#main{flex:1;display:flex;overflow:hidden}

/* Tool panel */
#toolpanel{width:64px;background:#12121f;display:flex;flex-direction:column;align-items:center;padding:8px 0;gap:2px;flex-shrink:0;overflow-y:auto}
.tool-btn{width:48px;height:48px;background:none;border:none;border-radius:8px;color:rgba(255,255,255,.55);cursor:pointer;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;font-size:.6rem;padding:0;transition:all .12s}
.tool-btn i{font-size:1.2rem}
.tool-btn:hover{background:rgba(255,255,255,.08);color:#fff}
.tool-btn.active{background:rgba(233,69,96,.2);color:#e94560;border:1px solid rgba(233,69,96,.4)}
.tool-sep{width:36px;height:1px;background:rgba(255,255,255,.1);margin:4px 0}

/* Properties panel */
#props{width:200px;background:#1e1e30;padding:12px;flex-shrink:0;overflow-y:auto;display:flex;flex-direction:column;gap:12px}
.prop-section{font-size:.68rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.3);margin-bottom:4px}
.prop-row{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px}
.prop-label{color:rgba(255,255,255,.6);font-size:.78rem;white-space:nowrap}
.prop-input{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:4px;color:#fff;padding:3px 6px;font-size:.78rem;width:100%;outline:none}
.prop-input:focus{border-color:#e94560}
.prop-color{width:32px;height:28px;border:none;border-radius:4px;cursor:pointer;padding:2px}
.prop-text{color:rgba(255,255,255,.5);font-size:.72rem;line-height:1.5}
.prop-hint{color:rgba(255,255,255,.25);font-size:.68rem;line-height:1.5}

/* Canvas area */
#canvas-wrap{flex:1;overflow:hidden;position:relative;cursor:crosshair}
canvas{display:block}
#coords{position:absolute;bottom:8px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,.5);color:#fff;font-size:.72rem;padding:2px 10px;border-radius:10px;pointer-events:none}
#scale-bar{position:absolute;bottom:8px;right:12px;background:rgba(0,0,0,.5);color:#fff;font-size:.7rem;padding:3px 8px;border-radius:6px;pointer-events:none}
</style>
</head>
<body>

<!-- Top bar -->
<div id="topbar">
    <a href="<?= base_url('plans') ?>" class="back"><i class="bi bi-arrow-left me-1"></i>Plans</a>
    <div class="tb-sep"></div>
    <input type="text" id="plan-title" value="<?= htmlspecialchars($plan->title ?? 'Untitled Plan') ?>">
    <button class="tb-btn" onclick="savePlan()"><i class="bi bi-floppy"></i> Save</button>
    <span id="save-status"></span>
    <div class="tb-sep"></div>
    <button class="tb-btn" onclick="open3D()" id="btn-3d" title="Save then open 3D view"><i class="bi bi-cube"></i> 3D View</button>
    <button class="tb-btn" onclick="exportPNG()"><i class="bi bi-download"></i> PNG</button>
    <button class="tb-btn" onclick="clearAll()"><i class="bi bi-trash"></i> Clear</button>
    <div class="tb-sep"></div>
    <label class="tb-check"><input type="checkbox" id="cb-grid" checked> Grid</label>
    <label class="tb-check"><input type="checkbox" id="cb-snap" checked> Snap</label>
    <div class="tb-sep"></div>
    <button class="tb-btn" onclick="zoom(-0.2)" style="width:28px;padding:5px">-</button>
    <span id="zoom-label">100%</span>
    <button class="tb-btn" onclick="zoom(0.2)" style="width:28px;padding:5px">+</button>
    <button class="tb-btn" onclick="resetView()"><i class="bi bi-arrows-fullscreen"></i></button>
    <div class="tb-sep" style="margin-left:auto"></div>
    <span id="scale-info">1 grid = 0.5 m</span>
</div>

<!-- Main drawing area -->
<div id="main">

    <!-- Tool buttons -->
    <div id="toolpanel">
        <button class="tool-btn" data-tool="select" title="Select / Move (V)"><i class="bi bi-cursor"></i><span>Select</span></button>
        <div class="tool-sep"></div>
        <button class="tool-btn active" data-tool="wall" title="Wall (W)"><i class="bi bi-slash-lg"></i><span>Wall</span></button>
        <button class="tool-btn" data-tool="room" title="Room (R)"><i class="bi bi-square"></i><span>Room</span></button>
        <button class="tool-btn" data-tool="door" title="Door (D)"><i class="bi bi-door-open"></i><span>Door</span></button>
        <button class="tool-btn" data-tool="window" title="Window (N)"><i class="bi bi-columns-gap"></i><span>Window</span></button>
        <button class="tool-btn" data-tool="stair" title="Staircase (S)"><i class="bi bi-ladder"></i><span>Stair</span></button>
        <button class="tool-btn" data-tool="roof"  title="Roof (F)"><i class="bi bi-house-fill"></i><span>Roof</span></button>
        <div class="tool-sep"></div>
        <button class="tool-btn" data-tool="text" title="Text Label (T)"><i class="bi bi-fonts"></i><span>Text</span></button>
        <div class="tool-sep"></div>
        <button class="tool-btn" data-tool="eraser" title="Eraser (E)"><i class="bi bi-eraser"></i><span>Erase</span></button>
    </div>

    <!-- Properties -->
    <div id="props">
        <div>
            <div class="prop-section">Stroke</div>
            <div class="prop-row">
                <span class="prop-label">Color</span>
                <input type="color" id="p-stroke" class="prop-color" value="#1a1a2e">
            </div>
            <div class="prop-row">
                <span class="prop-label">Width</span>
                <input type="range" id="p-lw" min="1" max="12" value="3" class="prop-input" style="padding:0">
                <span id="p-lw-val" style="color:rgba(255,255,255,.5);font-size:.75rem;min-width:16px">3</span>
            </div>
        </div>
        <div id="fill-props">
            <div class="prop-section">Fill</div>
            <div class="prop-row">
                <span class="prop-label">Color</span>
                <input type="color" id="p-fill" class="prop-color" value="#dbeafe">
            </div>
            <div class="prop-row">
                <span class="prop-label">Opacity</span>
                <input type="range" id="p-opacity" min="0" max="100" value="25" class="prop-input" style="padding:0">
                <span id="p-opacity-val" style="color:rgba(255,255,255,.5);font-size:.75rem;min-width:22px">25%</span>
            </div>
        </div>
        <div id="text-props" style="display:none">
            <div class="prop-section">Text</div>
            <div class="prop-row">
                <span class="prop-label">Size</span>
                <input type="number" id="p-fontsize" value="14" min="8" max="72" class="prop-input" style="width:60px">
            </div>
        </div>
        <div id="room-label-props" style="display:none">
            <div class="prop-section">Room</div>
            <input type="text" id="p-label" placeholder="Room label…" class="prop-input">
        </div>
        <div id="stair-props" style="display:none">
            <div class="prop-section">Stairs</div>
            <div class="prop-row">
                <span class="prop-label">Steps</span>
                <input type="number" id="p-steps" value="8" min="2" max="24" class="prop-input" style="width:60px">
            </div>
        </div>
        <div id="roof-props" style="display:none">
            <div class="prop-section">Roof</div>
            <div class="prop-row">
                <span class="prop-label">Type</span>
                <select id="p-rooftype" class="prop-input">
                    <option value="gable">Gable</option>
                    <option value="hip">Hip</option>
                    <option value="flat">Flat</option>
                    <option value="shed">Shed</option>
                </select>
            </div>
            <div class="prop-row">
                <span class="prop-label">Pitch</span>
                <input type="range" id="p-pitch" min="5" max="60" value="30" class="prop-input" style="padding:0">
                <span id="p-pitch-val" style="color:rgba(255,255,255,.5);font-size:.75rem;min-width:22px">30°</span>
            </div>
            <div class="prop-row">
                <span class="prop-label">Ridge</span>
                <select id="p-roofdir" class="prop-input">
                    <option value="ew">E–W</option>
                    <option value="ns">N–S</option>
                </select>
            </div>
            <div class="prop-row">
                <span class="prop-label">Overhang</span>
                <input type="range" id="p-overhang" min="0" max="60" value="10" class="prop-input" style="padding:0">
                <span id="p-overhang-val" style="color:rgba(255,255,255,.5);font-size:.75rem;min-width:22px">10</span>
            </div>
        </div>
        <div style="margin-top:auto">
            <div class="prop-section">Shortcuts</div>
            <div class="prop-hint">
                V select &nbsp; W wall &nbsp; R room<br>
                D door &nbsp; N window &nbsp; S stair<br>
                F roof &nbsp; T text &nbsp; E erase<br>
                Del delete &nbsp; Ctrl+Z undo<br>
                Ctrl+Y redo &nbsp; Shift+drag lock axis<br>
                Scroll wheel zoom &nbsp; Space+drag pan
            </div>
        </div>
    </div>

    <!-- Canvas -->
    <div id="canvas-wrap">
        <canvas id="c"></canvas>
        <div id="coords">0, 0 m</div>
        <div id="scale-bar">1 cell = 0.5 m</div>
    </div>

</div><!-- /#main -->

<script>
// ── Saved plan data ───────────────────────────────────────────────────
var PLAN_ID    = <?= $plan ? (int)$plan->id : 0 ?>;
var SAVE_URL   = '<?= $save_url ?>';
var CSRF_NAME  = '<?= $this->security->get_csrf_token_name() ?>';
var CSRF_HASH  = '<?= $this->security->get_csrf_hash() ?>';
var INIT_DATA  = <?= $plan && $plan->plan_data ? $plan->plan_data : '[]' ?>;
var GRID_SIZE  = <?= $plan ? (int)$plan->grid_size : 20 ?>;

// ── State ─────────────────────────────────────────────────────────────
var shapes  = JSON.parse(JSON.stringify(INIT_DATA));
var history = [JSON.parse(JSON.stringify(shapes))];
var hIdx    = 0;
var sel     = null;   // selected shape index
var tool    = 'wall';
var drawing = false;
var panning = false;
var spaceDown = false;
var scale   = 1;
var panX    = 40, panY = 40;
var mx0     = 0, my0 = 0;  // mouse down pos (canvas)
var panX0   = 0, panY0 = 0;
var dragOff = {x:0,y:0};   // offset when dragging selected shape
var gs      = GRID_SIZE;   // grid size (world units)

// ── Canvas setup ──────────────────────────────────────────────────────
var wrap = document.getElementById('canvas-wrap');
var cv   = document.getElementById('c');
var ctx  = cv.getContext('2d');

function resize() {
    cv.width  = wrap.clientWidth;
    cv.height = wrap.clientHeight;
    render();
}
window.addEventListener('resize', resize);
resize();

// ── Coordinate helpers ────────────────────────────────────────────────
function toW(cx,cy){ return {x:(cx-panX)/scale, y:(cy-panY)/scale}; }
function toC(wx,wy){ return {x:wx*scale+panX,   y:wy*scale+panY};   }
function snap(v)   { return Math.round(v/gs)*gs; }
function snapPt(wx,wy){
    var doSnap = document.getElementById('cb-snap').checked;
    return doSnap ? {x:snap(wx),y:snap(wy)} : {x:wx,y:wy};
}

// ── Rendering ─────────────────────────────────────────────────────────
function render(tempShape) {
    ctx.clearRect(0,0,cv.width,cv.height);
    ctx.save();

    // Grid
    if (document.getElementById('cb-grid').checked) drawGrid();

    // All committed shapes
    for (var i=0;i<shapes.length;i++) drawShape(shapes[i], i===sel);

    // Shape being drawn (preview)
    if (tempShape) drawShape(tempShape, false, true);

    ctx.restore();
}

function drawGrid() {
    var gPx  = gs * scale;
    var startX = ((panX % gPx) + gPx) % gPx;
    var startY = ((panY % gPx) + gPx) % gPx;
    ctx.strokeStyle = 'rgba(0,0,0,.07)';
    ctx.lineWidth   = 1;
    for (var x=startX; x<cv.width;  x+=gPx){ ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,cv.height); ctx.stroke(); }
    for (var y=startY; y<cv.height; y+=gPx){ ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(cv.width,y);  ctx.stroke(); }
    // Major lines every 5 cells
    ctx.strokeStyle = 'rgba(0,0,0,.13)';
    var mg = gPx*5;
    var smX = ((panX % mg) + mg) % mg;
    var smY = ((panY % mg) + mg) % mg;
    for (var x=smX; x<cv.width;  x+=mg){ ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,cv.height); ctx.stroke(); }
    for (var y=smY; y<cv.height; y+=mg){ ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(cv.width,y);  ctx.stroke(); }
}

function drawShape(s, isSel, isPreview) {
    ctx.save();
    ctx.globalAlpha = isPreview ? 0.65 : 1;
    var selColor = '#2563eb';

    switch(s.type) {
        case 'wall': {
            var p1=toC(s.x1,s.y1), p2=toC(s.x2,s.y2);
            ctx.beginPath(); ctx.moveTo(p1.x,p1.y); ctx.lineTo(p2.x,p2.y);
            ctx.strokeStyle = isSel ? selColor : (s.color||'#1a1a2e');
            ctx.lineWidth   = (s.lw||3)*scale;
            ctx.lineCap     = 'round';
            ctx.stroke();
            if (isSel) drawEndDots(p1,p2);
            break;
        }
        case 'room': {
            var c=toC(s.x,s.y), cw=s.w*scale, ch=s.h*scale;
            ctx.fillStyle   = hexToRgba(s.fill||'#dbeafe', s.opacity!==undefined?s.opacity:25);
            ctx.fillRect(c.x,c.y,cw,ch);
            ctx.strokeStyle = isSel ? selColor : (s.color||'#1a1a2e');
            ctx.lineWidth   = (s.lw||1.5)*scale;
            ctx.strokeRect(c.x,c.y,cw,ch);
            if (s.label) {
                ctx.fillStyle   = isSel ? selColor : '#374151';
                ctx.font        = (14*scale)+'px system-ui,sans-serif';
                ctx.textAlign   = 'center'; ctx.textBaseline='middle';
                ctx.fillText(s.label, c.x+cw/2, c.y+ch/2);
            }
            // Dimensions
            ctx.fillStyle='rgba(100,100,100,.7)';
            ctx.font=(11*scale)+'px system-ui,sans-serif';
            ctx.textAlign='center'; ctx.textBaseline='bottom';
            ctx.fillText(fmt(s.w)+'m', c.x+cw/2, c.y-3*scale);
            ctx.save(); ctx.translate(c.x-4*scale, c.y+ch/2);
            ctx.rotate(-Math.PI/2); ctx.textBaseline='bottom';
            ctx.fillText(fmt(s.h)+'m', 0, 0); ctx.restore();
            if (isSel) drawCornerDots(c.x,c.y,cw,ch);
            break;
        }
        case 'door': {
            var hinge=toC(s.hx,s.hy);
            var r = s.r*scale;
            ctx.strokeStyle = isSel ? selColor : (s.color||'#1a1a2e');
            ctx.lineWidth   = 1.5*scale;
            // Door leaf line
            ctx.beginPath(); ctx.moveTo(hinge.x,hinge.y);
            ctx.lineTo(hinge.x+r*Math.cos(s.ang), hinge.y+r*Math.sin(s.ang));
            ctx.stroke();
            // Swing arc
            ctx.beginPath();
            ctx.arc(hinge.x,hinge.y,r, s.ang, s.ang+s.dir*Math.PI/2);
            ctx.setLineDash([4*scale,4*scale]); ctx.stroke();
            ctx.setLineDash([]);
            // Hinge dot
            ctx.fillStyle = isSel ? selColor : '#1a1a2e';
            ctx.beginPath(); ctx.arc(hinge.x,hinge.y,3*scale,0,Math.PI*2); ctx.fill();
            break;
        }
        case 'window': {
            var p1=toC(s.x1,s.y1), p2=toC(s.x2,s.y2);
            var len=Math.hypot(p2.x-p1.x,p2.y-p1.y);
            if (len<1) break;
            var nx=(p1.y-p2.y)/len*(5*scale), ny=(p2.x-p1.x)/len*(5*scale);
            ctx.strokeStyle = isSel ? selColor : (s.color||'#0369a1');
            ctx.lineWidth   = 2.5*scale;
            ctx.beginPath(); ctx.moveTo(p1.x,p1.y); ctx.lineTo(p2.x,p2.y); ctx.stroke();
            ctx.lineWidth = 1.5*scale;
            [[0.25],[0.5],[0.75]].forEach(function(a){
                var t=a[0], mx=p1.x+t*(p2.x-p1.x), my=p1.y+t*(p2.y-p1.y);
                ctx.beginPath(); ctx.moveTo(mx-nx,my-ny); ctx.lineTo(mx+nx,my+ny); ctx.stroke();
            });
            if (isSel) drawEndDots(p1,p2);
            break;
        }
        case 'stair': {
            var c=toC(s.x,s.y), cw=s.w*scale, ch=s.h*scale;
            ctx.strokeStyle = isSel ? selColor : (s.color||'#374151');
            ctx.lineWidth   = 1.2*scale;
            ctx.strokeRect(c.x,c.y,cw,ch);
            var steps=s.steps||8;
            var isH = Math.abs(cw)>=Math.abs(ch); // horizontal steps
            if (isH) {
                var sw=cw/steps;
                for(var i=1;i<steps;i++){ ctx.beginPath(); ctx.moveTo(c.x+i*sw,c.y); ctx.lineTo(c.x+i*sw,c.y+ch); ctx.stroke(); }
            } else {
                var sh=ch/steps;
                for(var i=1;i<steps;i++){ ctx.beginPath(); ctx.moveTo(c.x,c.y+i*sh); ctx.lineTo(c.x+cw,c.y+i*sh); ctx.stroke(); }
            }
            // Arrow
            ctx.fillStyle='rgba(100,100,100,.4)'; ctx.font=(10*scale)+'px system-ui';
            ctx.textAlign='center'; ctx.textBaseline='middle';
            ctx.fillText('UP', c.x+cw/2, c.y+ch/2);
            if (isSel) drawCornerDots(c.x,c.y,cw,ch);
            break;
        }
        case 'roof': {
            var c=toC(s.x,s.y), cw=s.w*scale, ch=s.h*scale;
            if (Math.abs(cw)<2||Math.abs(ch)<2) break;
            var rc = s.color||'#c07840';
            ctx.save();
            // Tinted fill
            ctx.fillStyle = hexToRgba(rc, 18);
            ctx.fillRect(c.x,c.y,cw,ch);
            // Diagonal hatching (clipped)
            ctx.save();
            ctx.beginPath(); ctx.rect(c.x,c.y,cw,ch); ctx.clip();
            ctx.strokeStyle = hexToRgba(rc, 35);
            ctx.lineWidth = 1;
            var hSt = 16/scale;
            for (var di=-(Math.abs(cw)+Math.abs(ch)); di<(Math.abs(cw)+Math.abs(ch)); di+=hSt) {
                ctx.beginPath(); ctx.moveTo(c.x+di, c.y); ctx.lineTo(c.x+di+Math.abs(ch), c.y+ch); ctx.stroke();
            }
            ctx.restore();
            // Dashed border
            ctx.strokeStyle = isSel ? selColor : rc;
            ctx.lineWidth = 2/scale;
            ctx.setLineDash([8/scale,4/scale]);
            ctx.strokeRect(c.x,c.y,cw,ch);
            ctx.setLineDash([]);
            // Ridge / apex line
            var rt = s.roof_type||'gable';
            if (rt==='gable'||rt==='hip') {
                var isEW=(s.direction||'ew')==='ew';
                var hip = rt==='hip' ? Math.min(Math.abs(cw),Math.abs(ch))*0.22 : 0;
                ctx.strokeStyle = isSel ? selColor : rc;
                ctx.lineWidth   = 3/scale;
                ctx.beginPath();
                if (isEW){ ctx.moveTo(c.x+hip,c.y+ch/2); ctx.lineTo(c.x+cw-hip,c.y+ch/2); }
                else     { ctx.moveTo(c.x+cw/2,c.y+hip); ctx.lineTo(c.x+cw/2,c.y+ch-hip); }
                ctx.stroke();
                // Corner-to-ridge guide lines
                ctx.lineWidth = 1/scale;
                ctx.strokeStyle = hexToRgba(rc, 55);
                var corners = isEW
                    ? [[c.x,c.y],[c.x+cw,c.y],[c.x,c.y+ch],[c.x+cw,c.y+ch]]
                    : [[c.x,c.y],[c.x+cw,c.y],[c.x,c.y+ch],[c.x+cw,c.y+ch]];
                corners.forEach(function(co,i){
                    var tx = isEW ? (i<2 ? c.x+hip : c.x+cw-hip) : c.x+cw/2;
                    var ty = isEW ? c.y+ch/2 : (i<2 ? c.y+hip : c.y+ch-hip);
                    ctx.beginPath(); ctx.moveTo(co[0],co[1]); ctx.lineTo(tx,ty); ctx.stroke();
                });
            } else if (rt==='shed') {
                var isEW=(s.direction||'ew')==='ew';
                ctx.strokeStyle = isSel ? selColor : rc;
                ctx.lineWidth = 2.5/scale;
                ctx.beginPath();
                if (isEW){ ctx.moveTo(c.x,c.y); ctx.lineTo(c.x+cw,c.y); }
                else     { ctx.moveTo(c.x,c.y); ctx.lineTo(c.x,c.y+ch); }
                ctx.stroke();
            }
            // Label
            ctx.fillStyle = isSel ? selColor : rc;
            ctx.font = (11/scale)+'px system-ui,sans-serif';
            ctx.textAlign='center'; ctx.textBaseline='middle';
            ctx.fillText(
                (rt.charAt(0).toUpperCase()+rt.slice(1))+' '+(s.pitch||30)+'°',
                c.x+cw/2, c.y+ch/2+(Math.abs(ch)>40/scale ? 12/scale : 0)
            );
            if (isSel) drawCornerDots(c.x,c.y,cw,ch);
            ctx.restore();
            break;
        }
        case 'text': {
            var p=toC(s.x,s.y);
            ctx.fillStyle = isSel ? selColor : (s.color||'#1a1a2e');
            ctx.font = (s.size||14)*scale+'px system-ui,sans-serif';
            ctx.textAlign='left'; ctx.textBaseline='top';
            ctx.fillText(s.text, p.x, p.y);
            if (isSel) {
                var m=ctx.measureText(s.text), h=(s.size||14)*scale;
                ctx.strokeStyle=selColor; ctx.lineWidth=1;
                ctx.setLineDash([3,3]); ctx.strokeRect(p.x-3,p.y-3,m.width+6,h+6); ctx.setLineDash([]);
            }
            break;
        }
    }
    ctx.restore();
}

function drawEndDots(p1,p2) {
    ctx.fillStyle='#2563eb';
    ctx.beginPath(); ctx.arc(p1.x,p1.y,4,0,Math.PI*2); ctx.fill();
    ctx.beginPath(); ctx.arc(p2.x,p2.y,4,0,Math.PI*2); ctx.fill();
}
function drawCornerDots(x,y,w,h) {
    ctx.fillStyle='#2563eb';
    [[x,y],[x+w,y],[x,y+h],[x+w,y+h]].forEach(function(p){
        ctx.beginPath(); ctx.arc(p[0],p[1],4,0,Math.PI*2); ctx.fill();
    });
}
function hexToRgba(hex,pct) {
    var r=parseInt(hex.slice(1,3),16),g=parseInt(hex.slice(3,5),16),b=parseInt(hex.slice(5,7),16);
    return 'rgba('+r+','+g+','+b+','+(pct/100)+')';
}
function fmt(worldUnits) { return ((worldUnits/gs)*0.5).toFixed(1); }

// ── Hit testing ────────────────────────────────────────────────────────
function hitTest(s, wx, wy) {
    var tol = 8/scale;
    switch(s.type) {
        case 'wall': case 'window': return distSeg(wx,wy,s.x1,s.y1,s.x2,s.y2)<tol;
        case 'room': case 'stair': case 'roof': return wx>=s.x&&wx<=s.x+s.w&&wy>=s.y&&wy<=s.y+s.h;
        case 'door': return Math.abs(Math.hypot(wx-s.hx,wy-s.hy)-s.r)<tol*2;
        case 'text': return wx>=s.x&&wx<=s.x+80&&wy>=s.y&&wy<=s.y+20;
    }
    return false;
}
function distSeg(px,py,x1,y1,x2,y2){
    var dx=x2-x1,dy=y2-y1,l2=dx*dx+dy*dy;
    if(l2===0) return Math.hypot(px-x1,py-y1);
    var t=Math.max(0,Math.min(1,((px-x1)*dx+(py-y1)*dy)/l2));
    return Math.hypot(px-(x1+t*dx),py-(y1+t*dy));
}
function findShape(wx,wy) {
    for(var i=shapes.length-1;i>=0;i--) if(hitTest(shapes[i],wx,wy)) return i;
    return -1;
}

// ── Mouse events ──────────────────────────────────────────────────────
var temp = null; // shape being drawn

cv.addEventListener('mousedown', function(e) {
    var raw = {x:e.offsetX, y:e.offsetY};
    var w   = toW(raw.x, raw.y);
    var sp  = snapPt(w.x, w.y);
    mx0=raw.x; my0=raw.y;

    if (spaceDown || e.button===1) { panning=true; panX0=panX; panY0=panY; cv.style.cursor='grabbing'; return; }

    drawing = true;

    if (tool==='select') {
        var hit = findShape(w.x,w.y);
        sel = hit>=0 ? hit : null;
        if (sel!==null) {
            var s=shapes[sel];
            dragOff = s.type==='wall'||s.type==='window'
                ? {dx:sp.x-s.x1,dy:sp.y-s.y1,dx2:sp.x-s.x2,dy2:sp.y-s.y2}
                : {dx:sp.x-s.x,  dy:sp.y-s.y};
        }
        render();
        return;
    }
    if (tool==='eraser') {
        var hit=findShape(w.x,w.y);
        if (hit>=0){ shapes.splice(hit,1); if(sel===hit)sel=null; commit(); render(); }
        return;
    }
    if (tool==='text') {
        var txt=prompt('Enter label:',''); if(!txt)return;
        shapes.push({type:'text',x:sp.x,y:sp.y,text:txt,
            size:+document.getElementById('p-fontsize').value,
            color:document.getElementById('p-stroke').value});
        commit(); render(); drawing=false; return;
    }
    if (tool==='door') {
        temp={type:'door',hx:sp.x,hy:sp.y,r:gs*2,ang:0,dir:1,
            color:document.getElementById('p-stroke').value};
        return;
    }
    // All other tools: record start
    temp = makeStart(sp);
});

cv.addEventListener('mousemove', function(e) {
    var raw={x:e.offsetX,y:e.offsetY};
    var w  = toW(raw.x,raw.y);
    var sp = snapPt(w.x,w.y);

    // Update coords display
    document.getElementById('coords').textContent =
        fmt(sp.x)+'m, '+fmt(sp.y)+'m';

    if (panning) { panX=panX0+(raw.x-mx0); panY=panY0+(raw.y-my0); render(); return; }

    if (!drawing) return;

    if (tool==='select' && sel!==null) {
        moveShape(shapes[sel], sp); render(); return;
    }
    if (!temp) return;

    // Shift-lock axis for walls/windows
    if (e.shiftKey && (tool==='wall'||tool==='window')) {
        var dx=Math.abs(sp.x-temp.x1),dy=Math.abs(sp.y-temp.y1);
        if (dx>dy) sp.y=temp.y1; else sp.x=temp.x1;
    }

    updateTemp(sp, e);
    render(temp);
});

cv.addEventListener('mouseup', function(e) {
    if (panning){ panning=false; cv.style.cursor='crosshair'; return; }
    if (!drawing) return;
    drawing=false;

    if (tool==='select'){ commit(); return; }
    if (!temp) return;

    var w  = toW(e.offsetX,e.offsetY);
    var sp = snapPt(w.x,w.y);
    if (e.shiftKey&&(tool==='wall'||tool==='window')){
        var dx=Math.abs(sp.x-temp.x1),dy=Math.abs(sp.y-temp.y1);
        if(dx>dy) sp.y=temp.y1; else sp.x=temp.x1;
    }
    updateTemp(sp, e);
    finaliseShape();
    temp=null;
    render();
});

cv.addEventListener('wheel', function(e){ e.preventDefault(); zoom(e.deltaY<0?0.15:-0.15, e.offsetX, e.offsetY); },{passive:false});
cv.addEventListener('mouseleave', function(){ drawing=false; panning=false; temp=null; render(); });

// ── Shape helpers ─────────────────────────────────────────────────────
function getProps() {
    return {
        color:   document.getElementById('p-stroke').value,
        fill:    document.getElementById('p-fill').value,
        opacity: +document.getElementById('p-opacity').value,
        lw:      +document.getElementById('p-lw').value,
        label:   document.getElementById('p-label').value,
        steps:   +document.getElementById('p-steps').value,
        size:    +document.getElementById('p-fontsize').value,
    };
}

function makeStart(sp) {
    var p=getProps();
    switch(tool){
        case 'wall':   return {type:'wall',   x1:sp.x,y1:sp.y,x2:sp.x,y2:sp.y,color:p.color,lw:p.lw};
        case 'room':   return {type:'room',   x:sp.x,y:sp.y,w:0,h:0,color:p.color,fill:p.fill,opacity:p.opacity,lw:p.lw,label:p.label};
        case 'window': return {type:'window', x1:sp.x,y1:sp.y,x2:sp.x,y2:sp.y,color:p.color};
        case 'stair':  return {type:'stair',  x:sp.x,y:sp.y,w:0,h:0,color:p.color,steps:p.steps};
        case 'roof':   return {type:'roof', x:sp.x,y:sp.y,w:0,h:0,color:p.color,
            roof_type: document.getElementById('p-rooftype').value,
            pitch: +document.getElementById('p-pitch').value,
            direction: document.getElementById('p-roofdir').value,
            overhang: +document.getElementById('p-overhang').value};
    }
    return null;
}

function updateTemp(sp, e) {
    if (!temp) return;
    switch(temp.type){
        case 'wall':   temp.x2=sp.x; temp.y2=sp.y; break;
        case 'room':
        case 'stair':  temp.w=sp.x-temp.x; temp.h=sp.y-temp.y; break;
        case 'roof':
            temp.w=sp.x-temp.x; temp.h=sp.y-temp.y;
            temp.roof_type = document.getElementById('p-rooftype').value;
            temp.pitch      = +document.getElementById('p-pitch').value;
            temp.direction  = document.getElementById('p-roofdir').value;
            temp.overhang   = +document.getElementById('p-overhang').value;
            break;
        case 'window': temp.x2=sp.x; temp.y2=sp.y; break;
        case 'door':
            temp.ang=Math.atan2(sp.y-temp.hy, sp.x-temp.hx);
            temp.dir=e.shiftKey?-1:1;
            break;
    }
}

function finaliseShape() {
    if (!temp) return;
    // Discard tiny shapes
    if (temp.type==='wall'||temp.type==='window'){
        if(Math.hypot(temp.x2-temp.x1,temp.y2-temp.y1)<gs/2) return;
    }
    if (temp.type==='room'||temp.type==='stair'||temp.type==='roof'){
        if(Math.abs(temp.w)<gs/2||Math.abs(temp.h)<gs/2) return;
        // Normalise negative size
        if(temp.w<0){temp.x+=temp.w;temp.w=-temp.w;}
        if(temp.h<0){temp.y+=temp.h;temp.h=-temp.h;}
    }
    shapes.push(JSON.parse(JSON.stringify(temp)));
    commit();
}

function moveShape(s, sp) {
    switch(s.type){
        case 'wall': case 'window':
            s.x1=sp.x-dragOff.dx; s.y1=sp.y-dragOff.dy;
            s.x2=sp.x-dragOff.dx2; s.y2=sp.y-dragOff.dy2; break;
        case 'room': case 'stair': case 'roof':
            s.x=sp.x-dragOff.dx; s.y=sp.y-dragOff.dy; break;
        case 'door':
            s.hx=sp.x-dragOff.dx; s.hy=sp.y-dragOff.dy; break;
        case 'text':
            s.x=sp.x-dragOff.dx; s.y=sp.y-dragOff.dy; break;
    }
}

// ── History ───────────────────────────────────────────────────────────
function commit() {
    history = history.slice(0, hIdx+1);
    history.push(JSON.parse(JSON.stringify(shapes)));
    hIdx = history.length-1;
    markDirty();
}
function undo() { if(hIdx>0){hIdx--; shapes=JSON.parse(JSON.stringify(history[hIdx])); sel=null; render(); markDirty();} }
function redo() { if(hIdx<history.length-1){hIdx++; shapes=JSON.parse(JSON.stringify(history[hIdx])); sel=null; render(); markDirty();} }

// ── Zoom / pan ────────────────────────────────────────────────────────
function zoom(delta, cx, cy) {
    cx = cx!==undefined ? cx : cv.width/2;
    cy = cy!==undefined ? cy : cv.height/2;
    var prev=scale;
    scale = Math.max(0.2, Math.min(4, scale+delta));
    panX = cx - (cx-panX)*(scale/prev);
    panY = cy - (cy-panY)*(scale/prev);
    document.getElementById('zoom-label').textContent = Math.round(scale*100)+'%';
    render();
}
function resetView(){ scale=1; panX=40; panY=40; document.getElementById('zoom-label').textContent='100%'; render(); }

// ── Save ──────────────────────────────────────────────────────────────
var dirty = false;
function markDirty(){ dirty=true; document.getElementById('save-status').textContent='●  Unsaved'; document.getElementById('save-status').style.color='#fbbf24'; }
function markSaved(){ dirty=false; document.getElementById('save-status').textContent='✓ Saved'; document.getElementById('save-status').style.color='#4ade80'; setTimeout(function(){if(!dirty)document.getElementById('save-status').textContent='';},2500); }

function savePlan() {
    var title = document.getElementById('plan-title').value.trim() || 'Untitled Plan';
    var body  = CSRF_NAME+'='+encodeURIComponent(CSRF_HASH)
              +'&id='+PLAN_ID
              +'&title='+encodeURIComponent(title)
              +'&plan_data='+encodeURIComponent(JSON.stringify(shapes))
              +'&grid_size='+gs;
    fetch(SAVE_URL, {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body})
        .then(function(r){return r.json();})
        .then(function(d){
            if(d.success){
                if(!PLAN_ID){ PLAN_ID=d.id; history.replaceState({},'',' <?= base_url('plans/draw/') ?>'+d.id); }
                markSaved();
            }
        }).catch(function(){ document.getElementById('save-status').textContent='Save failed'; });
}

// Auto-save every 60 s
setInterval(function(){ if(dirty) savePlan(); }, 60000);

// ── Export PNG ────────────────────────────────────────────────────────
function exportPNG() {
    sel=null; render();
    var tmp=document.createElement('canvas');
    var margin=40;
    var bounds=getBounds();
    var bw=(bounds.x2-bounds.x1)*scale+margin*2;
    var bh=(bounds.y2-bounds.y1)*scale+margin*2;
    tmp.width=Math.max(bw,400); tmp.height=Math.max(bh,300);
    var tc=tmp.getContext('2d');
    tc.fillStyle='#fff'; tc.fillRect(0,0,tmp.width,tmp.height);
    var ox=panX-(bounds.x1*scale)+margin;
    var oy=panY-(bounds.y1*scale)+margin;
    // Draw grid
    var gPx=gs*scale;
    tc.strokeStyle='rgba(0,0,0,.07)'; tc.lineWidth=1;
    for(var x=(ox%gPx+gPx)%gPx;x<tmp.width;x+=gPx){tc.beginPath();tc.moveTo(x,0);tc.lineTo(x,tmp.height);tc.stroke();}
    for(var y=(oy%gPx+gPx)%gPx;y<tmp.height;y+=gPx){tc.beginPath();tc.moveTo(0,y);tc.lineTo(tmp.width,y);tc.stroke();}
    // Draw shapes onto temp canvas (swap context temporarily)
    var savedCtx=ctx, savedCV=cv, savedPanX=panX, savedPanY=panY;
    ctx=tc; cv=tmp; panX=ox; panY=oy;
    for(var i=0;i<shapes.length;i++) drawShape(shapes[i],false,false);
    ctx=savedCtx; cv=savedCV; panX=savedPanX; panY=savedPanY;
    var a=document.createElement('a');
    a.href=tmp.toDataURL('image/png');
    a.download=(document.getElementById('plan-title').value||'house-plan')+'.png';
    a.click();
}
function getBounds(){
    var x1=Infinity,y1=Infinity,x2=-Infinity,y2=-Infinity;
    shapes.forEach(function(s){
        var pts=[];
        if(s.type==='wall'||s.type==='window') pts=[[s.x1,s.y1],[s.x2,s.y2]];
        else if(s.type==='room'||s.type==='stair'||s.type==='roof') pts=[[s.x,s.y],[s.x+s.w,s.y+s.h]];
        else if(s.type==='door') pts=[[s.hx-s.r,s.hy-s.r],[s.hx+s.r,s.hy+s.r]];
        else if(s.type==='text') pts=[[s.x,s.y],[s.x+80,s.y+20]];
        pts.forEach(function(p){x1=Math.min(x1,p[0]);y1=Math.min(y1,p[1]);x2=Math.max(x2,p[0]);y2=Math.max(y2,p[1]);});
    });
    if(!isFinite(x1)){x1=0;y1=0;x2=cv.width/scale;y2=cv.height/scale;}
    return {x1:x1,y1:y1,x2:x2,y2:y2};
}

function clearAll(){ if(confirm('Clear all shapes?')){ shapes=[]; sel=null; commit(); render(); } }

// ── Keyboard shortcuts ────────────────────────────────────────────────
document.addEventListener('keydown', function(e) {
    if(e.target.tagName==='INPUT'||e.target.tagName==='TEXTAREA') return;
    if(e.code==='Space'){ spaceDown=true; cv.style.cursor='grab'; e.preventDefault(); return; }
    if(e.ctrlKey&&e.key==='z'){ undo(); return; }
    if(e.ctrlKey&&(e.key==='y'||e.key==='Y')){ redo(); return; }
    if(e.ctrlKey&&e.key==='s'){ e.preventDefault(); savePlan(); return; }
    var map={'v':'select','w':'wall','r':'room','d':'door','n':'window','s':'stair','f':'roof','t':'text','e':'eraser'};
    if(map[e.key]) { setTool(map[e.key]); return; }
    if((e.key==='Delete'||e.key==='Backspace')&&sel!==null){ shapes.splice(sel,1); sel=null; commit(); render(); }
});
document.addEventListener('keyup', function(e){ if(e.code==='Space'){ spaceDown=false; cv.style.cursor='crosshair'; } });

// ── Tool switching ────────────────────────────────────────────────────
function setTool(t) {
    tool=t; sel=null; drawing=false; temp=null;
    document.querySelectorAll('.tool-btn').forEach(function(b){ b.classList.toggle('active', b.dataset.tool===t); });
    // Show/hide property panels
    document.getElementById('fill-props').style.display     = (t==='room'||t==='stair') ? '' : 'none';
    document.getElementById('text-props').style.display     = t==='text'     ? '' : 'none';
    document.getElementById('room-label-props').style.display = t==='room'   ? '' : 'none';
    document.getElementById('stair-props').style.display    = t==='stair'    ? '' : 'none';
    document.getElementById('roof-props').style.display     = t==='roof'     ? '' : 'none';
    render();
}
document.querySelectorAll('.tool-btn').forEach(function(b){ b.addEventListener('click', function(){ setTool(b.dataset.tool); }); });
setTool('wall');

// ── Property listeners ────────────────────────────────────────────────
document.getElementById('p-lw').addEventListener('input', function(){ document.getElementById('p-lw-val').textContent=this.value; });
document.getElementById('p-opacity').addEventListener('input', function(){ document.getElementById('p-opacity-val').textContent=this.value+'%'; });
document.getElementById('p-pitch').addEventListener('input', function(){ document.getElementById('p-pitch-val').textContent=this.value+'°'; });
document.getElementById('p-overhang').addEventListener('input', function(){ document.getElementById('p-overhang-val').textContent=this.value; });
document.getElementById('cb-grid').addEventListener('change', render);
document.getElementById('cb-snap').addEventListener('change', render);

// ── Open 3D viewer ─────────────────────────────────────────────────────
function open3D() {
    if (!PLAN_ID) {
        // Must save first to get an ID
        var title = document.getElementById('plan-title').value.trim() || 'Untitled Plan';
        var body  = CSRF_NAME+'='+encodeURIComponent(CSRF_HASH)
                  +'&id=0&title='+encodeURIComponent(title)
                  +'&plan_data='+encodeURIComponent(JSON.stringify(shapes))
                  +'&grid_size='+gs;
        document.getElementById('btn-3d').textContent = '…';
        fetch(SAVE_URL, {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body})
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.success) {
                    PLAN_ID = d.id;
                    window.open('<?= base_url('plans/view3d/') ?>' + d.id, '_blank');
                }
                document.getElementById('btn-3d').innerHTML = '<i class="bi bi-cube"></i> 3D View';
            });
    } else {
        savePlan();
        window.open('<?= base_url('plans/view3d/') ?>' + PLAN_ID, '_blank');
    }
}
</script>
</body>
</html>
