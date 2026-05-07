window.addEventListener("message", function(event) {
    if (event.data.type === "FACE_RECOGNITION_COMPLETE") {
        let faceId = event.data.faceId;
        console.log("Face ID received:", faceId);
        
        // Example client implementation
        // document.getElementById("face_id").value = faceId;
        // fetchCustomerDetails(faceId);
    }
});
