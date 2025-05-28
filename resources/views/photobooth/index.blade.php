<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Modern Photobooth</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #8E44FF 0%, #6A82FB 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .photobooth-container {
            background: #F9F9FF;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(26, 26, 26, 0.15);
            overflow: hidden;
            max-width: 1200px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 400px;
            min-height: 700px;
            border: 2px solid rgba(142, 68, 255, 0.1);
        }

        .camera-section {
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #F9F9FF;
        }

        .camera-container {
            position: relative;
            width: 100%;
            max-width: 600px;
            aspect-ratio: 4/3;
            background: #1A1A1A;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            border: 3px solid #8E44FF;
        }

        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #canvas {
            display: none;
        }

        .countdown-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(26, 26, 26, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            color: #8E44FF;
            font-size: 120px;
            font-weight: bold;
            z-index: 10;
            text-shadow: 0 0 20px rgba(142, 68, 255, 0.5);
        }

        .controls-section {
            padding: 30px;
            background: white;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .section-title {
            font-size: 24px;
            font-weight: bold;
            color: #1A1A1A;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #8E44FF;
        }

        .control-group {
            background: #F9F9FF;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid rgba(142, 68, 255, 0.1);
        }

        .control-label {
            font-weight: 600;
            color: #1A1A1A;
            margin-bottom: 10px;
            display: block;
        }

        .control-label i {
            color: #8E44FF;
            margin-right: 8px;
        }

        .photo-count-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .count-option {
            padding: 15px;
            border: 2px solid rgba(106, 130, 251, 0.3);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            font-weight: 600;
            color: #1A1A1A;
        }

        .count-option:hover {
            border-color: #6A82FB;
            background: rgba(106, 130, 251, 0.1);
            transform: translateY(-2px);
        }

        .count-option.active {
            border-color: #8E44FF;
            background: #8E44FF;
            color: white;
            box-shadow: 0 5px 15px rgba(142, 68, 255, 0.3);
        }

        .timer-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .timer-input {
            flex: 1;
            padding: 12px;
            border: 2px solid rgba(106, 130, 251, 0.3);
            border-radius: 8px;
            font-size: 16px;
            background: white;
            color: #1A1A1A;
        }

        .timer-input:focus {
            outline: none;
            border-color: #8E44FF;
            box-shadow: 0 0 0 3px rgba(142, 68, 255, 0.1);
        }

        .capture-btn {
            background: linear-gradient(135deg, #B657FF 0%, #8E44FF 100%);
            color: white;
            border: none;
            padding: 20px 40px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(182, 87, 255, 0.3);
        }

        .capture-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(182, 87, 255, 0.4);
            background: linear-gradient(135deg, #8E44FF 0%, #B657FF 100%);
        }

        .capture-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 8px 25px rgba(182, 87, 255, 0.2);
        }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 20px;
        }

        .photo-preview {
            aspect-ratio: 3/4;
            background: #F9F9FF;
            border: 2px dashed rgba(106, 130, 251, 0.4);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6A82FB;
            position: relative;
            overflow: hidden;
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-preview .photo-number {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(26, 26, 26, 0.8);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(106, 130, 251, 0.2);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 10px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #8E44FF, #B657FF);
            width: 0%;
            transition: width 0.3s ease;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }

        .btn-secondary {
            background: #6A82FB;
            color: white;
            border: 2px solid #6A82FB;
        }

        .btn-secondary:hover {
            background: #8E44FF;
            border-color: #8E44FF;
        }

        .btn-success {
            background: #B657FF;
            color: white;
            border: 2px solid #B657FF;
        }

        .btn-success:hover {
            background: #8E44FF;
            border-color: #8E44FF;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(142, 68, 255, 0.3);
        }

        .status-message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-weight: 600;
            text-align: center;
        }

        .status-success {
            background: rgba(182, 87, 255, 0.1);
            color: #8E44FF;
            border: 2px solid rgba(182, 87, 255, 0.3);
        }

        .status-error {
            background: rgba(255, 87, 87, 0.1);
            color: #ff5757;
            border: 2px solid rgba(255, 87, 87, 0.3);
        }

        .camera-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
        }

        .camera-switch-btn {
            background: rgba(26, 26, 26, 0.6);
            border: 2px solid rgba(142, 68, 255, 0.5);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #8E44FF;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .camera-switch-btn:hover {
            background: rgba(26, 26, 26, 0.8);
            border-color: #B657FF;
            color: #B657FF;
            box-shadow: 0 0 15px rgba(142, 68, 255, 0.3);
        }

        @media (max-width: 768px) {
            .photobooth-container {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
            
            .photo-count-selector {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="photobooth-container">
        <div class="camera-section">
            <h1 class="section-title">
                <i class="fas fa-camera"></i>
                Modern Photobooth
            </h1>
            
            <div class="camera-container">
                <video id="video" autoplay muted playsinline></video>
                <canvas id="canvas"></canvas>
                <div id="countdown" class="countdown-overlay">3</div>
                <div class="camera-controls">
                    <button id="switchCameraBtn" class="camera-switch-btn">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <div class="photos-grid" id="photosGrid">
                <!-- Photos will be added here dynamically -->
            </div>

            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>

            <div id="statusMessage"></div>
        </div>

        <div class="controls-section">
            <div class="control-group">
                <label class="control-label">
                    <i class="fas fa-images"></i>
                    Number of Photos
                </label>
                <div class="photo-count-selector">
                    <div class="count-option active" data-count="1">1 Photo</div>
                    <div class="count-option" data-count="2">2 Photos</div>
                    <div class="count-option" data-count="3">3 Photos</div>
                    <div class="count-option" data-count="4">4 Photos</div>
                    <div class="count-option" data-count="5">5 Photos</div>
                    <div class="count-option" data-count="6">6 Photos</div>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="timerDuration">
                    <i class="fas fa-clock"></i>
                    Timer Duration (seconds)
                </label>
                <div class="timer-controls">
                    <input type="number" id="timerDuration" class="timer-input" value="3" min="1" max="10">
                    <span>seconds</span>
                </div>
            </div>

            <button id="captureBtn" class="capture-btn">
                <i class="fas fa-camera"></i>
                Start Photo Session
            </button>

            <div class="action-buttons" id="actionButtons" style="display: none;">
                <button id="resetBtn" class="btn btn-secondary">
                    <i class="fas fa-redo"></i>
                    Reset
                </button>
                <button id="createCollageBtn" class="btn btn-success">
                    <i class="fas fa-download"></i>
                    Create & Download
                </button>
            </div>
        </div>
    </div>

    <script>
        class Photobooth {
            constructor() {
                this.video = document.getElementById('video');
                this.canvas = document.getElementById('canvas');
                this.ctx = this.canvas.getContext('2d');
                this.captureBtn = document.getElementById('captureBtn');
                this.countdown = document.getElementById('countdown');
                this.photosGrid = document.getElementById('photosGrid');
                this.progressFill = document.getElementById('progressFill');
                this.statusMessage = document.getElementById('statusMessage');
                this.actionButtons = document.getElementById('actionButtons');
                this.resetBtn = document.getElementById('resetBtn');
                this.createCollageBtn = document.getElementById('createCollageBtn');
                
                this.photoCount = 1;
                this.timerDuration = 3;
                this.capturedPhotos = [];
                this.sessionId = this.generateSessionId();
                this.isCapturing = false;
                this.currentFacingMode = 'user';

                this.init();
            }

            generateSessionId() {
                // Generate a clean session ID with only alphanumeric characters
                const timestamp = Date.now().toString();
                const random = Math.random().toString(36).substr(2, 9);
                return 'session' + timestamp + random.replace(/[^a-zA-Z0-9]/g, '');
            }

            async init() {
                await this.setupCamera();
                this.setupEventListeners();
                this.updatePhotosGrid();
            }

            async setupCamera() {
                try {
                    // Try with ideal constraints first
                    let stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            width: { ideal: 1280 },
                            height: { ideal: 720 },
                            facingMode: 'user' // Front camera for selfies
                        } 
                    });
                    this.video.srcObject = stream;
                    
                    // Wait for video to be ready
                    await new Promise((resolve) => {
                        this.video.onloadedmetadata = () => {
                            this.video.play();
                            resolve();
                        };
                    });
                    
                    this.showStatus('Camera ready! 📸', 'success');
                } catch (error) {
                    console.error('Camera error:', error);
                    
                    // Try with basic constraints as fallback
                    try {
                        let stream = await navigator.mediaDevices.getUserMedia({ 
                            video: true 
                        });
                        this.video.srcObject = stream;
                        
                        await new Promise((resolve) => {
                            this.video.onloadedmetadata = () => {
                                this.video.play();
                                resolve();
                            };
                        });
                        
                        this.showStatus('Camera ready with basic settings! 📸', 'success');
                    } catch (fallbackError) {
                        console.error('Fallback camera error:', fallbackError);
                        
                        // Show specific error messages
                        if (fallbackError.name === 'NotAllowedError') {
                            this.showStatus('Camera access denied. Please allow camera permissions in your browser settings.', 'error');
                        } else if (fallbackError.name === 'NotFoundError') {
                            this.showStatus('No camera found on this device.', 'error');
                        } else if (fallbackError.name === 'NotReadableError') {
                            this.showStatus('Camera is being used by another application. Please close other camera apps.', 'error');
                        } else {
                            this.showStatus('Camera error: ' + fallbackError.message + '. Try refreshing the page.', 'error');
                        }
                    }
                }
            }

            setupEventListeners() {
                // Photo count selection
                document.querySelectorAll('.count-option').forEach(option => {
                    option.addEventListener('click', (e) => {
                        document.querySelectorAll('.count-option').forEach(opt => opt.classList.remove('active'));
                        e.target.classList.add('active');
                        this.photoCount = parseInt(e.target.dataset.count);
                        this.updatePhotosGrid();
                        this.updateProgress();
                    });
                });

                // Timer duration
                document.getElementById('timerDuration').addEventListener('change', (e) => {
                    this.timerDuration = parseInt(e.target.value);
                });

                // Capture button
                this.captureBtn.addEventListener('click', () => {
                    if (!this.isCapturing) {
                        this.startPhotoSession();
                    }
                });

                // Reset button
                this.resetBtn.addEventListener('click', () => {
                    this.resetSession();
                });

                // Create collage button
                this.createCollageBtn.addEventListener('click', () => {
                    this.createCollage();
                });

                // Switch camera button
                document.getElementById('switchCameraBtn').addEventListener('click', () => {
                    this.switchCamera();
                });
            }

            updatePhotosGrid() {
                this.photosGrid.innerHTML = '';
                for (let i = 0; i < this.photoCount; i++) {
                    const photoDiv = document.createElement('div');
                    photoDiv.className = 'photo-preview';
                    photoDiv.innerHTML = `
                        <div class="photo-number">${i + 1}</div>
                        ${this.capturedPhotos[i] ? 
                            `<img src="${this.capturedPhotos[i].url}" alt="Photo ${i + 1}">` : 
                            `<i class="fas fa-camera" style="font-size: 24px;"></i>`
                        }
                    `;
                    this.photosGrid.appendChild(photoDiv);
                }
            }

            updateProgress() {
                const progress = (this.capturedPhotos.length / this.photoCount) * 100;
                this.progressFill.style.width = progress + '%';
            }

            async startPhotoSession() {
                if (this.capturedPhotos.length >= this.photoCount) {
                    this.showStatus('Photo session complete!', 'success');
                    return;
                }

                this.isCapturing = true;
                this.captureBtn.disabled = true;
                
                await this.countdown_timer();
                await this.capturePhoto();
                
                this.isCapturing = false;
                this.captureBtn.disabled = false;

                if (this.capturedPhotos.length < this.photoCount) {
                    this.captureBtn.textContent = `Capture Photo ${this.capturedPhotos.length + 1}`;
                } else {
                    this.captureBtn.style.display = 'none';
                    this.actionButtons.style.display = 'flex';
                    this.showStatus('All photos captured! Create your collage now.', 'success');
                }
            }

            async countdown_timer() {
                this.countdown.style.display = 'flex';
                
                for (let i = this.timerDuration; i > 0; i--) {
                    this.countdown.textContent = i;
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
                
                this.countdown.style.display = 'none';
            }

            async capturePhoto() {
                try {
                    // Get video dimensions
                    const videoWidth = this.video.videoWidth;
                    const videoHeight = this.video.videoHeight;
                    
                    // Calculate aspect ratio
                    const aspectRatio = videoWidth / videoHeight;
                    
                    // Set canvas to a standard size while maintaining aspect ratio
                    let canvasWidth = 800; // Standard width
                    let canvasHeight = Math.round(canvasWidth / aspectRatio);
                    
                    // If height is too large, adjust based on height instead
                    if (canvasHeight > 600) {
                        canvasHeight = 600;
                        canvasWidth = Math.round(canvasHeight * aspectRatio);
                    }
                    
                    // Set canvas dimensions
                    this.canvas.width = canvasWidth;
                    this.canvas.height = canvasHeight;
                    
                    // Draw video frame to canvas maintaining aspect ratio
                    this.ctx.drawImage(this.video, 0, 0, canvasWidth, canvasHeight);
                    
                    // Convert to blob with high quality
                    const dataURL = this.canvas.toDataURL('image/png', 0.9);
                    
                    // Validate the image data
                    if (!dataURL || !dataURL.startsWith('data:image/')) {
                        throw new Error('Failed to capture image from camera');
                    }
                    
                    // Get CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfToken) {
                        throw new Error('CSRF token not found');
                    }
                    
                    console.log('Sending capture request:', {
                        session_id: this.sessionId,
                        photo_count: this.photoCount,
                        image_length: dataURL.length,
                        canvas_size: `${canvasWidth}x${canvasHeight}`,
                        video_size: `${videoWidth}x${videoHeight}`
                    });
                    
                    // Use Laravel route name for proper URL generation
                    const response = await fetch('{{ route("photobooth.capture") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                        },
                        body: JSON.stringify({
                            image: dataURL,
                            photo_count: this.photoCount,
                            session_id: this.sessionId
                        })
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('HTTP Error:', response.status, errorText);
                        throw new Error(`HTTP ${response.status}: ${errorText}`);
                    }

                    const result = await response.json();
                    console.log('Capture response:', result);
                    
                    if (result.success) {
                        this.capturedPhotos.push({
                            filename: result.filename,
                            url: result.path
                        });
                        this.updatePhotosGrid();
                        this.updateProgress();
                        this.showStatus(`Photo ${this.capturedPhotos.length} captured!`, 'success');
                    } else {
                        throw new Error(result.error || 'Failed to capture photo');
                    }
                } catch (error) {
                    console.error('Capture error:', error);
                    this.showStatus('Error capturing photo: ' + error.message, 'error');
                }
            }

            async createCollage() {
                if (this.capturedPhotos.length === 0) {
                    this.showStatus('No photos to create collage with.', 'error');
                    return;
                }

                this.createCollageBtn.disabled = true;
                this.createCollageBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

                try {
                    const response = await fetch('{{ route("photobooth.collage") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            photos: this.capturedPhotos.map(photo => photo.filename),
                            session_id: this.sessionId
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        this.showStatus('Collage created successfully!', 'success');
                        // Automatically download the collage
                        window.location.href = result.download_url;
                    } else {
                        throw new Error(result.error || 'Failed to create collage');
                    }
                } catch (error) {
                    console.error('Collage creation error:', error);
                    this.showStatus('Error creating collage: ' + error.message, 'error');
                } finally {
                    this.createCollageBtn.disabled = false;
                    this.createCollageBtn.innerHTML = '<i class="fas fa-download"></i> Create & Download';
                }
            }

            resetSession() {
                this.capturedPhotos = [];
                this.sessionId = this.generateSessionId();
                this.updatePhotosGrid();
                this.updateProgress();
                this.captureBtn.style.display = 'block';
                this.captureBtn.textContent = 'Start Photo Session';
                this.actionButtons.style.display = 'none';
                this.showStatus('Session reset. Ready for new photos!', 'success');
            }

            showStatus(message, type) {
                this.statusMessage.innerHTML = `<div class="status-message status-${type}">${message}</div>`;
                setTimeout(() => {
                    this.statusMessage.innerHTML = '';
                }, 5000);
            }

            async switchCamera() {
                try {
                    // Stop current stream
                    if (this.video.srcObject) {
                        this.video.srcObject.getTracks().forEach(track => track.stop());
                    }

                    // Toggle between front and back camera
                    this.currentFacingMode = this.currentFacingMode === 'user' ? 'environment' : 'user';
                    
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: this.currentFacingMode,
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    });
                    
                    this.video.srcObject = stream;
                    
                    await new Promise((resolve) => {
                        this.video.onloadedmetadata = () => {
                            this.video.play();
                            resolve();
                        };
                    });
                    
                    const cameraType = this.currentFacingMode === 'user' ? 'Front' : 'Back';
                    this.showStatus(`${cameraType} camera activated! 📸`, 'success');
                } catch (error) {
                    this.showStatus('Error switching camera: ' + error.message, 'error');
                    // Fallback to any available camera
                    this.setupCamera();
                }
            }
        }

        // Initialize the photobooth when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            new Photobooth();
        });
    </script>
</body>
</html> 