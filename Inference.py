import cv2
import numpy as np
import mediapipe as mp
from keras.models import load_model
import time

# Load the trained model and labels
model = load_model("model.h5")
label = np.load("labels.npy")

# Initialize MediaPipe Holistic and Hands solutions
holistic = mp.solutions.holistic
hands = mp.solutions.hands
holis = holistic.Holistic()
drawing = mp.solutions.drawing_utils

# Start capturing video from the webcam
cap = cv2.VideoCapture(0)

# Keep the camera window open for 15 seconds
start_time = time.time()
while time.time() - start_time < 15:
    # Read a frame from the webcam
    _, frm = cap.read()

    # Flip the frame horizontally for a mirror effect
    frm = cv2.flip(frm, 1)

    # Display the frame in a window
    cv2.putText(frm, "Capturing in progress...", (50, 50), cv2.FONT_ITALIC, 1, (0, 255, 0), 2)
    cv2.imshow("Camera Feed", frm)

    # Wait for 1 millisecond and check if the user pressed 'Esc' to exit
    if cv2.waitKey(1) == 27:
        break

# Capture a single frame after 15 seconds
_, frm = cap.read()

# Flip the frame horizontally for a mirror effect
frm = cv2.flip(frm, 1)

# Process the frame with MediaPipe Holistic
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
        for i in range(42):
            lst.append(0.0)

    # Extract right hand landmarks and normalize them
    if res.right_hand_landmarks:
        for i in res.right_hand_landmarks.landmark:
            lst.append(i.x - res.right_hand_landmarks.landmark[8].x)
            lst.append(i.y - res.right_hand_landmarks.landmark[8].y)
    else:
        for i in range(42):
            lst.append(0.0)

    # Reshape the list to match the model's input shape
    lst = np.array(lst).reshape(1, -1)

    # Predict the label for the current frame
    pred = model.predict(lst)
    pred_label = label[np.argmax(pred)]

    # Display the predicted label
    print("Predicted Label:", pred_label)

    # Draw the landmarks on the frame
    drawing.draw_landmarks(frm, res.face_landmarks, holistic.FACEMESH_CONTOURS)
    drawing.draw_landmarks(frm, res.left_hand_landmarks, hands.HAND_CONNECTIONS)
    drawing.draw_landmarks(frm, res.right_hand_landmarks, hands.HAND_CONNECTIONS)

    # Display the frame with the predicted label
    cv2.putText(frm, f"Prediction: {pred_label}", (50, 50), cv2.FONT_ITALIC, 1, (255, 0, 0), 2)
    cv2.imshow("Prediction Result", frm)
    cv2.waitKey(0)  # Wait for a key press to close the window

# Release the webcam and close all OpenCV windows
cap.release()
cv2.destroyAllWindows()