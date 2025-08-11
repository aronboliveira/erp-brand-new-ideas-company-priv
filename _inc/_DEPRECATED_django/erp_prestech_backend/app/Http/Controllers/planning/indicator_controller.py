import json
import logging
from typing import Union
from django.contrib import messages
from django.contrib.auth.mixins import LoginRequiredMixin
from django.http import HttpRequest, HttpResponse, HttpResponseRedirect
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.utils.translation import gettext as _
from  .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from ....Models.activity.performance_type import PerformanceType
from ....Models.companies.branch import Branch
from ....Models.companies.department import Department
from ....Models.individuals.employee import Employee
from ....Models.planning.indicator import Indicator

logger = logging.getLogger(__name__)

class IndicatorController(LoginRequiredMixin, Controller):

  def index(self, request: HttpRequest) -> Union[HttpResponse, HttpResponseRedirect]:
    """
    Display a listing of the indicators.
    
    Args:
        request (HttpRequest): The HTTP request object
        
    Returns:
        Union[HttpResponse, HttpResponseRedirect]: The rendered template or redirect response
    """
    try:
      if not request.user.has_perm('indicator.manage_indicator'):
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      user = request.user
      indicators = []
      
      if user.type == 'Employee':
        try:
          employee = Employee.objects.filter(user_id=user.id).first()
          
          if not employee:
            logger.error(f"Employee record not found for user ID: {user.id}")
            messages.error(request, _('Employee record not found.'))
            return redirect(get_redirect_url(request))
          
          indicators = Indicator.objects.filter(
            created_by=user.creator_id(),
            branch=employee.branch_id or 0,
            department=employee.department_id or 0,
            designation=employee.designation_id or 0
          ).select_related(
            'branches', 'departments', 'designations', 'user'
          )
        except Employee.DoesNotExist:
          logger.error(f"Employee record not found for user ID: {user.id}")
          messages.error(request, _('Employee record not found.'))
          return redirect(get_redirect_url(request))
      else:
        indicators = Indicator.objects.filter(
          created_by=user.creator_id()
        ).select_related(
          'branches', 'departments', 'designations', 'user'
        )
      
      context = {'indicators': indicators}
      return render(request, 'indicator/index.html', context)
    except Exception as e:
      logger.error(f"Failed to retrieve indicators: {str(e)}")
      messages.error(request, _('An error occurred while retrieving indicators.'))
      return redirect(get_redirect_url(request))

  def create(self, request: HttpRequest) -> Union[HttpResponse, HttpResponseRedirect]:
    """
    Show the form for creating a new indicator.
    
    Args:
        request (HttpRequest): The HTTP request object
        
    Returns:
        Union[HttpResponse, HttpResponseRedirect]: The rendered template or redirect response
    """
    try:
      if not request.user.has_perm('indicator.create_indicator'):
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      branches = Branch.objects.filter(
        created_by=request.user.creator_id()
      ).values_list('id', 'name')
      
      performance = PerformanceType.objects.filter(
        created_by=request.user.creator_id()
      )
      
      departments = list(Department.objects.filter(
        created_by=request.user.creator_id()
      ).values_list('id', 'name'))
      
      departments.insert(0, ('', 'Select Department'))
      
      context = {
        'branches': dict(branches),
        'departments': dict(departments),
        'performance': performance
      }
      
      return render(request, 'indicator/create.html', context)
    except Exception as e:
      logger.error(f"Failed to load indicator creation form: {str(e)}")
      messages.error(request, _('An error occurred while loading the form.'))
      return redirect(get_redirect_url(request))

  def store(self, request: HttpRequest) -> HttpResponseRedirect:
    """
    Store a newly created indicator in storage.
    
    Args:
        request (HttpRequest): The HTTP request object
        
    Returns:
        HttpResponseRedirect: Redirect response to index or back
    """
    try:
      if not request.user.has_perm('indicator.create_indicator'):
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      # Form validation
      if not request.POST.get('branch'):
        messages.error(request, _('Branch field is required.'))
        return redirect(get_redirect_url(request))
      
      if not request.POST.get('department'):
        messages.error(request, _('Department field is required.'))
        return redirect(get_redirect_url(request))
      
      if not request.POST.get('designation'):
        messages.error(request, _('Designation field is required.'))
        return redirect(get_redirect_url(request))
      
      indicator = Indicator()
      indicator.branch = request.POST.get('branch')
      indicator.department = request.POST.get('department')
      indicator.designation = request.POST.get('designation')
      
      # Get rating from request and convert to JSON
      rating_data = request.POST.get('rating', '{}')
      try:
        # Validate JSON data if it's already a string
        if isinstance(rating_data, str):
          json.loads(rating_data)
        indicator.rating = json.dumps(rating_data)
      except json.JSONDecodeError:
        logger.error(f"Invalid JSON data for rating: {rating_data}")
        indicator.rating = '{}'
      
      if request.user.type == 'company':
        indicator.created_user = request.user.creator_id()
      else:
        indicator.created_user = request.user.id
      
      indicator.created_by = request.user.creator_id()
      indicator.save()
      
      logger.info(f"Indicator created successfully, ID: {indicator.id}")
      messages.success(request, _('Indicator successfully created.'))
      return redirect(reverse('indicator.index'))
    except Exception as e:
      logger.error(f"Failed to create indicator: {str(e)}")
      messages.error(request, _('An error occurred while creating the indicator.'))
      return redirect(get_redirect_url(request))

  def show(self, request: HttpRequest, indicator_id: int) -> Union[HttpResponse, HttpResponseRedirect]:
    """
    Display the specified indicator.
    
    Args:
        request (HttpRequest): The HTTP request object
        indicator_id (int): The ID of the indicator to show
        
    Returns:
        Union[HttpResponse, HttpResponseRedirect]: The rendered template or redirect response
    """
    try:
      indicator = get_object_or_404(Indicator, id=indicator_id)
      
      if indicator.created_by != request.user.creator_id():
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      try:
        ratings = json.loads(indicator.rating or '{}')
      except json.JSONDecodeError:
        logger.error(f"Invalid JSON in indicator rating for ID {indicator_id}")
        ratings = {}
      
      performance = PerformanceType.objects.filter(
        created_by=request.user.creator_id()
      )
      
      context = {
        'indicator': indicator,
        'ratings': ratings,
        'performance': performance
      }
      
      return render(request, 'indicator/show.html', context)
    except Indicator.DoesNotExist:
      logger.error(f"Indicator with ID {indicator_id} not found")
      messages.error(request, _('Indicator not found.'))
      return redirect(get_redirect_url(request))
    except Exception as e:
      logger.error(f"Failed to show indicator {indicator_id}: {str(e)}")
      messages.error(request, _('An error occurred while showing the indicator.'))
      return redirect(get_redirect_url(request))

  def edit(self, request: HttpRequest, indicator_id: int) -> Union[HttpResponse, HttpResponseRedirect]:
    """
    Show the form for editing the specified indicator.
    
    Args:
        request (HttpRequest): The HTTP request object
        indicator_id (int): The ID of the indicator to edit
        
    Returns:
        Union[HttpResponse, HttpResponseRedirect]: The rendered template or redirect response
    """
    try:
      if not request.user.has_perm('indicator.edit_indicator'):
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      indicator = get_object_or_404(Indicator, id=indicator_id)
      
      if indicator.created_by != request.user.creator_id():
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      performance = PerformanceType.objects.filter(
        created_by=request.user.creator_id()
      )
      
      branches = Branch.objects.filter(
        created_by=request.user.creator_id()
      ).values_list('id', 'name')
      
      departments = list(Department.objects.filter(
        created_by=request.user.creator_id()
      ).values_list('id', 'name'))
      
      departments.insert(0, ('', 'Select Department'))
      
      try:
        ratings = json.loads(indicator.rating or '{}')
      except json.JSONDecodeError:
        logger.error(f"Invalid JSON in indicator rating for ID {indicator_id}")
        ratings = {}
      
      context = {
        'indicator': indicator,
        'branches': dict(branches),
        'departments': dict(departments),
        'performance': performance,
        'ratings': ratings
      }
      
      return render(request, 'indicator/edit.html', context)
    except Indicator.DoesNotExist:
      logger.error(f"Indicator with ID {indicator_id} not found")
      messages.error(request, _('Indicator not found.'))
      return redirect(get_redirect_url(request))
    except Exception as e:
      logger.error(f"Failed to load indicator edit form for ID {indicator_id}: {str(e)}")
      messages.error(request, _('An error occurred while loading the edit form.'))
      return redirect(get_redirect_url(request))

  def update(self, request: HttpRequest, indicator_id: int) -> HttpResponseRedirect:
    """
    Update the specified indicator in storage.
    
    Args:
        request (HttpRequest): The HTTP request object
        indicator_id (int): The ID of the indicator to update
        
    Returns:
        HttpResponseRedirect: Redirect response to index or back
    """
    try:
      if not request.user.has_perm('indicator.edit_indicator'):
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      indicator = get_object_or_404(Indicator, id=indicator_id)
      
      if indicator.created_by != request.user.creator_id():
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      # Form validation
      if not request.POST.get('branch'):
        messages.error(request, _('Branch field is required.'))
        return redirect(get_redirect_url(request))
      
      if not request.POST.get('department'):
        messages.error(request, _('Department field is required.'))
        return redirect(get_redirect_url(request))
      
      if not request.POST.get('designation'):
        messages.error(request, _('Designation field is required.'))
        return redirect(get_redirect_url(request))
      
      indicator.branch = request.POST.get('branch')
      indicator.department = request.POST.get('department')
      indicator.designation = request.POST.get('designation')
      
      # Get rating from request and convert to JSON
      rating_data = request.POST.get('rating', '{}')
      try:
        # Validate JSON data if it's already a string
        if isinstance(rating_data, str):
          json.loads(rating_data)
        indicator.rating = json.dumps(rating_data)
      except json.JSONDecodeError:
        logger.error(f"Invalid JSON data for rating: {rating_data}")
        indicator.rating = '{}'
      
      indicator.save()
      
      logger.info(f"Indicator updated successfully, ID: {indicator.id}")
      messages.success(request, _('Indicator successfully updated.'))
      return redirect(reverse('indicator.index'))
    except Indicator.DoesNotExist:
      logger.error(f"Indicator with ID {indicator_id} not found")
      messages.error(request, _('Indicator not found.'))
      return redirect(get_redirect_url(request))
    except Exception as e:
      logger.error(f"Failed to update indicator {indicator_id}: {str(e)}")
      messages.error(request, _('An error occurred while updating the indicator.'))
      return redirect(get_redirect_url(request))

  def destroy(self, request: HttpRequest, indicator_id: int) -> HttpResponseRedirect:
    """
    Remove the specified indicator from storage.
    
    Args:
        request (HttpRequest): The HTTP request object
        indicator_id (int): The ID of the indicator to delete
        
    Returns:
        HttpResponseRedirect: Redirect response to index or back
    """
    try:
      if not request.user.has_perm('indicator.delete_indicator'):
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      indicator = get_object_or_404(Indicator, id=indicator_id)
      
      if indicator.created_by != request.user.creator_id():
        messages.error(request, _('Permission denied.'))
        return redirect(get_redirect_url(request))
      
      indicator.delete()
      
      logger.info(f"Indicator deleted successfully, ID: {indicator_id}")
      messages.success(request, _('Indicator successfully deleted.'))
      return redirect(reverse('indicator.index'))
    except Indicator.DoesNotExist:
      logger.error(f"Indicator with ID {indicator_id} not found")
      messages.error(request, _('Indicator not found.'))
      return redirect(get_redirect_url(request))
    except Exception as e:
      logger.error(f"Failed to delete indicator {indicator_id}: {str(e)}")
      messages.error(request, _('An error occurred while deleting the indicator.'))
      return redirect(get_redirect_url(request))