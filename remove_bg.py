from rembg import remove
from PIL import Image
import os

input_path = r'c:\laragon\www\Prototipo_Base\public\images\hero_doctor.jpg'
output_path = r'c:\laragon\www\Prototipo_Base\public\images\hero_doctor_nobg.png'

print("Opening image...")
input_image = Image.open(input_path)

print("Removing background...")
output_image = remove(input_image)

print("Saving image...")
output_image.save(output_path)

print("Done.")
