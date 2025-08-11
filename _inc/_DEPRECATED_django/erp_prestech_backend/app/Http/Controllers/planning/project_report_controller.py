import inspect
import logging
from datetime import date
from typing import Union
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db.models import Count, Sum
from django.http import HttpRequest, HttpResponse, JsonResponse, Http404
from django.shortcuts import render, get_object_or_404
from django.utils.decorators import method_decorator
from django.contrib.auth.decorators import login_required
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._helpers.security import permission_required_custom, security_logger
from .._traits.controller import Controller
from ....Models.individuals.user import User
from ....Models.planning.milestone import Milestone
from ....Models.planning.project import Project
from ....Models.planning.project_task import ProjectTask
from ....Models.planning.task_stage import TaskStage
from ....Models.shapes.timesheet import Timesheet
from ....Models.utils.utility import Utility
from ....Exports.task_report_export import TaskReportExport


class ProjectReportController(Controller):

  @classmethod
  def _authorize(cls, request: HttpRequest, perm: str, suffix: str="") -> bool:
    self = cls()
    self.request = request
    ability = f"{perm}{f' {suffix}' if suffix else ''}"
    self.authorize(ability)
    return True

  @method_decorator(login_required)
  @permission_required_custom("view project report", security_logger)
  def index(self, request: HttpRequest) -> Union[HttpResponse, JsonResponse]:
    REF = f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
    try:
      user = request.user
      if user.type == "client":
        qs = Project.objects.filter(client_id=user.id)
      elif user.type == "company":
        all_users = request.GET.get("all_users")
        if all_users:
          qs = Project.objects.filter(
            projectuser__user_id=all_users
          )
        else:
          qs = Project.objects.filter(created_by=user.id)
        for fld in ("status","start_date","end_date"):
          if request.GET.get(fld):
            qs = qs.filter(**{fld: request.GET[fld]})
      else:
        qs = Project.objects.filter(
          projectuser__user_id=user.id
        )
      users = (
        User.objects.filter(created_by=user.creator_id())
        .exclude(type="client") if user.type=="company" else []
      )
      status = Project.PROJECT_STATUS if user.type=="company" else []
      projects = qs.order_by("-id").prefetch_related("tasks")
      last_task = TaskStage.objects.filter(
        created_by=user.creator_id()
      ).order_by("-order").first()
      return render(request, "project_report/index.html", {
        "projects": projects, "users": users,
        "status": status, "last_task": last_task
      })
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=security_logger)
    except Exception as e:
      logging.error(f"{REF} error: {e}", exc_info=True)
      return default_undefined_exception(request, err=e, ref=REF, logger=security_logger)

  @method_decorator(login_required)
  @permission_required_custom("view project report", security_logger)
  def show(self, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
    REF = f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
    try:
      user = request.user
      if user.type=="client":
        project = get_object_or_404(Project, client_id=user.id, pk=project_id)
      elif user.type.lower()=="employee":
        project = get_object_or_404(
          Project.objects.filter(projectuser__user_id=user.id),
          pk=project_id
        )
      else:
        project = get_object_or_404(Project, created_by=user.id, pk=project_id)
      chart_data = self.get_project_chart({
        "project_id": project_id, "duration": "week"
      })
      daysleft = (project.end_date - date.today()).days
      status_counts = ProjectTask.objects.filter(
        project_id=project_id
      ).values("stage__name").annotate(count=Count("pk"))
      total_task = ProjectTask.objects.filter(project_id=project_id).count()
      arr_status, arr_status_lbl = [], []
      for itm in status_counts:
        arr_status_lbl.append(itm["stage__name"])
        pct = (itm["count"]*100/total_task) if total_task else 0
        arr_status.append(round(pct,2))
      prio_counts = ProjectTask.objects.filter(
        project_id=project_id
      ).values("priority").annotate(count=Count("pk"))
      arr_prio, arr_prio_lbl = [], []
      for itm in prio_counts:
        arr_prio_lbl.append(itm["priority"])
        pct = (itm["count"]*100/total_task) if total_task else 0
        arr_prio.append(round(pct,2))
      arr_class = ["text-success","text-primary","text-danger"]
      chart_data2 = self.get_project_chart({
        "created_by": project_id, "duration": "week"
      })
      stages = TaskStage.objects.all()
      milestones = Milestone.objects.filter(project_id=project_id)
      logged = sum(ts.time.total_seconds()/3600 for ts in Timesheet.objects.filter(project_id=project_id))
      logged_hour_chart = round(logged,2)
      esti_logged = (
        ProjectTask.objects.filter(project_id=project_id)
        .aggregate(total=Sum("estimated_hrs"))["total"] or 0
      )
      tasks = ProjectTask.objects.filter(project_id=project_id)
      last_task = TaskStage.objects.filter(
        created_by=user.creator_id()
      ).order_by("-order").first()
      return render(request,"project_report/show.html",{
        "user": user,
        "arrProcessPer_status_task": arr_status,
        "arrProcess_Label_status_tasks": arr_status_lbl,
        "arrProcessPer_priority": arr_prio,
        "arrProcess_Label_priority": arr_prio_lbl,
        "esti_logged_hour_chart": esti_logged,
        "logged_hour_chart": logged_hour_chart,
        "arrProcessClass": arr_class,
        "chartData": chart_data,
        "chartData2": chart_data2,
        "stages": stages,
        "milestones": milestones,
        "tasks": tasks,
        "last_task": last_task,
        "project": project,
        "daysleft": daysleft
      })
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=security_logger)
    except Http404:
      raise
    except Exception as e:
      logging.error(f"{REF} error: {e}", exc_info=True)
      return default_undefined_exception(request, err=e, ref=REF, logger=security_logger)

  @method_decorator(login_required)
  @permission_required_custom("view project report", security_logger)
  def get_project_chart(self, arr_param: dict, request: Union[HttpRequest, None]) -> Union[dict, JsonResponse]:
    import base64
    import json
    import urllib
    REF = f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
    try:
      if isinstance(arr_param, str):
        if not isinstance(request, HttpRequest): raise TypeError('Invalid type passed as a request')
        try:
          dec_url = urllib.parse.unquote(arr_param)
          padding_needed = len(dec_url) % 4
          if padding_needed: dec_url += '=' * (4 - padding_needed)
          arr_param = json.loads(base64.b64decode(dec_url).decode('utf-8'))
        except (base64.binascii.Error, UnicodeDecodeError) as e:
          logging.error()
          messages.error(f'Project chart data failed to be decoded: {e.__class__.__name__}')
        except (json.JSONDecodeError, ValueError) as e:
          logging.error(f'{REF} failed to load JSON data: {e}', exc_info=True)
          messages.error(f'Project chart data failed to load: {e.__class__.__name__}')
        except Exception as e:
          return default_undefined_exception(request, err=e, ref=REF, logger=security_logger)
      arr_duration = {}
      if arr_param.get("duration")=="week":
        prev = Utility.get_first_seventh_week_day(-1)
        for dt in prev["datePeriod"]:
          arr_duration[dt.strftime("%Y-%m-%d")] = dt.strftime("%a")
      arr_task = {"label":[],"color":[]}
      for dt,lbl in arr_duration.items():
        qs = ProjectTask.objects.filter(updated_at__date=dt)
        if arr_param.get("project_id"):
          qs = qs.filter(project_id=arr_param["project_id"])
        if arr_param.get("created_by"):
          qs = qs.filter(project__created_by=arr_param["created_by"])
        arr_task["label"].append(lbl)
      return arr_task if not request else JsonResponse(**arr_task)
    except Exception as e:
      logging.error(f"{REF} error: {e}", exc_info=True)
      return {}


  @method_decorator(login_required, name='dispatch')
  @permission_required_custom("view project report", security_logger)
  def export(self, request: HttpRequest, project_id: str) -> Union[HttpResponse, None]:
			REF = f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
			try:
					exporter = TaskReportExport(project_id, company_name="ERP Inc")
					exporter.format_data()
					df = exporter.dataframe()
					if df.empty:
							logging.info(f"{REF} no tasks to export for project {project_id}")
							return HttpResponse(status=204)
					filename = exporter.export_to_excel("task_report_export.xlsx")
					if not filename:
							return HttpResponse(status=204)
					with open(filename, "rb") as file_obj:
							content = file_obj.read()
					response = HttpResponse(
							content,
							content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
					)
					response["Content-Disposition"] = f'attachment; filename="{filename}"'
					return response
			except PermissionDenied as e:
					return default_permission_denial(request, err=e, ref=REF, logger=security_logger)
			except Http404:
					raise
			except Exception as e:
					logging.error(f"{REF} error: {e}", exc_info=True)
					return default_undefined_exception(request, err=e, ref=REF, logger=security_logger)
