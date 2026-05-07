/**
 * API Module
 * Handles communication with the backend face recognition API
 */

// const API_BASE_URL = 'https://face-api-ipfa.onrender.com';
// const API_BASE_URL = 'https://ec2-35-87-137-123.us-west-2.compute.amazonaws.com/faceapi';
// const API_BASE_URL = 'http://127.0.0.1:5000';
const API_BASE_URL = 'https://100.23.131.184';
  
/**
 * Initialize registration session
 */
export async function initRegister(clientId) {
    try {
        const response = await fetch(`${API_BASE_URL}/faceapi/api/init_register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                client_id: clientId
            })
        });

        if (!response.ok) {
            throw new Error('Init register failed');
        }

        return await response.json();
    } catch (error) {
        console.error("Init register error:", error);
        return { success: false };
    }
}

/**
 * Send frame to process_frame API
 */
export async function processFrame(frameBlob, clientId) {
    try {
        const base64Frame = await blobToBase64(frameBlob);

        const response = await fetch(`${API_BASE_URL}/faceapi/api/process_frame`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                frame: base64Frame,
                client_id: clientId
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error ${response.status}`);
        }

        return await response.json();

    } catch (error) {
        console.error("Process frame error:", error);
        return { success: false };
    }
}


/**
 * Convert Blob to base64
 */
function blobToBase64(blob) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result.split(',')[1]);
        reader.onerror = reject;
        reader.readAsDataURL(blob);
    });
}
