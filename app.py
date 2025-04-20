from flask import Flask, request, jsonify
from deepface import DeepFace
import cv2
import numpy as np
import base64

app = Flask(__name__)

@app.route('/detect_mood', methods=['POST'])
def detect_mood():
    # Get the image data from the request
    image_data = request.json.get('image_data')
    if not image_data:
        return jsonify({"status": "error", "message": "No image data provided"}), 400

    # Decode the base64 image data
    try:
        image_data = base64.b64decode(image_data.split(',')[1])
        nparr = np.frombuffer(image_data, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    except Exception as e:
        return jsonify({"status": "error", "message": "Invalid image data"}), 400

    # Analyze the image for emotions
    try:
        result = DeepFace.analyze(img, actions=['emotion'], enforce_detection=False)
        emotion = result[0]['dominant_emotion']
        return jsonify({
            "status": "success",
            "emotion": emotion
        })
    except Exception as e:
        return jsonify({
            "status": "error",
            "message": str(e)
        }), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)