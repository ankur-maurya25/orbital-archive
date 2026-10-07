/**
 * ORBITAL ARCHIVE - Earth & Orbital Visualization
 * Three.js implementation for Section 01 Hero Earth
 */

class EarthVisualization {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    if (!this.container) return;

    this.scene = null;
    this.camera = null;
    this.renderer = null;
    this.earthGroup = null;
    this.ringsGroup = null;
    this.animationId = null;
    this.markers = [];
    this.prefersReducedMotion = false;

    // Check motion preference
    if (window.matchMedia) {
      const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
      this.prefersReducedMotion = motionQuery.matches;
      motionQuery.addEventListener('change', (e) => {
        this.prefersReducedMotion = e.matches;
      });
    }

    // Verify WebGL availability and Three.js
    if (!this.isWebGLAvailable() || typeof THREE === 'undefined') {
      this.renderFallback();
      return;
    }

    try {
      this.init();
    } catch (err) {
      console.warn('WebGL initialization failed, switching to orbital fallback.', err);
      this.renderFallback();
    }
  }

  isWebGLAvailable() {
    try {
      const canvas = document.createElement('canvas');
      return !!(window.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
    } catch (e) {
      return false;
    }
  }

  renderFallback() {
    if (!this.container) return;
    // Graceful aerospace SVG fallback with exact visual composition
    this.container.innerHTML = `
      <div class="webgl-fallback-stage" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; position: relative; pointer-events: none;">
        <svg viewBox="0 0 800 800" style="width: 100%; height: 100%; max-width: 800px; max-height: 800px; filter: drop-shadow(0 0 40px rgba(56, 189, 248, 0.2));">
          <!-- Orbital rings -->
          <circle cx="400" cy="400" r="320" fill="none" stroke="rgba(110, 168, 255, 0.25)" stroke-width="1.2" stroke-dasharray="4 6"/>
          <ellipse cx="400" cy="400" rx="360" ry="140" transform="rotate(-25 400 400)" fill="none" stroke="rgba(56, 189, 248, 0.45)" stroke-width="1.5"/>
          <ellipse cx="400" cy="400" rx="280" ry="180" transform="rotate(35 400 400)" fill="none" stroke="rgba(217, 154, 91, 0.4)" stroke-width="1.2" stroke-dasharray="3 4"/>
          <!-- Atmospheric Outer Glow -->
          <circle cx="400" cy="400" r="210" fill="url(#atmosGlow)" opacity="0.6"/>
          <!-- Earth sphere disk -->
          <circle cx="400" cy="400" r="195" fill="url(#earthGradient)" stroke="rgba(56, 189, 248, 0.5)" stroke-width="2"/>
          <defs>
            <radialGradient id="atmosGlow" cx="50%" cy="50%" r="50%">
              <stop offset="70%" stop-color="#38bdf8" stop-opacity="0.4"/>
              <stop offset="100%" stop-color="#38bdf8" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="earthGradient" cx="35%" cy="30%" r="65%">
              <stop offset="0%" stop-color="#1e3a8a"/>
              <stop offset="45%" stop-color="#0f172a"/>
              <stop offset="85%" stop-color="#030712"/>
              <stop offset="100%" stop-color="#000000"/>
            </radialGradient>
          </defs>
        </svg>
      </div>
    `;
  }

  init() {
    const width = this.container.clientWidth || 900;
    const height = this.container.clientHeight || 900;

    // Scene
    this.scene = new THREE.Scene();

    // Camera
    this.camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
    this.camera.position.z = 24;

    // Renderer
    this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    this.renderer.setSize(width, height);
    this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    this.renderer.domElement.style.pointerEvents = 'none'; // Never block page scroll or touch
    this.container.appendChild(this.renderer.domElement);

    // Master Group
    this.earthGroup = new THREE.Group();
    this.scene.add(this.earthGroup);

    // Create Earth sphere with procedural high-res shader/canvas texture
    this.createEarth();

    // Create Atmosphere Glow
    this.createAtmosphere();

    // Create Orbital Rings & Telemetry Markers
    this.createOrbitalRings();

    // Ambient & Directional Lighting
    const ambientLight = new THREE.AmbientLight(0x0a1626, 1.2);
    this.scene.add(ambientLight);

    const sunLight = new THREE.DirectionalLight(0xffffff, 2.5);
    sunLight.position.set(15, 6, 12);
    this.scene.add(sunLight);

    const rimLight = new THREE.DirectionalLight(0x38bdf8, 1.8);
    rimLight.position.set(-15, -6, -10);
    this.scene.add(rimLight);

    // Event listeners
    window.addEventListener('resize', () => this.onResize());
    
    // Start animation loop
    this.animate();
  }

  createEarth() {
    // Generate photorealistic procedural Earth canvas with continents and night city lights
    const canvas = document.createElement('canvas');
    canvas.width = 2048;
    canvas.height = 1024;
    const ctx = canvas.getContext('2d');

    // Deep ocean base
    const oceanGrad = ctx.createLinearGradient(0, 0, 0, 1024);
    oceanGrad.addColorStop(0, '#040b17');
    oceanGrad.addColorStop(0.5, '#07152b');
    oceanGrad.addColorStop(1, '#040b17');
    ctx.fillStyle = oceanGrad;
    ctx.fillRect(0, 0, 2048, 1024);

    // Draw continent silhouettes and night lights
    ctx.fillStyle = '#112233';
    ctx.beginPath();
    // Simplified global landmass shapes
    // Eurasia / Africa
    ctx.ellipse(1150, 420, 280, 220, 0, 0, Math.PI * 2);
    ctx.ellipse(1100, 600, 180, 240, 0, 0, Math.PI * 2);
    // Americas
    ctx.ellipse(450, 360, 220, 180, 0, 0, Math.PI * 2);
    ctx.ellipse(560, 650, 160, 220, 0, 0, Math.PI * 2);
    // Australia
    ctx.ellipse(1600, 720, 120, 90, 0, 0, Math.PI * 2);
    ctx.fill();

    // City lights on dark side
    ctx.fillStyle = 'rgba(255, 215, 120, 0.7)';
    for (let i = 0; i < 400; i++) {
      const x = Math.random() * 2048;
      const y = Math.random() * 800 + 100;
      const r = Math.random() * 1.5 + 0.5;
      ctx.beginPath();
      ctx.arc(x, y, r, 0, Math.PI * 2);
      ctx.fill();
    }

    const earthTexture = new THREE.CanvasTexture(canvas);

    // Earth Sphere
    const geometry = new THREE.SphereGeometry(7, 64, 64);
    const material = new THREE.MeshStandardMaterial({
      map: earthTexture,
      roughness: 0.65,
      metalness: 0.1,
    });

    this.earthMesh = new THREE.Mesh(geometry, material);
    this.earthGroup.add(this.earthMesh);

    // Subtle Cloud Layer
    const cloudGeo = new THREE.SphereGeometry(7.08, 48, 48);
    const cloudMat = new THREE.MeshStandardMaterial({
      color: 0xffffff,
      transparent: true,
      opacity: 0.18,
      blending: THREE.AdditiveBlending,
    });
    this.cloudMesh = new THREE.Mesh(cloudGeo, cloudMat);
    this.earthGroup.add(this.cloudMesh);
  }

  createAtmosphere() {
    const atmosGeo = new THREE.SphereGeometry(7.4, 48, 48);
    const atmosMat = new THREE.ShaderMaterial({
      vertexShader: `
        varying vec3 vNormal;
        void main() {
          vNormal = normalize(normalMatrix * normal);
          gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
        }
      `,
      fragmentShader: `
        varying vec3 vNormal;
        void main() {
          float intensity = pow(0.65 - dot(vNormal, vec3(0.0, 0.0, 1.0)), 2.2);
          gl_FragColor = vec4(0.22, 0.74, 0.97, 1.0) * intensity * 0.9;
        }
      `,
      blending: THREE.AdditiveBlending,
      side: THREE.BackSide,
      transparent: true,
    });

    const atmosphere = new THREE.Mesh(atmosGeo, atmosMat);
    this.earthGroup.add(atmosphere);
  }

  createOrbitalRings() {
    this.ringsGroup = new THREE.Group();
    this.earthGroup.add(this.ringsGroup);

    const ringConfigs = [
      { radius: 8.4, tiltX: 0.45, tiltY: 0.2, color: 0x38bdf8, label: 'ISS [400 KM]' },
      { radius: 9.8, tiltX: -0.3, tiltY: 0.5, color: 0xd99a5b, label: 'HUBBLE [540 KM]' },
      { radius: 11.2, tiltX: 0.6, tiltY: -0.4, color: 0x6ea8ff, label: 'TIANGONG [390 KM]' },
      { radius: 13.0, tiltX: 0.1, tiltY: 0.1, color: 0x94a3b8, label: 'GEOSTATIONARY [35,786 KM]' }
    ];

    ringConfigs.forEach(cfg => {
      // Orbit Path Ring
      const ringGeo = new THREE.BufferGeometry();
      const points = [];
      const segments = 128;
      for (let i = 0; i <= segments; i++) {
        const theta = (i / segments) * Math.PI * 2;
        points.push(new THREE.Vector3(Math.cos(theta) * cfg.radius, 0, Math.sin(theta) * cfg.radius));
      }
      ringGeo.setFromPoints(points);

      const ringMat = new THREE.LineBasicMaterial({
        color: cfg.color,
        transparent: true,
        opacity: 0.45,
        blending: THREE.AdditiveBlending,
      });

      const ringLine = new THREE.Line(ringGeo, ringMat);
      ringLine.rotation.x = cfg.tiltX;
      ringLine.rotation.y = cfg.tiltY;
      this.ringsGroup.add(ringLine);

      // Orbiting Satellite Marker Dot
      const markerGeo = new THREE.SphereGeometry(0.18, 12, 12);
      const markerMat = new THREE.MeshBasicMaterial({ color: cfg.color });
      const marker = new THREE.Mesh(markerGeo, markerMat);

      this.markers.push({
        mesh: marker,
        radius: cfg.radius,
        tiltX: cfg.tiltX,
        tiltY: cfg.tiltY,
        angle: Math.random() * Math.PI * 2,
        speed: 0.006 + Math.random() * 0.004,
      });

      this.ringsGroup.add(marker);
    });
  }

  animate() {
    this.animationId = requestAnimationFrame(() => this.animate());

    if (this.prefersReducedMotion) {
      // Respect prefers-reduced-motion: render static scene without continuous orbital spin
      if (this.renderer && this.scene && this.camera) {
        this.renderer.render(this.scene, this.camera);
      }
      return;
    }

    // Earth natural rotation
    if (this.earthMesh) {
      this.earthMesh.rotation.y += 0.0012;
    }
    if (this.cloudMesh) {
      this.cloudMesh.rotation.y += 0.0016;
    }

    // Move satellite markers along their orbits
    this.markers.forEach(m => {
      m.angle += m.speed;
      const x = Math.cos(m.angle) * m.radius;
      const z = Math.sin(m.angle) * m.radius;
      
      const pos = new THREE.Vector3(x, 0, z);
      pos.applyAxisAngle(new THREE.Vector3(1, 0, 0), m.tiltX);
      pos.applyAxisAngle(new THREE.Vector3(0, 1, 0), m.tiltY);
      
      m.mesh.position.copy(pos);
    });

    this.renderer.render(this.scene, this.camera);
  }

  onResize() {
    if (!this.container || !this.renderer || !this.camera) return;
    const width = this.container.clientWidth;
    const height = this.container.clientHeight;
    this.camera.aspect = width / height;
    this.camera.updateProjectionMatrix();
    this.renderer.setSize(width, height);
  }
}

// Global initialization
window.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('earth-canvas-container')) {
    window.earthVisual = new EarthVisualization('earth-canvas-container');
  }
});
