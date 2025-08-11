from django.db import models
from django.core.files.storage import default_storage
from django.core.validators import MinValueValidator
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, defalt_decicmal_10, default_decimal_12,
                               VOID, VALID_FILE_SIZES, FILE_SIZE_VALIDATOR)

PRODUCT_CATEGORY_CHOICES = [
    ("computer_hardware", "Computer Hardware (Desktops, Laptops, Components)"),
    ("server_infrastructure", "Server Infrastructure (Racks, Blades, Data Center)"),
    ("networking_equipment", "Networking Equipment (Routers, Switches, Modems)"),
    ("mobile_devices", "Mobile Devices (Phones, Tablets, Handhelds)"),
    ("iot_devices", "IoT Devices (Home Automation, Smart Gadgets)"),
    ("robotics_automation", "Robotics & Automation"),
    ("wearables", "Wearables (Smartwatches, AR Glasses, Fitness Trackers)"),
    ("software_media", "Software & Media (OS, Productivity Suites, Games)"),
    ("technology", "General Technology"),
    ("electronics", "Consumer Electronics"),
    ("fashion", "Fashion & Apparel"),
    ("food_beverage", "Food & Beverage"),
    ("home_garden", "Home & Garden"),
    ("health_beauty", "Health & Beauty"),
    ("sports_outdoors", "Sports & Outdoors"),
    ("auto_industrial", "Automotive & Industrial"),
    ("arts_crafts", "Arts & Crafts"),
    ("books_media", "Books & Media"),
    ("children_infant", "Children & Infant"),
    ("furniture_decor", "Furniture & Decor"),
    ("jewelry_accessories", "Jewelry & Accessories"),
    ("office_business", "Office & Business"),
    ("pet_supplies", "Pet Supplies"),
    ("toys_games", "Toys & Games"),
    ("travel", "Travel"),
    ("other", "Other"),
]

class Product(Describable):
  name = default_char_field()
  price = defalt_decicmal_10()
  service = models.ForeignKey("ProductService", on_delete=models.CASCADE, related_name="services")
  image = models.ImageField(upload_to='product_images/', storage=default_storage, validators=[FILE_SIZE_VALIDATOR], **VOID)
  image_url = models.URLField(max_length=200, **VOID)
  image_size = models.IntegerField(validators=VALID_FILE_SIZES, **VOID)
  type = default_char_field(default='other', choices=PRODUCT_CATEGORY_CHOICES)
  quantity = models.PositiveIntegerField(validators={MinValueValidator(1)})
  tax = default_decimal_12(**VOID)
  discount = default_decimal_12(**VOID)
  total = default_decimal_12(**VOID)
  category = models.ForeignKey('ProductCategory', on_delete=models.SET_NULL, related_name='products', 
                               help_text='Optional FK for clustering', **VOID)
  
  custom_field = None  # non-persistent attribute
  
  class Meta:
    db_table = 'product'
