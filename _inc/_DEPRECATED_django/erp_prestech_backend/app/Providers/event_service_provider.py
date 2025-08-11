from django.contrib.auth.models import User
from django.db.models.signals import post_save
from django.dispatch import receiver
from django.core.mail import send_mail

@receiver(post_save, sender=User)
def send_email_verification(sender, instance, created, **kwargs):
    if created:
        # Logic to send a verification email
        send_mail(
            subject='Verify Your Email',
            message='Click here to verify your email address.',
            from_email='no-reply@example.com',
            recipient_list=[instance.email],
            fail_silently=False,
        )
