# Encrypted Payload Issue — Payslip PDFs

## Status: Partially Fixed

## Date: 2026-02-07

### Problem

The route `payslips.payslipPdf` expects encrypted payslip IDs for security.
When crawling with raw integer IDs, it throws "The payload is invalid."

### Current Fix

- Added try/catch in PayslipController::payslipPdf to gracefully handle non-encrypted IDs
- Mock payslip data seeded for testing

### TODO

- Ensure all payslip links in the UI use encrypted IDs
- Review if encryption is truly needed or if UUIDs suffice
