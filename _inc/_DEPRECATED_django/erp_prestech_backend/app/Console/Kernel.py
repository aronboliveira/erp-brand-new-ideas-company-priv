import atexit
import time
from django.core.management import call_command
from apscheduler.schedulers.background import BackgroundScheduler
from myapp.management.commands.custom_command import Command as CustomCommand

class Kernel:
    def __init__(self):
        self.scheduler = BackgroundScheduler()
        self.register_commands()
        self.schedule()
        self.scheduler.start()
        atexit.register(lambda: self.scheduler.shutdown())

    def schedule(self):
        self.scheduler.add_job(lambda: call_command('inspire'), 'interval', hours=1)

    def register_commands(self):
        custom_command = CustomCommand()
        custom_command.handle()

if __name__ == "__main__":
    kernel = Kernel()
    try:
        while True:
            time.sleep(1)
    except (KeyboardInterrupt, SystemExit):
        pass
