# landingpage/providers/add_menu_provider.py
from django.template.loader import render_to_string

def add_menu(request):
    """
    This context processor renders the landing page menu and injects it
    into the template context under the key 'add_menu'. This is similar to
    Laravel's AddMenuProvider which pushes a menu view into all named routes.
    """
    menu_html = render_to_string("landingpage/menu/landingpage.html")
    return {"add_menu": menu_html}
