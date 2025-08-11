from django.core.management.base import BaseCommand
from ...Models.planning.plan import Plan

class PlansTableSeeder(BaseCommand):
    help = 'Seeds the Plans table with the default free plan.'

    def handle(self, *args, **kwargs):
        # Create the Free Plan if it doesn't exist
        if not Plan.objects.filter(name='Free Plan').exists():
            Plan.objects.create(
                name='Free Plan',
                price=0,
                duration='lifetime',
                max_users=5,
                max_customers=5,
                max_vendors=5,
                max_clients=5,
                storage_limit=1024,
                crm=True,
                hrm=True,
                account=True,
                project=True,
                pos=True,
                chatgpt=True,
                image='free_plan.png',
            )
            self.stdout.write(self.style.SUCCESS("Free Plan created successfully."))
        else:
            self.stdout.write("Free Plan already exists.")
