from flask import Flask, request, jsonify
from PIL import Image
import pytesseract
import spacy
import re
import os

# Specify the path to Tesseract executable
pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract.exe'

# Load spaCy model
nlp = spacy.load("en_core_web_sm")

app = Flask(__name__)

# Function to extract data from text using regex
def extract_data(text):
    pattern = re.compile(r'(\d+)\)\s(\d+)\s(\d+\.\d+)\s(\w+)\s(\d+\.\d+)\s(\d+\.\d+)\s(.+)\s(\d+)\s(\d+)\s(.+)\s(.+)\s(\d+\w+)\s(.+)\s(.+)\s(\d+\.\d+)\s(\w+)\s(\d+\.\d+)\s(.+)')
    matches = pattern.findall(text)
    labeled_data = []
    for match in matches:
        labeled_data.append({
            "Sr No": match[0],
            "Material Code": match[1],
            "Quantity": match[2],
            "Unit": match[3],
            "Identification Marks": match[4],
            "Est value": match[5],
            "Reversal": match[6],
            "Desc. of Goods": match[7],
            "HSN Code": match[8],
            "Nature of Processing": match[9],
            "Old Mat No": match[10],
            "Rate": match[11],
            "Heat Code": match[12],
            "Wt/Unit": match[13],
            "UoM": match[14],
            "Total Wt": match[15],
            "Due date": match[16],
            "RT Type": match[17],
        })
    return labeled_data

@app.route('/process_image', methods=['POST'])
def process_image():
    if 'file' not in request.files:
        return jsonify({'status': 'error', 'message': 'No file part'}), 400

    file = request.files['file']
    if file.filename == '':
        return jsonify({'status': 'error', 'message': 'No selected file'}), 400

    if file:
        # Save the file to a temporary location
        temp_filepath = os.path.join('/tmp', file.filename)
        file.save(temp_filepath)

        # Use Tesseract to extract text
        image = Image.open(temp_filepath)
        text = pytesseract.image_to_string(image)

        # Extract data from text
        labeled_data = extract_data(text)

        # Remove the temporary file
        os.remove(temp_filepath)

        return jsonify({'status': 'success', 'data': labeled_data})

if __name__ == '__main__':
    app.run(debug=True)
