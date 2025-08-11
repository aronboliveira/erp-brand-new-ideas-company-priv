import logging
import pandas as pd
import numpy as np
from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Border, Side, Alignment
from openpyxl.utils.dataframe import dataframe_to_rows
# from django.http import HttpResponse
# from rest_framework.views import APIView
# from rest_framework.response import Response
# from rest_framework import status

logger = logging.getLogger(__name__)

class ProfitLossExport:
  def __init__(self, raw_data, start_date, end_date, company_name):
    self.raw_data = raw_data
    self.start_date = start_date
    self.end_date = end_date
    self.company_name = company_name
    self.df = pd.DataFrame()
    self.formatted_data = []

  def format_data(self):
    try:
      formatted_data = []
      total_income = 0
      total_costs = 0
      total_expense = 0

      for category in self.raw_data:
        if category.get('Type') in ['Income', 'Costs of Goods Sold']:
          formatted_data.append({
            'Account Name': '',
            'Account No': '',
            'Total': ''
          })
          formatted_data.append({
            'Account Name': category['Type'],
            'Account No': '',
            'Total': ''
          })
          for account in category.get('account', []):
            net_amount = account.get('netAmount', 0)
            net_amount = net_amount if net_amount > 0 else -net_amount
            if not any(word.lower() == 'total' for word in account.get('account_name','').split()):
              formatted_data.append({
                'Account Name': f"   {account.get('account_name','')}",
                'Account No': account.get('account_code',''),
                'Total': net_amount
              })
            else:
              formatted_data.append({
                'Account Name': account.get('account_name',''),
                'Account No': account.get('account_code',''),
                'Total': net_amount
              })
            if account.get('account_name') == 'Total Income':
              total_income = net_amount
            if account.get('account_name') == 'Total Costs of Goods Sold':
              total_costs = net_amount

      gross_profit = total_income - total_costs
      formatted_data.append({
        'Account Name': 'Gross Profit',
        'Account No': '',
        'Total': gross_profit
      })

      for category in self.raw_data:
        if category.get('Type') == 'Expenses':
          formatted_data.append({'Account Name': '', 'Account No': '', 'Total': ''})
          formatted_data.append({'Account Name': category['Type'], 'Account No':'', 'Total': ''})

          for account in category.get('account', []):
            net_amount = account.get('netAmount', 0)
            net_amount = net_amount if net_amount > 0 else -net_amount
            if account.get('account_name') == 'Total Expenses':
              formatted_data.append({
                'Account Name': account.get('account_name',''),
                'Account No': account.get('account_code',''),
                'Total': net_amount
              })
              total_expense = net_amount
            else:
              formatted_data.append({
                'Account Name': f"   {account.get('account_name','')}",
                'Account No': account.get('account_code',''),
                'Total': net_amount
              })

          formatted_data.append({
            'Account Name': 'Net Profit/Loss',
            'Account No': '',
            'Total': gross_profit - total_expense
          })

      self.formatted_data = formatted_data
    except Exception as e:
      logger.error(f"format_data() error: {str(e)}")

  def dataframe(self):
    try:
      if not self.formatted_data:
        logger.warning("No formatted data to create DataFrame.")
        return pd.DataFrame()
      self.df = pd.DataFrame(self.formatted_data)
      self.df.replace({np.nan: ''}, inplace=True)
    except Exception as e:
      logger.error(f"dataframe() error: {str(e)}")
    return self.df

  def export_to_excel(self, file_name="profit_loss_export.xlsx"):
    try:
      if self.df.empty:
        logger.warning("DataFrame is empty skipping Excel generation.")
        return None

      wb = Workbook()
      ws = wb.active
      ws.title = "Profit & Loss"

      # Insert DataFrame rows
      for row_data in dataframe_to_rows(self.df, index=False, header=True):
        ws.append(row_data)

      # Basic styling references
      bold_font = Font(bold=True)
      cold_fill = PatternFill(start_color="003366", end_color="003366", fill_type="solid")
      thin_side_h = Side(border_style="thin", color="D9D9D9")
      thin_side_v = Side(border_style="thin", color="808080")

      # Style header
      if ws.max_row > 0:
        for col in ws.iter_cols(min_row=1, max_row=1):
          for cell in col:
            cell.font = bold_font
            cell.fill = cold_fill
            cell.border = Border(top=thin_side_h, bottom=thin_side_h, left=thin_side_v, right=thin_side_v)

      # Freeze header row
      ws.freeze_panes = ws["A2"]

      # Merge top cells for a title row
      max_cols = len(self.df.columns)
      if max_cols > 0:
        ws.merge_cells(start_row=1, start_column=1, end_row=1, end_column=max_cols+3) # allow some extra merges
        ws["A1"] = f"Profit & Loss - {self.company_name}"
        ws["A1"].font = bold_font
        ws["A1"].alignment = Alignment(horizontal="center")

      # Save
      wb.save(file_name)
      return file_name
    except Exception as e:
      logger.error(f"export_to_excel() error: {str(e)}")
      return None

  def column_widths(self):
    # Example column widths
    return {
      'A': 30,
      'B': 15,
      'C': 15
    }

  def start_cell(self):
    return "A5"
