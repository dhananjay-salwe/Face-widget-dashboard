// Helper function to safely get elements from the Shadow DOM
function getEl(id) {
    const root = window.FaceWidgetShadowRoot || document;
    return root.getElementById(id);
}

function setStatus(text) {
    const statusEl = getEl("statusText");
    if (statusEl) {
        statusEl.textContent = text;
    }
}

function setIdleState() {
    const circleEl = getEl("scannerCircle");
    if (circleEl) {
        circleEl.classList.remove("scanning", "recognized", "error");
    }
    setStatus("Ready");
    updateProgress(0);
}

function setScanningState() {
    const circleEl = getEl("scannerCircle");
    if (circleEl) {
        circleEl.classList.add("scanning");
        circleEl.classList.remove("recognized", "error");
    }
    setStatus("Scanning...");
}

function setRecognizedState() {
    const circleEl = getEl("scannerCircle");
    if (circleEl) {
        circleEl.classList.add("recognized");
        circleEl.classList.remove("scanning", "error");
    }
    setStatus("Recognized");
}

function setErrorState(message) {
    const circleEl = getEl("scannerCircle");
    if (circleEl) {
        circleEl.classList.add("error");
        circleEl.classList.remove("scanning", "recognized");
    }
    setStatus(message || "Error");
}

function updateStatus(message) {
    setStatus(message);
}

function updateProgress(percent) {
    const statusEl = getEl("statusText");
    const clamped = Math.max(0, Math.min(100, Number(percent) || 0));
    if (statusEl) {
        statusEl.dataset.progress = String(clamped);
    }
}

function showBlinkHint() {}
function hideBlinkHint() {}
function showWarning() {}
function hideWarning() {}
function showFaceGuide() {}
function hideFaceGuide() {}

export {
    setIdleState,
    setScanningState,
    setRecognizedState,
    updateStatus,
    setErrorState,
    updateProgress,
    showBlinkHint,
    hideBlinkHint,
    showWarning,
    hideWarning,
    showFaceGuide,
    hideFaceGuide,
};