from django.core.management.base import BaseCommand
from django.core.management import call_command
from .notification_seeder import NotificationSeeder
from .plans_table_seeder import PlansTableSeeder
from .users_table_seeder import UsersTableSeeder
from .ai_template_seeder import AiTemplateSeeder
from ...Models.utils.utility import Utility

class DatabaseSeeder(BaseCommand):
    help = 'Seed the application\'s database'

    def add_arguments(self, parser):
        parser.add_argument(
            '--updater',
            action='store_true',
            help='Run in updater mode (simulate LaravelUpdater::database)'
        )

    def handle(self, *args, **options):
        # Seed notifications
        self.stdout.write("Seeding notifications...")
        NotificationSeeder().run()

        # Run module migrations and seed for LandingPage.
        # Assuming "landingpage" is the app label for that module and you have a custom seeder.
        self.stdout.write("Migrating LandingPage module...")
        call_command('migrate', 'landingpage')
        self.stdout.write("Seeding LandingPage module...")
        call_command('seed_landingpage')  # assumes you have a management command for landing page seeding

        # Conditionally seed further data
        if not options['updater']:
            self.stdout.write("Seeding plans, users, and AI templates...")
            PlansTableSeeder().run()
            UsersTableSeeder().run()
            AiTemplateSeeder().run()
        else:
            self.stdout.write("Updater mode: Running language creation...")
            Utility.languagecreate()

        self.stdout.write(self.style.SUCCESS("Database seeded successfully."))
