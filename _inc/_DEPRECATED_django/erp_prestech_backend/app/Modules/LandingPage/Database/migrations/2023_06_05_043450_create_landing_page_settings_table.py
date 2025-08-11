from django.db import models

class LandingPageSetting(models.Model):
    name = models.CharField(max_length=255, unique=True)
    value = models.TextField(blank=True, null=True, max_length=65535)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'landing_page_settings'
        verbose_name = 'Landing Page Setting'
        verbose_name_plural = 'Landing Page Settings'

    def __str__(self):
        return self.name
