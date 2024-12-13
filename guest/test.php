<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>FaceMesh Lip Detection</title>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/facemesh"></script>
</head>

<body>
    <video id="video" autoplay playsinline></video>
    <canvas id="output" style="border: 1px solid black;"></canvas>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('output');
        const ctx = canvas.getContext('2d');

        async function setupCamera() {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: 640,
                    height: 480
                }
            });
            video.srcObject = stream;
            return new Promise((resolve) => {
                video.onloadedmetadata = () => {
                    resolve(video);
                };
            });
        }

        async function main() {
            await setupCamera();
            video.play();
            const model = await facemesh.load();
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            async function detect() {
                if (video.readyState >= 2) { // Check if video is ready
                    const predictions = await model.estimateFaces({
                        input: video
                    });
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    if (predictions.length > 0) {
                        predictions.forEach(prediction => {
                            const keypoints = prediction.scaledMesh;

                            // Vẽ tất cả các điểm đặc trưng của khuôn mặt
                            ctx.fillStyle = 'green';
                            keypoints.forEach(([x, y]) => {
                                ctx.beginPath();
                                ctx.arc(x, y, 1, 0, 2 * Math.PI);
                                ctx.fill();
                            });
                        });
                    }
                }
                requestAnimationFrame(detect);
            }

            detect();
        }

        main();
    </script>
</body>

</html>