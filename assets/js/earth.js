/**
 * ORBITAL ARCHIVE - Photorealistic Earth & Orbital Visualization
 * Three.js implementation for Section 01 Hero Earth
 * Features:
 * - NASA Visible Earth / Blue Marble day map texture with procedural canvas fallback
 * - Natural day/night terminator line via calibrated directional lighting
 * - Dual-mesh atmospheric rim shell with theme-adaptive glow shader
 * - Theme-adaptive rendering (NIGHT observatory vs ARCHIVE museum paper)
 * - Ultra-slow rotation (0.0006 rad/frame) and subtle mouse parallax tilt
 * - Complete prefers-reduced-motion accessibility
 * - Restrained archival orbital reference annotations
 */

class EarthVisualization {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    if (!this.container) return;

    this.scene = null;
    this.camera = null;
    this.renderer = null;
    this.earthGroup = null;
    this.earthMesh = null;
    this.cloudMesh = null;
    this.atmosphereMesh = null;
    this.atmosMaterial = null;
    this.ringsGroup = null;
    this.ringLines = [];
    this.markers = [];
    this.ambientLight = null;
    this.sunLight = null;
    this.rimLight = null;
    this.animationId = null;
    this.prefersReducedMotion = false;
    this.currentTheme = document.documentElement.getAttribute('data-theme') || 'night';

    // Mouse parallax variables
    this.targetRotX = 0;
    this.targetRotY = 0;

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
    this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'high-performance' });
    this.renderer.setSize(width, height);
    this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    this.renderer.domElement.style.pointerEvents = 'none'; // Never block page scroll or touch
    this.container.appendChild(this.renderer.domElement);

    // Master Group
    this.earthGroup = new THREE.Group();
    this.scene.add(this.earthGroup);

    // Create Earth sphere with photorealistic texture & procedural fallback
    this.createEarth();

    // Create Atmospheric Glow Shell
    this.createAtmosphere();

    // Create Restrained Archival Orbital Rings & Markers
    this.createOrbitalRings();

    // Calibrated Day/Night Lighting
    this.ambientLight = new THREE.AmbientLight(0x0a1626, 1.2);
    this.scene.add(this.ambientLight);

    this.sunLight = new THREE.DirectionalLight(0xffffff, 2.5);
    this.sunLight.position.set(16, 7, 12);
    this.scene.add(this.sunLight);

    this.rimLight = new THREE.DirectionalLight(0x38bdf8, 1.6);
    this.rimLight.position.set(-16, -6, -10);
    this.scene.add(this.rimLight);

    // Apply active theme colors
    this.setTheme(this.currentTheme);

    // Event listeners
    window.addEventListener('resize', () => this.onResize());
    window.addEventListener('mousemove', (e) => this.onMouseMove(e));

    // Start animation loop
    this.animate();
  }

  createProceduralCanvasTexture() {
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

    // Landmass silhouettes
    ctx.fillStyle = '#112233';
    ctx.beginPath();
    ctx.ellipse(1150, 420, 280, 220, 0, 0, Math.PI * 2);
    ctx.ellipse(1100, 600, 180, 240, 0, 0, Math.PI * 2);
    ctx.ellipse(450, 360, 220, 180, 0, 0, Math.PI * 2);
    ctx.ellipse(560, 650, 160, 220, 0, 0, Math.PI * 2);
    ctx.ellipse(1600, 720, 120, 90, 0, 0, Math.PI * 2);
    ctx.fill();

    // Night city lights
    ctx.fillStyle = 'rgba(255, 215, 120, 0.7)';
    for (let i = 0; i < 400; i++) {
      const x = Math.random() * 2048;
      const y = Math.random() * 800 + 100;
      const r = Math.random() * 1.5 + 0.5;
      ctx.beginPath();
      ctx.arc(x, y, r, 0, Math.PI * 2);
      ctx.fill();
    }

    return new THREE.CanvasTexture(canvas);
  }

  createEarth() {
    // 1. Instant high-res procedural base canvas so sphere is never blank
    const fallbackTexture = this.createProceduralCanvasTexture();

    // 2. Base Earth Sphere
    const geometry = new THREE.SphereGeometry(7, 64, 64);
    const material = new THREE.MeshStandardMaterial({
      map: fallbackTexture,
      roughness: 0.65,
      metalness: 0.08,
    });

    this.earthMesh = new THREE.Mesh(geometry, material);
    this.earthGroup.add(this.earthMesh);

    // 3. Asynchronously load NASA Blue Marble day map texture
    const textureLoader = new THREE.TextureLoader();
    textureLoader.load(
      'assets/images/earth_daymap.jpg',
      (texture) => {
        if (this.earthMesh && this.earthMesh.material) {
          texture.generateMipmaps = true;
          texture.minFilter = THREE.LinearMipmapLinearFilter;
          this.earthMesh.material.map = texture;
          this.earthMesh.material.needsUpdate = true;
        }
      },
      undefined,
      (err) => {
        console.warn('NASA Visible Earth texture not accessible, retaining procedural canvas:', err);
      }
    );

    // 4. Subtle Cloud Layer
    const cloudGeo = new THREE.SphereGeometry(7.08, 48, 48);
    const cloudMat = new THREE.MeshStandardMaterial({
      color: 0xffffff,
      transparent: true,
      opacity: 0.16,
      blending: THREE.AdditiveBlending,
    });
    this.cloudMesh = new THREE.Mesh(cloudGeo, cloudMat);
    this.earthGroup.add(this.cloudMesh);
  }

  createAtmosphere() {
    const atmosGeo = new THREE.SphereGeometry(7.35, 48, 48);
    this.atmosMaterial = new THREE.ShaderMaterial({
      uniforms: {
        glowColor: { value: new THREE.Color(0x38bdf8) },
        glowIntensity: { value: 0.95 }
      },
      vertexShader: `
        varying vec3 vNormal;
        void main() {
          vNormal = normalize(normalMatrix * normal);
          gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
        }
      `,
      fragmentShader: `
        uniform vec3 glowColor;
        uniform float glowIntensity;
        varying vec3 vNormal;
        void main() {
          float intensity = pow(0.68 - dot(vNormal, vec3(0.0, 0.0, 1.0)), 2.4);
          gl_FragColor = vec4(glowColor, 1.0) * intensity * glowIntensity;
        }
      `,
      blending: THREE.AdditiveBlending,
      side: THREE.BackSide,
      transparent: true,
    });

    this.atmosphereMesh = new THREE.Mesh(atmosGeo, this.atmosMaterial);
    this.earthGroup.add(this.atmosphereMesh);
  }

  createOrbitalRings() {
    this.ringsGroup = new THREE.Group();
    this.earthGroup.add(this.ringsGroup);
    this.ringLines = [];
    this.markers = [];

    const ringConfigs = [
      { radius: 8.4, tiltX: 0.45, tiltY: 0.2, nightColor: 0x38bdf8, archiveColor: 0x315f9f, label: 'ISS [400 KM]' },
      { radius: 9.8, tiltX: -0.3, tiltY: 0.5, nightColor: 0xd99a5b, archiveColor: 0xa96732, label: 'HUBBLE [540 KM]' },
      { radius: 11.2, tiltX: 0.6, tiltY: -0.4, nightColor: 0x6ea8ff, archiveColor: 0x475569, label: 'TIANGONG [390 KM]' },
      { radius: 13.0, tiltX: 0.1, tiltY: 0.1, nightColor: 0x94a3b8, archiveColor: 0x64748b, label: 'GEOSTATIONARY [35,786 KM]' }
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
        color: cfg.nightColor,
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
      const markerMat = new THREE.MeshBasicMaterial({ color: cfg.nightColor });
      const marker = new THREE.Mesh(markerGeo, markerMat);

      this.markers.push({
        mesh: marker,
        radius: cfg.radius,
        tiltX: cfg.tiltX,
        tiltY: cfg.tiltY,
        angle: Math.random() * Math.PI * 2,
        speed: 0.005 + Math.random() * 0.003,
      });

      this.ringsGroup.add(marker);

      this.ringLines.push({
        line: ringLine,
        marker: marker,
        nightColor: cfg.nightColor,
        archiveColor: cfg.archiveColor
      });
    });
  }

  setTheme(theme) {
    this.currentTheme = theme;
    const isArchive = theme === 'archive';

    // Ambient Lighting
    if (this.ambientLight) {
      this.ambientLight.color.setHex(isArchive ? 0xf5efe6 : 0x0a1626);
      this.ambientLight.intensity = isArchive ? 1.8 : 1.2;
    }

    // Directional Sunlight
    if (this.sunLight) {
      this.sunLight.color.setHex(isArchive ? 0xfff8ee : 0xffffff);
      this.sunLight.intensity = isArchive ? 2.0 : 2.5;
    }

    // Rim Lighting
    if (this.rimLight) {
      this.rimLight.color.setHex(isArchive ? 0x315f9f : 0x38bdf8);
      this.rimLight.intensity = isArchive ? 0.75 : 1.6;
    }

    // Atmospheric Glow Uniforms
    if (this.atmosMaterial && this.atmosMaterial.uniforms) {
      if (this.atmosMaterial.uniforms.glowColor) {
        this.atmosMaterial.uniforms.glowColor.value.setHex(isArchive ? 0x315f9f : 0x38bdf8);
      }
      if (this.atmosMaterial.uniforms.glowIntensity) {
        this.atmosMaterial.uniforms.glowIntensity.value = isArchive ? 0.45 : 0.95;
      }
    }

    // Cloud Layer
    if (this.cloudMesh && this.cloudMesh.material) {
      this.cloudMesh.material.opacity = isArchive ? 0.12 : 0.16;
    }

    // Orbital Rings & Markers
    if (this.ringLines) {
      this.ringLines.forEach(item => {
        const color = isArchive ? item.archiveColor : item.nightColor;
        item.line.material.color.setHex(color);
        item.line.material.opacity = isArchive ? 0.65 : 0.45;
        if (item.marker && item.marker.material) {
          item.marker.material.color.setHex(color);
        }
      });
    }
  }

  onMouseMove(e) {
    if (this.prefersReducedMotion) return;
    const nx = (e.clientX / window.innerWidth) * 2 - 1;
    const ny = (e.clientY / window.innerHeight) * 2 - 1;
    this.targetRotY = nx * 0.12;
    this.targetRotX = ny * 0.08;
  }

  animate() {
    this.animationId = requestAnimationFrame(() => this.animate());

    if (this.prefersReducedMotion) {
      // Respect prefers-reduced-motion: render static scene without continuous rotation
      if (this.renderer && this.scene && this.camera) {
        this.renderer.render(this.scene, this.camera);
      }
      return;
    }

    // Earth natural ultra-slow rotation (0.0006 rad/frame)
    if (this.earthMesh) {
      this.earthMesh.rotation.y += 0.0006;
    }
    if (this.cloudMesh) {
      this.cloudMesh.rotation.y += 0.0008;
    }

    // Gentle mouse parallax tilt lerp
    if (this.earthGroup) {
      this.earthGroup.rotation.y += (this.targetRotY - this.earthGroup.rotation.y) * 0.035;
      this.earthGroup.rotation.x += (this.targetRotX - this.earthGroup.rotation.x) * 0.035;
    }

    // Move satellite markers along orbital reference planes
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
