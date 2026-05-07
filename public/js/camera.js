/**
 * Camera Module
 * Handles camera initialization, streaming, and frame capture
 */

let activeStream = null;

/**
 * Start the camera and attach stream to video element
 * @param {HTMLVideoElement} videoElement - The video element to stream to
 * @returns {Promise<boolean>} - Success status
 */
export async function startCamera(videoElement) {
    try {
        // Request camera access
        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            }
        });
        
        // Attach stream to video element
        videoElement.srcObject = stream;
        activeStream = stream;
        
        // Wait for video to be ready
        await new Promise((resolve) => {
            videoElement.onloadedmetadata = () => {
                videoElement.play();
                resolve();
            };
        });
        
        console.log('Camera started successfully');
        return true;
        
    } catch (error) {
        console.error('Failed to start camera:', error);
        return false;
    }
}

/**
 * Stop the camera and release resources
 * @param {HTMLVideoElement} videoElement - The video element to stop
 */
export function stopCamera(videoElement) {
    if (activeStream) {
        // Stop all tracks
        activeStream.getTracks().forEach(track => track.stop());
        activeStream = null;
    }
    
    // Clear video source
    if (videoElement) {
        videoElement.srcObject = null;
    }
    
    console.log('Camera stopped');
}

/**
 * Capture a frame from the video element as a Blob
 * @param {HTMLVideoElement} videoElement - The video element to capture from
 * @returns {Promise<Blob>} - The captured frame as a JPEG Blob
 */


export async function captureFrame(videoElement) {

    // Wait until video is ready
    if (videoElement.videoWidth === 0 || videoElement.videoHeight === 0) {
        console.warn("Video not ready yet");
        return null;
    }

    const canvas = document.createElement('canvas');
    canvas.width = videoElement.videoWidth;
    canvas.height = videoElement.videoHeight;

    const ctx = canvas.getContext('2d');
    
    // FIX: Mirror correction for face API
    // The video DISPLAY is mirrored (CSS transform: scaleX(-1) for normal UX)
    // But we need to send the non-mirrored frame to the face API
    // for correct facial landmark detection
    
    // Flip the canvas horizontally to correct the mirror effect
    // This ensures the face API receives the original (non-mirrored) frame
    ctx.translate(canvas.width, 0);  // Move origin to right side
    ctx.scale(-1, 1);                 // Flip horizontally (remove mirror effect)
    ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

    return new Promise((resolve) => {
        canvas.toBlob((blob) => {
            resolve(blob || null);
        }, 'image/jpeg', 0.8);
    });
}
