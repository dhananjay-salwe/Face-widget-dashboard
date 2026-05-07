const BASE_URL  = window.WidgetConfig?.base_url  || '';
const WIDGET_ID = window.WidgetConfig?.widget_id || '';

// All imports go through /widget-js/ Laravel route — CORS always present
Promise.all([
    import(BASE_URL + '/widget-js/load.php?f=camera.js'),
    import(BASE_URL + '/widget-js/load.php?f=api.js'),
    import(BASE_URL + '/widget-js/load.php?f=ui.js'),
]).then(([cameraModule, apiModule, uiModule]) =>  {

    const { startCamera, stopCamera, captureFrame } = cameraModule;
    const { initRegister, processFrame }            = apiModule;
    const {
        setIdleState, setScanningState, setRecognizedState,
        updateStatus, setErrorState, updateProgress,
        showBlinkHint, hideBlinkHint,
        showWarning, hideWarning,
        showFaceGuide, hideFaceGuide,
    } = uiModule;

    const shadow = window.FaceWidgetShadowRoot || document;

    const CLIENT_ID      = "client_" + crypto.randomUUID();
    const FRAME_INTERVAL = 800;

    const videoElement  = shadow.getElementById("scannerVideo");
    const startButton   = shadow.getElementById("startButton");
    const captureCanvas = shadow.getElementById("capture-canvas");
    const captureCtx    = captureCanvas ? captureCanvas.getContext("2d") : null;

    let isScanning      = false;
    let scanInterval    = null;
    let isProcessing    = false;
    let lastStatusText  = "";
    let usageRegistered = false;
    let frozenImageUrl  = null;

    // ====================================================================
    // USAGE TRACKING
    // ====================================================================

    async function registerUsageHit() {
        if (usageRegistered || !WIDGET_ID || !BASE_URL) return;
        usageRegistered = true;
        try {
            await fetch(`${BASE_URL}/widget/${WIDGET_ID}/track-hit`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    // "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ client_id: CLIENT_ID }),
            });
        } catch (e) {
            console.error("[FaceWidget] Failed to register usage hit:", e);
        }
    }

    // ====================================================================
    // MAIN CAPTURE FLOW
    // ====================================================================

    async function startCapture() {
        if (isScanning) return;

        isScanning = true;
        setScanningState();
        showFaceGuide();
        updateProgress(0);

        updateStatus("Starting camera...");
        const cameraStarted = await startCamera(videoElement);

        if (!cameraStarted) {
            setErrorState("Camera access denied");
            isScanning = false;
            hideFaceGuide();
            return;
        }

        updateStatus("Initializing session...");
        const initResult = await initRegister(CLIENT_ID);

        if (!initResult || initResult.success === false) {
            setErrorState("Init failed");
            stopCamera(videoElement);
            isScanning = false;
            hideFaceGuide();
            return;
        }

        await new Promise((resolve) => setTimeout(resolve, 500));
        await registerUsageHit();

        updateStatus("Scanning...");
        scanInterval = setInterval(async () => { await scanFrame(); }, FRAME_INTERVAL);
        await scanFrame();
    }

    // ====================================================================
    // FRAME SCANNING
    // ====================================================================

    async function scanFrame() {
        if (!isScanning || isProcessing) return;
        isProcessing = true;

        try {
            const frameBlob = await captureFrame(videoElement);
            if (!frameBlob) return;

            const result = await processFrame(frameBlob, CLIENT_ID);
            console.log("API result:", result);
            if (!result) return;

            applyBackendState(result);

            if (result.final === true) {
                stopCapture();
                stopCamera(videoElement);
                hideBlinkHint();
                hideFaceGuide();
                hideWarning();
            }

            if (result.stop_sending) {
                stopCapture();
                hideFaceGuide();
                hideBlinkHint();
                if (!result.final) {
                    stopCamera(videoElement);
                    setErrorState(result.status || "Scan stopped");
                }
            }

            if (result.final === true && result.face_id) {
                handleRecognition(result.face_id);
            } else if (result.final === true && !result.face_id) {
                handleFailedRecognition(result.status || "Recognition failed");
            }

        } catch (error) {
            console.error("Frame processing error:", error);
        } finally {
            isProcessing = false;
        }
    }

    // ====================================================================
    // BACKEND STATE → UI
    // ====================================================================

    function applyBackendState(result) {
        if (!result || !result.status) return;

        if (result.status !== lastStatusText) {
            updateStatus(result.status);
            lastStatusText = result.status;
        }

        const msg = result.status.toLowerCase();

        if (msg.includes("spoof")  || msg.includes("detected") ||
            msg.includes("failed") || msg.includes("error")    ||
            msg.includes("locked")) {
            setErrorState(result.status);
            showWarning(result.status);
            hideBlinkHint();
            return;
        }

        if (msg.includes("confirming") || msg.includes("liveness")) {
            showBlinkHint();
        } else {
            hideBlinkHint();
        }

        if (msg.includes("hold still") || msg.includes("confirming") || msg.includes("liveness")) {
            setScanningState();
            showFaceGuide();
            updateProgress(50);
            return;
        }

        if (msg.includes("capturing")) {
            setScanningState();
            showFaceGuide();
            const match = result.status.match(/(\d+)\/(\d+)/);
            if (match) {
                updateProgress(Math.min((parseInt(match[1]) / parseInt(match[2])) * 100, 95));
            }
            return;
        }

        if (msg.includes("recognized")) {
            updateProgress(100);
            setRecognizedState();
            hideBlinkHint();
            hideWarning();
            return;
        }

        setScanningState();
    }

    // ====================================================================
    // RESULT HANDLERS
    // ====================================================================

    function handleRecognition(faceId) {
        console.log("[FaceWidget] Face recognized! ID:", faceId);

        stopCapture();
        hideBlinkHint();
        hideWarning();
        hideFaceGuide();
        updateProgress(100);
        setRecognizedState();
        updateStatus(`Recognized! ID: ${faceId || "Unknown"}`);

        if (window.parent !== window) {
            window.parent.postMessage({
                type:      "FACE_RECOGNITION_COMPLETE",
                faceId:    faceId,
                timestamp: new Date().toISOString(),
                baseUrl:  BASE_URL,
            }, "*");
        }

        const img = shadow.getElementById("captured-image");
        if (img) {
            setTimeout(() => { img.src = ""; img.style.display = "none"; }, 5000);
        }

        setTimeout(() => { setIdleState(); updateProgress(0); }, 5000);
    }

    function handleFailedRecognition(message) {
        stopCapture();
        hideFaceGuide();
        hideBlinkHint();
        hideWarning();
        setErrorState(message);
        setTimeout(() => { setIdleState(); updateProgress(0); }, 3000);
    }

    // ====================================================================
    // STOP / INIT
    // ====================================================================

    function stopCapture() {
        isScanning = false;
        if (scanInterval) {
            clearInterval(scanInterval);
            scanInterval = null;
        }
    }

    function init() {
        console.log("[FaceWidget] Initialized");
        setIdleState();
        if (startButton) {
            startButton.addEventListener("click", startCapture);
        } else {
            setTimeout(startCapture, 1000);
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    window.addEventListener("beforeunload", () => {
        stopCapture();
        stopCamera(videoElement);
    });

}).catch(err => {
    console.error("[FaceWidget] Failed to load modules:", err);
});
