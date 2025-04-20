import streamlit as st
import cv2
import numpy as np
import mediapipe as mp
from keras.models import load_model
import time
import spotipy
from spotipy.oauth2 import SpotifyOAuth

model = load_model("model.h5")
label = np.load("labels.npy")
holistic = mp.solutions.holistic
hands = mp.solutions.hands
holis = holistic.Holistic()
drawing = mp.solutions.drawing_utils

# Spotify API credentials
SPOTIPY_CLIENT_ID = "5e12f19bc6cd403caa101f80190c8f70"
SPOTIPY_CLIENT_SECRET = "8c0e86b9044542c68650557a96eb579a"
SPOTIPY_REDIRECT_URI = "http://localhost:8501/callback"

# Initialize Spotify client
sp = spotipy.Spotify(auth_manager=SpotifyOAuth(client_id="5e12f19bc6cd403caa101f80190c8f70",
                                               client_secret="8c0e86b9044542c68650557a96eb579a",
                                               redirect_uri="http://localhost:8501/callback",
                                               scope="user-modify-playback-state"))

# Mood-based Spotify playlists
mood_playlists = {
    "happy": "spotify:playlist:37i9dQZF1DXdPec7aLTmlC",  # Happy Hits
    "sad": "https://open.spotify.com/playlist/5vQGwceLcJTwMn21ilivs9?si=MsiRbSL2Th2A1rpTxS-dQA",    # Sad Songs
    "calm": "spotify:playlist:37i9dQZF1DX4WYpdgoIcn6",   # Chill Hits
    "rock": "https://open.spotify.com/playlist/3RXo0vuDK7aGctpeUXB6jP?si=cMWNK58dTpS5aWEnx-a1Og",  # Energetic
}
mood_mapping = {
    "sadness": "sad",
    "happiness": "happy",
    "calmness": "calm",
    "energetic": "rock",
}
st.title("Mood-Based Music Player")
st.write("Capture your mood, and we'll play Spotify music that matches it!")
def capture_and_predict_mood():
    # Start capturing video from the webcam
    cap = cv2.VideoCapture(0)
    camera_placeholder = st.empty()
    start_time = time.time()
    while time.time() - start_time < 10:
        _, frm = cap.read()
        frm = cv2.flip(frm, 1)
        camera_placeholder.image(frm, channels="BGR", caption="Capturing your mood...")
        if cv2.waitKey(1) == 27:
            break
    _, frm = cap.read()
    frm = cv2.flip(frm, 1)
    res = holis.process(cv2.cvtColor(frm, cv2.COLOR_BGR2RGB))

    lst = []

    if res.face_landmarks:
        for i in res.face_landmarks.landmark:
            lst.append(i.x - res.face_landmarks.landmark[1].x)
            lst.append(i.y - res.face_landmarks.landmark[1].y)
        if res.left_hand_landmarks:
            for i in res.left_hand_landmarks.landmark:
                lst.append(i.x - res.left_hand_landmarks.landmark[8].x)
                lst.append(i.y - res.left_hand_landmarks.landmark[8].y)
        else:
            for i in range(42):
                lst.append(0.0)
        if res.right_hand_landmarks:
            for i in res.right_hand_landmarks.landmark:
                lst.append(i.x - res.right_hand_landmarks.landmark[8].x)
                lst.append(i.y - res.right_hand_landmarks.landmark[8].y)
        else:
            for i in range(42):
                lst.append(0.0)
        lst = np.array(lst).reshape(1, -1)
        pred = model.predict(lst)
        pred_label = label[np.argmax(pred)]
        pred_label = pred_label.lower()
        pred_label = mood_mapping.get(pred_label, pred_label)
        st.success(f"Predicted Mood: {pred_label}")
        if pred_label in mood_playlists:
            playlist_uri = mood_playlists[pred_label]
            sp.start_playback(context_uri=playlist_uri)
            st.write(f"Now playing: {pred_label} playlist on Spotify!")
        else:
            st.warning(f"No playlist found for the mood: {pred_label}")
            # Play a default playlist
            default_playlist = mood_playlists.get("calm")  # Fallback to calm music
            sp.start_playback(context_uri=default_playlist)
            st.write("Playing default calm music.")
    cap.release()
if st.button("Capture Mood and Play Music"):
    capture_and_predict_mood()
