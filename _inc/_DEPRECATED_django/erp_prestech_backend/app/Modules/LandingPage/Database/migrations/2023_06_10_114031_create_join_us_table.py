from django.db import models

class JoinUs(models.Model):
    email = models.EmailField(unique=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'join_us'
        verbose_name = 'Join Us'
        verbose_name_plural = 'Join Us Entries'

    def __str__(self):
        return self.email
