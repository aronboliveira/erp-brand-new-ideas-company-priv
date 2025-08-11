import logging
import datetime
from django.db import models
from django.utils import timezone
from django.utils.timesince import timesince
from django.contrib.auth import get_user_model
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation

logger = logging.getLogger(__name__)

class Notification(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    user = models.ForeignKey("User", on_delete=models.CASCADE, db_index=True)
    type = models.CharField(max_length=100, db_index=True)
    data = models.JSONField()
    is_read = models.BooleanField(default=False)
    created_by = default_user_creation('%(class)s_created_by', db_index=True)

    def to_html(self) -> str:
        try:
            data = self.data
            link: str = "#"
            icon: str = "fa fa-bell"
            icon_color: str = "bg-primary"
            text: str = ""
            usr = None
            if data.get("updated_by"):
                User = get_user_model()
                usr = User.objects.filter(id=data.get("updated_by")).first()
            if usr:
                if self.type == "assign_deal":
                    link = f"/deals/{data.get('deal_id')}/"
                    text = (f"{usr.name} Added you in deal <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-plus"
                    icon_color = "bg-primary"
                elif self.type == "create_deal_call":
                    link = f"/deals/{data.get('deal_id')}/"
                    text = (f"{usr.name} Create new Deal Call in deal <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-phone"
                    icon_color = "bg-info"
                elif self.type == "update_deal_source":
                    link = f"/deals/{data.get('deal_id')}/"
                    text = (f"{usr.name} Update Sources in deal <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-file-alt"
                    icon_color = "bg-warning"
                elif self.type == "create_task":
                    link = f"/deals/{data.get('deal_id')}/"
                    text = (f"{usr.name} Create new Task in deal <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-tasks"
                    icon_color = "bg-primary"
                elif self.type == "add_product":
                    link = f"/deals/{data.get('deal_id')}/"
                    text = (f"{usr.name} Add new Products in deal <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-dolly"
                    icon_color = "bg-danger"
                elif self.type == "add_discussion":
                    link = f"/deals/{data.get('deal_id')}/"
                    text = (f"{usr.name} Add new Discussion in deal <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-comments"
                    icon_color = "bg-info"
                elif self.type == "move_deal":
                    link = f"/deals/{data.get('deal_id')}/"
                    text = (f"{usr.name} Moved the deal <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> from "
                            f"{str(data.get('old_status')).title()} to "
                            f"{str(data.get('new_status')).title()}")
                    icon = "fa fa-arrows-alt"
                    icon_color = "bg-primary"
                elif self.type == "assign_estimation":
                    link = f"/estimations/{data.get('estimation_id')}/"
                    text = (f"{usr.name} Added you in estimation <b class='font-weight-bold'>"
                            f"{data.get('estimation_name')}</b> ")
                    icon = "fa fa-plus"
                    icon_color = "bg-primary"
                elif self.type == "assign_lead":
                    link = f"/leads/{data.get('lead_id')}/"
                    text = (f"{usr.name} Added you in lead <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-plus"
                    icon_color = "bg-primary"
                elif self.type == "create_lead_call":
                    link = f"/leads/{data.get('lead_id')}/"
                    text = (f"{usr.name} Create new Lead Call in lead <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-phone"
                    icon_color = "bg-info"
                elif self.type == "update_lead_source":
                    link = f"/leads/{data.get('lead_id')}/"
                    text = (f"{usr.name} Update Sources in lead <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-file-alt"
                    icon_color = "bg-warning"
                elif self.type == "add_lead_product":
                    link = f"/leads/{data.get('lead_id')}/"
                    text = (f"{usr.name} Add new Products in lead <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-dolly"
                    icon_color = "bg-danger"
                elif self.type == "add_lead_discussion":
                    link = f"/leads/{data.get('lead_id')}/"
                    text = (f"{usr.name} Add new Discussion in lead <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> ")
                    icon = "fa fa-comments"
                    icon_color = "bg-info"
                elif self.type == "move_lead":
                    link = f"/leads/{data.get('lead_id')}/"
                    text = (f"{usr.name} Moved the lead <b class='font-weight-bold'>"
                            f"{data.get('name')}</b> from "
                            f"{str(data.get('old_status')).title()} to "
                            f"{str(data.get('new_status')).title()}")
                    icon = "fa fa-arrows-alt"
                    icon_color = "bg-primary"
            else:
                return ""
            date_str: str = f"{timesince(self.created_at, timezone.now())} ago"
            html: str = (
                f"<a href='{link}' class='list-group-item list-group-item-action'>"
                f"<div class='d-flex align-items-center'>"
                f"<div>"
                f"<span class='avatar {icon_color} text-white rounded-circle'>"
                f"<i class='{icon}'></i></span>"
                f"</div>"
                f"<div class='flex-fill ml-3'>"
                f"<div class='h6 text-sm mb-0'>{text}</div>"
                f"<small class='text-muted text-xs'>{date_str}</small>"
                f"</div>"
                f"</div>"
                f"</a>"
            )
            return html
        except Exception as e:
            logger.error(f"Failed to generate HTML for Notification: {e}")
            return ""

    class Meta:
        db_table = "notification"
