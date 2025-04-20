import cv2
import numpy as np
import mediapipe as mp
from keras.models import load_model
import sys
import json

# Load the trained model and labels
model = load_model("model.h5")
label = np.load("labels.npy")

# Initialize MediaPipe Holistic and Hands solutions
holistic = mp.solutions.holistic
hands = mp.solutions.hands
holis = holistic.Holistic()

# Process the image
image_path = sys.argv[1]
frm = cv2.imread(image_path)
res = holis.process(cv2.cvtColor(frm, cv2.COLOR_BGR2RGB))

lst = []

if res.face_landmarks:
    # Extract face landmarks and normalize them
    for i in res.face_landmarks.landmark:
        lst.append(i.x - res.face_landmarks.landmark[1].x)
        lst.append(i.y - res.face_landmarks.landmark[1].y)

    # Extract left hand landmarks and normalize them
    if res.left_hand_landmarks:
        for i in res.left_hand_landmarks.landmark:
            lst.append(i.x - res.left_hand_landmarks.landmark[8].x)
            lst.append(i.y - res.left_hand_landmarks.landmark[8].y)
    else:
        for _ in range(42):
            lst.append(0.0)

    # Extract right hand landmarks and normalize them
    if res.right_hand_landmarks:
        for i in res.right_hand_landmarks.landmark:
            lst.append(i.x - res.right_hand_landmarks.landmark[8].x)
            lst.append(i.y - res.right_hand_landmarks.landmark[8].y)
    else:
        for _ in range(42):
            lst.append(0.0)

    # Reshape the list to match the model's input shape
    lst = np.array(lst).reshape(1, -1)

    # Predict the label for the current frame
    pred = model.predict(lst)
    pred_label = label[np.argmax(pred)]

    # Return the prediction as JSON
    print(json.dumps({"prediction": pred_label}))
else:
    print(json.dumps({"prediction": "Unknown"}))