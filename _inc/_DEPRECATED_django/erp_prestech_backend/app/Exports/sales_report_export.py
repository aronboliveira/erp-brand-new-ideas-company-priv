import logging
import pandas as pd
import numpy as np
from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Border, Side, Alignment
from openpyxl.utils.dataframe import dataframe_to_rows

logger = logging.getLogger(__name__)

class SalesReportExport:
  def __init__(self, data, start_date, end_date, company_name, report_name):
    self.raw_data = data
    self.start_date = start_date
    self.end_date = end_date
    self.company_name = company_name
    self.report_name = report_name
    self.df = pd.DataFrame()
    self.formatted_data = []

  def format_data(self):
    try:
      temp = []
      if self.report_name == "Item":
        for val in self.raw_data:
          item_name = val.get("name", "")
          quantity_sold = val.get("invoice_count", 0)
          amount = val.get("price", 0)
          avg_amount = val.get("avg_price", 0)
          temp.append({
            "Item Name": item_name,
            "Quantity Sold": quantity_sold,
            "Amount": amount,
            "Average Amount": avg_amount
          })
      else:
        for val in self.raw_data:
          item_name = val.get("name", "")
          quantity_sold = val.get("invoice_count", 0)
          amount = val.get("price", 0)
          total_tax = val.get("total_tax", 0)
          temp.append({
            "Customer Name": item_name,
            "Invoice Count": quantity_sold,
            "Sales": amount,
            "Sales With Tax": amount + total_tax
          })
      self.formatted_data = temp
    except Exception as e:
      logger.error(f"format_data() error: {str(e)}")

  def dataframe(self):
    try:
      if not self.formatted_data:
        logger.warning("No data to build DataFrame.")
        return pd.DataFrame()
      self.df = pd.DataFrame(self.formatted_data)
      self.df.replace({np.nan: ""}, inplace=True)
    except Exception as e:
      logger.error(f"dataframe() error: {str(e)}")
    return self.df

  def export_to_excel(self, filename="sales_report_export.xlsx"):
    try:
      if self.df.empty:
        logger.warning("DataFrame is empty skipping Excel generation.")
        return None
      wb = Workbook()
      ws = wb.active
      ws.title = "Sales Report"

      for row_data in dataframe_to_rows(self.df, index=False, header=True):
        ws.append(row_data)

      bold_font = Font(bold=True)
      cold_fill = PatternFill(start_color="003366", end_color="003366", fill_type="solid")
      row_fill = PatternFill(start_color="D9E1F2", end_color="D9E1F2", fill_type="solid")
      thin_side_h = Side(border_style="thin", color="D9D9D9")
      thin_side_v = Side(border_style="thin", color="808080")

      # Style headers
      for col in ws.iter_cols(min_row=1, max_row=1):
        for cell in col:
          cell.font = bold_font
          cell.fill = cold_fill
          cell.border = Border(top=thin_side_h, bottom=thin_side_h,
                               left=thin_side_v, right=thin_side_v)

      # Freeze row
      ws.freeze_panes = ws["A2"]

      # Style data rows
      for row in ws.iter_rows(min_row=2, max_row=ws.max_row):
        for cell in row:
          cell.fill = row_fill
          cell.border = Border(top=thin_side_h, bottom=thin_side_h,
                               left=thin_side_v, right=thin_side_v)

      # Merge top cells for a title
      num_cols = len(self.df.columns)
      if num_cols > 0:
        ws.merge_cells(start_row=1, start_column=1, end_row=1, end_column=num_cols)
        ws["A1"] = f"Sales By {self.report_name} - {self.company_name}"
        ws["A1"].font = bold_font
        ws["A1"].alignment = Alignment(horizontal="center")

      wb.save(filename)
      return filename
    except Exception as e:
      logger.error(f"export_to_excel() error: {str(e)}")
      return None

  def start_cell(self):
    return "A6"

  def column_widths(self):
    return {
      "A": 30,
      "B": 15,
      "C": 15,
      "D": 20
    }
