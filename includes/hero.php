<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>3D Animated Hero Section</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/0.158.0/three.min.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      overflow-x: hidden;
      background-color: #0f172a;
      color: #f8fafc;
    }
    
    #hero-container {
      background-color: #0f172a !important;
      position: relative;
      height: 100vh;
      width: 100%;
      overflow: hidden;
    }
    
    #hero-canvas {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 1;
    }
    
    .hero-content {
      position: relative;
      z-index: 10;
      height: 100%;
      width: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 0 10%;
    }
    
    .hero-title {
      font-size: 4rem;
      font-weight: 800;
      margin-bottom: 1rem;
      opacity: 0;
      transform: translateY(30px);
      animation: fadeUp 0.8s ease forwards;
      background: linear-gradient(90deg, #60a5fa, #818cf8, #a78bfa);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    
    .hero-subtitle {
      font-size: 1.5rem;
      max-width: 600px;
      margin-bottom: 2rem;
      opacity: 0;
      transform: translateY(30px);
      animation: fadeUp 0.8s ease forwards 0.3s;
    }
    
    .hero-buttons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      opacity: 0;
      transform: translateY(30px);
      animation: fadeUp 0.8s ease forwards 0.6s;
    }
    
    .hero-btn {
      padding: 0.8rem 2rem;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }
    
    .hero-btn:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 0%;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
      z-index: -1;
    }
    
    .hero-btn:hover:before {
      width: 100%;
    }
    
    .primary-btn {
      background: linear-gradient(90deg, #3b82f6, #4f46e5);
      color: white;
      box-shadow: 0 4px 20px rgba(59, 130, 246, 0.5);
    }
    
    .primary-btn:hover {
      box-shadow: 0 6px 30px rgba(59, 130, 246, 0.7);
      transform: translateY(-2px);
    }
    
    .secondary-btn {
      border: 2px solid rgba(255, 255, 255, 0.3);
      color: white;
      backdrop-filter: blur(5px);
    }
    
    .secondary-btn:hover {
      border-color: white;
      background: rgba(255, 255, 255, 0.1);
      transform: translateY(-2px);
    }
    
    .floating-elements {
      position: absolute;
      top: 0;
      right: 0;
      width: 40%;
      height: 100%;
      z-index: 5;
      pointer-events: none;
    }
    
    .floating-card {
      position: absolute;
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.1);
      opacity: 0;
      animation: floatIn 1s ease forwards;
    }
    
    .card-1 {
      width: 220px;
      height: 150px;
      top: 30%;
      right: 5%;
      animation-delay: 0.9s;
      transform: rotate(5deg);
    }
    
    .card-2 {
      width: 180px;
      height: 180px;
      top: 50%;
      right: 25%;
      animation-delay: 1.2s;
      transform: rotate(-8deg);
    }
    
    .card-3 {
      width: 250px;
      height: 120px;
      top: 20%;
      right: 30%;
      animation-delay: 1.5s;
      transform: rotate(-3deg);
    }
    
    .card-icon {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #818cf8;
    }
    
    .card-title {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 5px;
    }
    
    .card-text {
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.7);
    }
    
    .scroll-indicator {
      position: absolute;
      bottom: 40px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      flex-direction: column;
      align-items: center;
      opacity: 0;
      animation: fadeIn 0.8s ease forwards 1.8s;
    }
    
    .scroll-text {
      margin-bottom: 10px;
      font-size: 0.9rem;
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    
    .scroll-icon {
      width: 24px;
      height: 40px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 12px;
      display: flex;
      justify-content: center;
    }
    
    .scroll-indicator-dot {
      width: 4px;
      height: 4px;
      background: white;
      border-radius: 50%;
      margin-top: 8px;
      animation: scrollAnim 1.5s ease-in-out infinite;
    }
    
    @keyframes fadeUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes fadeIn {
      to {
        opacity: 1;
      }
    }
    
    @keyframes floatIn {
      0% {
        opacity: 0;
        transform: translateY(50px) rotate(0);
      }
      100% {
        opacity: 1;
        transform: translateY(0) rotate(var(--rotation));
      }
    }
    
    @keyframes scrollAnim {
      0% {
        opacity: 1;
        transform: translateY(0);
      }
      30% {
        opacity: 1;
        transform: translateY(10px);
      }
      60% {
        opacity: 0;
        transform: translateY(10px);
      }
      100% {
        opacity: 0;
        transform: translateY(0);
      }
    }
    
    @media (max-width: 1024px) {
      .hero-title {
        font-size: 3rem;
      }
      
      .floating-elements {
        width: 100%;
        opacity: 0.4;
      }
    }
    
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2.5rem;
      }
      
      .hero-subtitle {
        font-size: 1.2rem;
      }
      
      .card-1 {
        right: 10%;
      }
      
      .card-2 {
        display: none;
      }
      
      .card-3 {
        right: 5%;
        top: 60%;
      }
    }
  </style>
</head>
<body>
  <div id="hero-container">
    <canvas id="hero-canvas"></canvas>
    
    <div class="hero-content">
      <h1 class="hero-title">Elevate Your Digital Experience</h1>
      <p class="hero-subtitle">Discover our revolutionary platform with immersive 3D technology that transforms the way you interact with content.</p>
      
      <div class="hero-buttons">
        <a href="#" class="hero-btn primary-btn">Get Started</a>
        <a href="#" class="hero-btn secondary-btn">Watch Demo</a>
      </div>
    </div>
    
    <div class="floating-elements">
      <div class="floating-card card-1" style="--rotation: 5deg;">
        <div class="card-icon">⚡</div>
        <div class="card-title">Lightning Fast</div>
        <div class="card-text">Experience performance like never before with our optimized platform.</div>
      </div>
      
      <div class="floating-card card-2" style="--rotation: -8deg;">
        <div class="card-icon">🛡️</div>
        <div class="card-title">Secure & Reliable</div>
        <div class="card-text">Your data is protected with enterprise-grade security measures.</div>
      </div>
      
      <div class="floating-card card-3" style="--rotation: -3deg;">
        <div class="card-icon">🚀</div>
        <div class="card-title">Next-Gen Features</div>
        <div class="card-text">Stay ahead with cutting-edge tools and capabilities.</div>
      </div>
    </div>
    
    <div class="scroll-indicator">
      <div class="scroll-text">Scroll Down</div>
      <div class="scroll-icon">
        <div class="scroll-indicator-dot"></div>
      </div>
    </div>
  </div>

  <script>
    // Initialize Three.js Scene
    const canvas = document.getElementById('hero-canvas');
    const renderer = new THREE.WebGLRenderer({
      canvas,
      antialias: true,
      alpha: true
    });
    
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    
    const scene = new THREE.Scene();
    
    // Camera Setup
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 30;
    scene.add(camera);
    
    // Lighting
    const ambientLight = new THREE.AmbientLight(0x404040, 2);
    scene.add(ambientLight);
    
    const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
    directionalLight.position.set(0, 10, 10);
    scene.add(directionalLight);
    
    // Create particles
    const particlesGeometry = new THREE.BufferGeometry();
    const particlesCount = 800;
    
    const posArray = new Float32Array(particlesCount * 3);
    const scaleArray = new Float32Array(particlesCount);
    
    for (let i = 0; i < particlesCount * 3; i += 3) {
      // Position
      posArray[i] = (Math.random() - 0.5) * 100;
      posArray[i + 1] = (Math.random() - 0.5) * 100;
      posArray[i + 2] = (Math.random() - 0.5) * 100;
      
      // Scale
      scaleArray[i / 3] = Math.random();
    }
    
    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
    particlesGeometry.setAttribute('aScale', new THREE.BufferAttribute(scaleArray, 1));
    
    // Material
    const particlesMaterial = new THREE.PointsMaterial({
      size: 0.3,
      sizeAttenuation: true,
      transparent: true,
      color: 0x4f46e5
    });
    
    // Mesh
    const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
    scene.add(particlesMesh);
    
    // Add geometric shapes
    const geometries = [
      new THREE.TorusGeometry(8, 2, 16, 100),
      new THREE.IcosahedronGeometry(7, 0),
      new THREE.OctahedronGeometry(7, 0)
    ];
    
    const materials = [
      new THREE.MeshPhongMaterial({
        color: 0x4f46e5,
        wireframe: true,
        transparent: true,
        opacity: 0.3
      }),
      new THREE.MeshPhongMaterial({
        color: 0x818cf8,
        wireframe: true,
        transparent: true,
        opacity: 0.3
      }),
      new THREE.MeshPhongMaterial({
        color: 0x3b82f6,
        wireframe: true,
        transparent: true,
        opacity: 0.3
      })
    ];
    
    const meshes = [];
    
    for (let i = 0; i < geometries.length; i++) {
      const mesh = new THREE.Mesh(geometries[i], materials[i]);
      mesh.position.set((Math.random() - 0.5) * 30, (Math.random() - 0.5) * 20, (Math.random() - 0.5) * 10);
      mesh.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
      meshes.push(mesh);
      scene.add(mesh);
    }
    
    // Mouse Movement
    let mouseX = 0;
    let mouseY = 0;
    
    document.addEventListener('mousemove', (event) => {
      mouseX = (event.clientX / window.innerWidth) * 2 - 1;
      mouseY = -(event.clientY / window.innerHeight) * 2 + 1;
    });
    
    // Responsive
    window.addEventListener('resize', () => {
      const width = window.innerWidth;
      const height = window.innerHeight;
      
      camera.aspect = width / height;
      camera.updateProjectionMatrix();
      
      renderer.setSize(width, height);
      renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    });
    
    // Animation
    const animate = () => {
      requestAnimationFrame(animate);
      
      // Rotate particles and adjust with mouse
      particlesMesh.rotation.x += 0.0005;
      particlesMesh.rotation.y += 0.0003;
      
      // Update shapes based on mouse
      meshes.forEach((mesh, i) => {
        mesh.rotation.x += 0.002 * (i + 1) * 0.2;
        mesh.rotation.y += 0.003 * (i + 1) * 0.1;
        mesh.position.x += mouseX * 0.02;
        mesh.position.y += mouseY * 0.02;
        
        // Reset position if too far
        if (Math.abs(mesh.position.x) > 40) {
          mesh.position.x = Math.sign(mesh.position.x) * 40;
        }
        
        if (Math.abs(mesh.position.y) > 40) {
          mesh.position.y = Math.sign(mesh.position.y) * 40;
        }
      });
      
      // Camera follows mouse slightly
      camera.position.x += (mouseX * 5 - camera.position.x) * 0.05;
      camera.position.y += (mouseY * 5 - camera.position.y) * 0.05;
      
      camera.lookAt(scene.position);
      
      renderer.render(scene, camera);
    };
    
    animate();
    
    // Mobile device detection and optimization
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      // Reduce particle count for mobile
      scene.remove(particlesMesh);
      
      const mobileParticlesGeometry = new THREE.BufferGeometry();
      const mobileParticlesCount = 300;
      
      const mobilePosArray = new Float32Array(mobileParticlesCount * 3);
      
      for (let i = 0; i < mobileParticlesCount * 3; i += 3) {
        mobilePosArray[i] = (Math.random() - 0.5) * 100;
        mobilePosArray[i + 1] = (Math.random() - 0.5) * 100;
        mobilePosArray[i + 2] = (Math.random() - 0.5) * 100;
      }
      
      mobileParticlesGeometry.setAttribute('position', new THREE.BufferAttribute(mobilePosArray, 3));
      
      const mobileParticlesMesh = new THREE.Points(mobileParticlesGeometry, particlesMaterial);
      scene.add(mobileParticlesMesh);
      
      // Simpler animation for mobile
      const mobileAnimate = () => {
        requestAnimationFrame(mobileAnimate);
        
        mobileParticlesMesh.rotation.x += 0.0003;
        mobileParticlesMesh.rotation.y += 0.0002;
        
        meshes.forEach((mesh, i) => {
          mesh.rotation.x += 0.001 * (i + 1) * 0.2;
          mesh.rotation.y += 0.002 * (i + 1) * 0.1;
        });
        
        renderer.render(scene, camera);
      };
      
      mobileAnimate();
    }
  </script>
</body>
</html>