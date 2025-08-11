import factory
from ..join_us import JoinUs

class JoinUsFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = JoinUs

    email = factory.Faker('email')
