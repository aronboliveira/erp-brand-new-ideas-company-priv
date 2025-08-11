import logging
import inspect
from typing import Any, Callable, Dict, Iterable, List
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect
from django.contrib import messages
from ....Models.activity.activity_log import ActivityLog
from ....Models.activity.activity import Activity
from ....Models.planning.task import Task
from ....Models.planning.note import Note
from ....Models.planning.schedule import Schedule
from ....Models.contact.email import Email
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class ActivityController(Controller):

    @classmethod
    def _process_items(
        cls,
        items: Iterable[Any],
        process_func: Callable[[Any], Dict[str, Any]]
    ) -> List[Dict[str, Any]]:
        """
        Processes a collection of items using the provided function.
        This acts as a Strategy pattern allowing different item processing behavior.
        """
        CN = cls.__name__
        FN = inspect.currentframe().f_code.co_name
        REF = f"{CN}::{FN}"

        results: List[Dict[str, Any]] = []
        for item in items:
            try:
                result = process_func(item)
                results.append(result)
                logger.debug("%s processed item [%s] into result: %s", REF, item, result)
            except Exception as e:
                logger.error("%s error processing item [%s]: %s", REF, item, e, exc_info=True)
                # TODO: consider re-raising or handling differently
        return results

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__
        FN = inspect.currentframe().f_code.co_name
        REF = f"{CN}::{FN}"
        logger.info("%s called by user: %s", REF, request.user)
        if not request.user.has_perm('crm.view_activity'):
            return default_permission_denial(
                request,
                err=PermissionDenied(),
                ref=REF,
                logger=logger
            )

        try:
            creator_id = getattr(request.user, 'creator_id', None)
            if creator_id is None:
                logger.error("%s no creator_id found for user: %s", REF, request.user)
                messages.error(request, "Creator ID not found.")
                return redirect(get_redirect_url(request))

            logger.debug("%s using creator_id: %s", REF, creator_id)

            with transaction.atomic():
                notes         = Note.objects.filter(created_by=creator_id).order_by('-id')
                tasks         = Task.objects.filter(created_by=creator_id).order_by('-id')
                emails        = Email.objects.filter(created_by=creator_id).order_by('-id')
                log_activities = ActivityLog.objects.filter(created_by=creator_id).order_by('-id')
                schedules     = Schedule.objects.filter(created_by=creator_id).order_by('-id')

                logger.debug(
                    "%s retrieved counts notes[%d], tasks[%d], emails[%d], log_activities[%d], schedules[%d]",
                    REF,
                    notes.count(), tasks.count(), emails.count(),
                    log_activities.count(), schedules.count()
                )

                notes_processed = cls._process_items(
                    notes,
                    lambda note: {
                        **Activity.get_activity(note.module_type, note.module_id),
                        'note':        note.note,
                        'created_at':  note.created_at.strftime('%Y-%m-%d %H:%M:%S'),
                    }
                )
                tasks_processed = cls._process_items(
                    tasks,
                    lambda task: {
                        **Activity.get_activity(task.module_type, task.module_id),
                        'note':             task.description,
                        'created_at':       task.created_at.strftime('%Y-%m-%d %H:%M:%S'),
                        'agent_or_manager': task.agent_or_manager,
                    }
                )
                emails_processed = cls._process_items(
                    emails,
                    lambda email_obj: {
                        **Activity.get_activity(email_obj.module_type, email_obj.module_id),
                        'note':        email_obj.description,
                        'created_at':  email_obj.created_at.strftime('%Y-%m-%d %H:%M:%S'),
                        'email':       email_obj.email,
                    }
                )
                log_activities_processed = cls._process_items(
                    log_activities,
                    lambda log_activity: {
                        **Activity.get_activity(log_activity.module_type, log_activity.module_id),
                        'note':       log_activity.note,
                        'created_at': log_activity.created_at.strftime('%Y-%m-%d %H:%M:%S'),
                        'start_date': log_activity.start_date,
                        'time':       log_activity.time,
                        'type':       log_activity.type,
                    }
                )
                schedules_processed = cls._process_items(
                    schedules,
                    lambda schedule: {
                        **Activity.get_activity(schedule.module_type, schedule.module_id),
                        'note':         schedule.note,
                        'created_at':   schedule.created_at.strftime('%Y-%m-%d %H:%M:%S'),
                        'start_date':   schedule.start_date,
                        'time':         schedule.start_time,
                        'type':         schedule.schedule_type,
                    }
                )

        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logger
            )

        context: Dict[str, Any] = {
            'results':  notes_processed,
            'results1': tasks_processed,
            'results2': emails_processed,
            'results3': log_activities_processed,
            'results4': schedules_processed,
        }

        logger.info("%s completed successfully. Rendering view with context.", REF)
        return render(request, 'crm/activity/view.html', context)
