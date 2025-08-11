from django.core.management.base import BaseCommand
from .LandingPageDataTableSeeder import run

class LandingBaseDatabaseSeeder(BaseCommand):
    help = 'Seed the landing page settings data'

    def handle(self, *args, **kwargs):
        self.stdout.write(self.style.NOTICE('Running landing page seeders...'))
        run()
        self.stdout.write(self.style.SUCCESS('Landing page settings seeded successfully.'))
