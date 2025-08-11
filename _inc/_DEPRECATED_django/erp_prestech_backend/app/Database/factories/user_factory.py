import factory
import random
import string
from django.utils import timezone
from django.contrib.auth.hashers import make_password
from ...Models.individuals.user import User

class UserFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = User

    name = factory.Faker('name')
    email = factory.Faker('safe_email')
    email_verified_at = factory.LazyFunction(timezone.now)
    # Here we hash the password "password". In Laravel the hashed password is static.
    password = factory.LazyFunction(lambda: make_password("password"))
    remember_token = factory.LazyFunction(lambda: ''.join(random.choices(string.ascii_letters + string.digits, k=10)))

    # Trait to produce a user with an unverified email
    class Params:
        unverified = factory.Trait(
            email_verified_at=None,
        )
