import inspect
import logging
from django.contrib import messages
from django.db.models import Q
from django.http import HttpRequest, JsonResponse
from django.shortcuts import redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_GET, require_POST
from ....Models.bills.bill import Bill
from ....Models.bills.invoice import Invoice
from ....Models.charts.chart_of_account import ChartOfAccount
from ....Models.products.product import Product
from ....Models.products.product_service import ProductService
from ....Models.products.product_service_category import ProductServiceCategory
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._helpers.http import get_redirect_url
from .._helpers.security import permission_required_custom
from django.contrib.auth.decorators import login_required
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class ProductServiceCategoryController(Controller):

  @classmethod
  @method_decorator(login_required)
  @permission_required_custom('manage constant category',logger)
  @require_GET
  def index(cls,request:HttpRequest):
    try:
      cats=ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id()
      )
      return render(request,
                    'productServiceCategory/index.html',
                    {'categories':cats})
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  @classmethod
  @method_decorator(login_required)
  @permission_required_custom('create constant category',logger)
  @require_GET
  def create(cls,request:HttpRequest):
    try:
      types_opts={'':'Select Category Type',**ProductServiceCategory.catTypes}
      chart_accounts=ChartOfAccount.objects.filter(
        created_by=request.user.creator_id()
      ).values_list('id','code')
      return render(request,
                    'productServiceCategory/create.html',
                    {'types':types_opts,
                     'chart_accounts':chart_accounts})
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  @classmethod
  @method_decorator(login_required)
  @permission_required_custom('create constant category',logger)
  @require_POST
  def store(cls,request:HttpRequest):
    try:
      d=request.POST
      name=d.get('name','').strip()
      color=d.get('color','').strip()
      typeVal=d.get('type','').strip()
      if not name:
        messages.error(request,"Name is required");
        return redirect(get_redirect_url(request))
      if len(name)>200:
        messages.error(request,"Name must be at most 200 characters");
        return redirect(get_redirect_url(request))
      if not color:
        messages.error(request,"Color is required");
        return redirect(get_redirect_url(request))
      if len(color)>100:
        messages.error(request,"Color must be at most 100 characters");
        return redirect(get_redirect_url(request))
      if not typeVal:
        messages.error(request,"Type is required");
        return redirect(get_redirect_url(request))
      if typeVal not in ProductServiceCategory.catTypes:
        messages.error(request,"Invalid category type");
        return redirect(get_redirect_url(request))
      cat=ProductServiceCategory()
      cat.name=name
      cat.color=color
      cat.type=typeVal
      cat.chart_account_id=d.get('chart_account') or '0'
      cat.created_by=request.user.creator_id()
      cat.save()
      return redirect(get_redirect_url(request))
    except Exception as e:
      return default_permission_denial(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  @classmethod
  @method_decorator(login_required)
  @permission_required_custom('view constant category',logger)
  @require_GET
  def show(cls,request:HttpRequest,pk):
    try:
      cat=ProductServiceCategory.objects.get(
        pk=pk,created_by=request.user.creator_id()
      )
      return render(request,
                    'productServiceCategory/show.html',
                    {'category':cat})
    except ProductServiceCategory.DoesNotExist as e:
      return default_permission_denial(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  @classmethod
  @method_decorator(login_required)
  @permission_required_custom('edit constant category',logger)
  @require_GET
  def edit(cls,request:HttpRequest,pk):
    try:
      cat=ProductServiceCategory.objects.get(pk=pk)
      return render(request,
                    'productServiceCategory/edit.html',
                    {'category':cat,'types':ProductServiceCategory.catTypes})
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  @classmethod
  @method_decorator(login_required)
  @permission_required_custom('edit constant category',logger)
  @require_POST
  def update(cls,request:HttpRequest,pk):
    try:
      d=request.POST
      cat=ProductServiceCategory.objects.get(
        pk=pk,created_by=request.user.creator_id()
      )
      name=d.get('name','').strip()
      color=d.get('color','').strip()
      typeVal=d.get('type','').strip()
      if not name:
        messages.error(request,"Name is required");
        return redirect(get_redirect_url(request))
      if len(name)>200:
        messages.error(request,"Name must be at most 200 characters");
        return redirect(get_redirect_url(request))
      if not color:
        messages.error(request,"Color is required");
        return redirect(get_redirect_url(request))
      if len(color)>100:
        messages.error(request,"Color must be at most 100 characters");
        return redirect(get_redirect_url(request))
      if not typeVal:
        messages.error(request,"Type is required");
        return redirect(get_redirect_url(request))
      if typeVal not in ProductServiceCategory.catTypes:
        messages.error(request,"Invalid category type");
        return redirect(get_redirect_url(request))
      cat.name=name
      cat.color=color
      cat.type=typeVal
      cat.chart_account_id=d.get('chart_account') or 0
      cat.save()
      return redirect(get_redirect_url(request))
    except Exception as e:
      return default_permission_denial(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  @classmethod
  @method_decorator(login_required)
  @permission_required_custom('delete constant category',logger)
  @require_POST
  def destroy(cls,request:HttpRequest,pk):
    try:
      cat=ProductServiceCategory.objects.get(
        pk=pk,created_by=request.user.creator_id()
      )
      rel=(Bill.objects.filter(category_id=pk).first()
           if cat.type=='bill'
           else Invoice.objects.filter(category_id=pk).first()
           if cat.type=='invoice'
           else Product.objects.filter(category_id=pk).first())
      if rel:
        return redirect(get_redirect_url(request))
      cat.delete()
      return redirect(get_redirect_url(request))
    except Exception as e:
      return default_permission_denial(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  @classmethod
  @method_decorator(login_required)
  @require_GET
  def get_product_categories(cls,request:HttpRequest):
    try:
      html='<div class="mb-3 mr-2 zoom-in">'\
           '<div class="card rounded-10 card-stats mb-0 cat-active overflow-hidden" data-id="0">'\
           '<div class="category-select" data-cat-id="0">'\
           '<button type="button" class="btn tab-btns btn-primary">All Categories</button>'\
           '</div></div></div>'
      for c in ProductServiceCategory.get_all_categories(request.user):
        html+=f'<div class="mb-3 mr-2 zoom-in cat-list-btn">'\
              f'<div class="card rounded-10 card-stats mb-0 overflow-hidden" data-id="{c.id}">'\
              f'<div class="category-select" data-cat-id="{c.id}">'\
              f'<button type="button" class="btn tab-btns btn-primary">{c.name}</button>'\
              '</div></div></div>'
      return JsonResponse({'html':html})
    except Exception as e:
      logger.error(f'{cls.__name__}::get_product_categories failed: {e}')
      return JsonResponse({'html':''},status=500)

  @classmethod
  @method_decorator(login_required)
  @require_GET
  def search_products_by_name(cls,request:HttpRequest):
    try:
      term=request.GET.get('q','').strip()
      ps=ProductService.objects.filter(
        name__icontains=term,
        created_by=request.user.creator_id()
      ).values('id','name')[:10]
      pr=Product.objects.filter(
        name__icontains=term,
        created_by=request.user.creator_id()
      ).values('id','name')[:10]
      res=[{'id':x['id'],'name':x['name'],'type':'service'}for x in ps]
      res+=[{'id':x['id'],'name':x['name'],'type':'product'}for x in pr]
      return JsonResponse(res,safe=False)
    except Exception as e:
      logger.error(f'{cls.__name__}::search_products_by_name failed: {e}')
      return JsonResponse([],status=500)

  @classmethod
  @method_decorator(login_required)
  @require_POST
  def get_account(cls,request:HttpRequest):
    try:
      t=request.POST.get('type')
      q=ChartOfAccount.objects.filter(
        created_by=request.user.creator_id()
      )
      mapping={
        'income':Q(type__name='Income'),
        'expense':Q(type__name='Expenses'),
        'asset':Q(type__name='Assets'),
        'liability':Q(type__name='Liabilities'),
        'equity':Q(type__name='Equity'),
        'costs of good sold':Q(type__name='Costs of Goods Sold')
      }
      acc=list(q.filter(mapping.get(t,Q())).values('id','code'))
      return JsonResponse(acc,safe=False)
    except Exception as e:
      logger.error(f'{cls.__name__}::get_account failed: {e}')
      return JsonResponse([],status=500)
