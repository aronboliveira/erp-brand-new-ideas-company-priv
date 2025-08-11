from django.contrib import admin
from django.urls import path, include

urlpatterns = [
 path('admin/', admin.site.urls, name="admin_main"),
 path('app/', include("app.urls"), name="path_include"),
 path('indicators/', include('app.indicator.urls')),
 path('labels/', include('app.labels.urls'))
]
