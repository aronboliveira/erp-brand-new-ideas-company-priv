# hashers.py (custom hasher example)
import os
from django.contrib.auth.hashers import BCryptSHA256PasswordHasher

class CustomBCryptSHA256PasswordHasher(BCryptSHA256PasswordHasher):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        # By default, Django's BcryptSHA256 uses a cost of 12
        rounds = os.getenv('BCRYPT_ROUNDS', '10')
        # bcrypt rounds must be an integer between 4 and 31
        self.rounds = int(rounds)
import os

HASH_DRIVER = os.getenv('HASH_DRIVER', 'bcrypt')  # 'bcrypt' or 'argon'

if HASH_DRIVER == 'bcrypt':
    PASSWORD_HASHERS = [
        'myproject.hashers.CustomBCryptSHA256PasswordHasher',
        # ...
    ]
elif HASH_DRIVER == 'argon':
    PASSWORD_HASHERS = [
        'myproject.hashers.CustomArgon2PasswordHasher',
        # ...
    ]
# else default to something else