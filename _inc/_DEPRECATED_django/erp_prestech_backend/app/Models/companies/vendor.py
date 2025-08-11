import logging
import uuid
from datetime import datetime
from django.db import models
from .._helpers.deliverable import Deliverable
from .._helpers.fields import default_char_field, VOID

class Vendor(Deliverable):
  registration_number = models.charField(max_length=126, unique=True)
  tax_id = models.CharField(max_length=50, unique=True, **VOID)
  business_type = models.CharField(
      max_length=20,
      choices=[
          ('sole_prop', 'Sole Proprietorship'),
          ('llc', 'Limited Liability Company'),
          ('corp', 'Corporation'),
          ('other', 'Other')
      ],
      default='llc'
  )
  service_areas = models.JSONField(
      default=list,
      help_text="List of served countries/states/postal codes"
      **VOID
  )
  accepted_payment_methods = models.JSONField(
      default=list,
      help_text="Supported payment gateways/methods"
  )
  operating_hours = models.JSONField(
      default=dict,
      help_text="Weekly operating hours configuration"
  )
  evaluations = models.JSONField(
    default=dict,
    help_text="Customers evaluations"
  )
  password = default_char_field()

  class Meta:
    db_table = 'vendor'
    ordering = ['-created_at']

  def auth_id(self) -> uuid.UUID:
    try:
      return self.id
    except Exception as e:
      logging.error(f"Failed to get auth id in Vendor: {e}")
      return None

  def creator_id(self) -> uuid.UUID:
    try:
      # Refactor: if a 'type' field is defined and its value is in ['company', 'super admin'],
      # return self.id; otherwise return the created_by field.
      return self.id if hasattr(self, 'type') and self.type in ['company', 'super admin'] else self.created_by
    except Exception as e:
      logging.error(f"Failed to get creator id in Vendor: {e}")
      return None

  def current_language(self) -> str:
    try:
      return self.lang
    except Exception as e:
      logging.error(f"Failed to get current language in Vendor: {e}")
      return ""

  def price_format(self, price: float) -> str:
    from ..utils.utility import Utility
    try:
      settings = Utility.settings()
      formatted = (
        (settings['site_currency_symbol'] if settings.get('site_currency_symbol_position') == "pre" else "") +
        f"{price:,.{Utility.get_val_by_name('decimal_number')}f}" +
        (settings['site_currency_symbol'] if settings.get('site_currency_symbol_position') == "post" else "")
      )
      return formatted
    except Exception as e:
      logging.error(f"Failed to format price in Vendor: {e}")
      return ""

  def currency_symbol(self) -> str:
    from ..utils.utility import Utility
    try:
      settings = Utility.settings()
      return settings.get('site_currency_symbol', '')
    except Exception as e:
      logging.error(f"Failed to get currency symbol in Vendor: {e}")
      return ""

  def date_format(self, date: str) -> str:
    from ..utils.utility import Utility
    try:
      settings = Utility.settings()
      dt = datetime.strptime(date, "%Y-%m-%d")
      return dt.strftime(settings.get('site_date_format', "%Y-%m-%d"))
    except Exception as e:
      logging.error(f"Failed to format date in Vendor: {e}")
      return ""

  def time_format(self, time: str) -> str:
    from ..utils.utility import Utility
    try:
      settings = Utility.settings()
      dt = datetime.strptime(time, "%H:%M:%S")
      return dt.strftime(settings.get('site_time_format', "%H:%M:%S"))
    except Exception as e:
      logging.error(f"Failed to format time in Vendor: {e}")
      return ""

  def purchase_number_format(self, number: int) -> str:
    from ..utils.utility import Utility
    try:
      settings = Utility.settings()
      return settings.get("purchase_prefix", "") + f"{number:05d}"
    except Exception as e:
      logging.error(f"Failed to format purchase number in Vendor: {e}")
      return ""

  def invoice_number_format(self, number: int) -> str:
    from ..utils.utility import Utility
    try:
      settings = Utility.settings()
      return settings.get("invoice_prefix", "") + f"{number:05d}"
    except Exception as e:
      logging.error(f"Failed to format invoice number in Vendor: {e}")
      return ""

  def bill_number_format(self, number: int) -> str:
    from ..utils.utility import Utility
    try:
      settings = Utility.settings()
      return settings.get("bill_prefix", "") + f"{number:05d}"
    except Exception as e:
      logging.error(f"Failed to format bill number in Vendor: {e}")
      return ""

  def bill_chart_data(self) -> dict:
    from ..bills.bill import Bill
    try:
      month = ['January', 'February', 'March', 'April', 'May', 'June',
               'July', 'August', 'September', 'October', 'November', 'December']
      data = {'month': month, 'current_year': datetime.now().strftime("%b-%Y")}
  # ! total_bill = Bill.objects.filter(vendor_id=self.id,
   #                                    send_date__year=datetime.now().year).count()
      bill_types = {
          'unpaid': {'status': '1', 'due_date__gt': datetime.now().date()},
          'paid': {'status': '4'},
          'partial': {'status': '3'},
          'due': {'status': '1', 'due_date__lt': datetime.now().date()},
      }
      result = {key: [] for key in bill_types}
      for month in range(1, 13):
          for bill_type, filters in bill_types.items():
              bills = Bill.objects.filter(
                  vendor_id=self.id,
                  send_date__year=datetime.now().year,
                  send_date__month=month,
                  **filters
              ).all()
              if bill_type == 'paid':
                  total = sum(bill.get_total() for bill in bills)
              else:
                  total = sum(bill.get_due() for bill in bills)
              result[bill_type].append(total)
      unpaid_data = result['unpaid']
      paid_data = result['paid']
      partial_data = result['partial']
      due_data = result['due']
      data_status = {'unpaid': unpaid_data, 'paid': paid_data,
                     'partial': partial_data, 'due': due_data}
      progress_data = {
          'total_bill': Bill.objects.filter(
              vendor_id=self.id,
              send_date__year=datetime.now().year
          ).count()
      }
      for bill_type, filters in bill_types.items():
          bills = Bill.objects.filter(
              vendor_id=self.id,
              send_date__year=datetime.now().year,
              **filters
          )
          progress_data[f'total_{bill_type}_bill'] = bills.count()
      total_bill_count = progress_data['total_bill']
      data['data'] = data_status
      for lb in ('unpaid', 'paid', 'partial', 'due'):
        progress_data[f'{lb}_pr'] = (progress_data[f'total_{lb}_bill'] * 100 / total_bill_count) if total_bill_count != 0 else 0
      for lb, c in {'unpaid': '#fc544b', 'paid': '#63ed7a',
                    'partial': '#6777ef', 'due': '#ffa426'}.items():
        progress_data[f'{lb}_color'] = c
      data['progress_data'] = progress_data
      return data
    except Exception as e:
      logging.error(f"Failed to get bill chart data in Vendor: {e}")
      return {}

  def vendor_bill(self, vendor_id: uuid.UUID) -> list:
    from ..bills.bill import Bill
    try:
      bills = Bill.objects.filter(vendor_id=vendor_id).order_by('-bill_date').all()
      return list(bills)
    except Exception as e:
      logging.error(f"Failed to get vendor bill in Vendor: {e}")
      return []

  def vendor_overdue(self, vendor_id: uuid.UUID) -> float:
    from ..bills.bill import Bill
    try:
      due_bill = Bill.objects.filter(vendor_id=vendor_id).exclude(
        status__in=['0', '4']
      ).filter(due_date__lt=datetime.now().date()).all()
      due_total = sum(bill.get_due() for bill in due_bill)
      return due_total
    except Exception as e:
      logging.error(f"Failed to get vendor overdue in Vendor: {e}")
      return 0.0

  def vendor_total_bill_sum(self, vendor_id: uuid.UUID) -> float:
    from ..bills.bill import Bill
    try:
      bills = Bill.objects.filter(vendor_id=vendor_id).all()
      total = sum(bill.get_total() for bill in bills)
      return total
    except Exception as e:
      logging.error(f"Failed to get vendor total bill sum in Vendor: {e}")
      return 0.0

  def vendor_total_bill(self, vendor_id: uuid.UUID) -> int:
    from ..bills.bill import Bill
    try:
      count = Bill.objects.filter(vendor_id=vendor_id).count()
      return count
    except Exception as e:
      logging.error(f"Failed to get vendor total bill in Vendor: {e}")
      return 0
